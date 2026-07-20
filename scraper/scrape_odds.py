"""
BallSignals — Football Odds Scraper
Fetches upcoming fixtures + 1X2 odds from The Odds API and inserts
them into the betting_tips table. Skips duplicates automatically.

Usage:
    python scraper/scrape_odds.py

Requires ODDS_API_KEY in your .env file.
Get a free key at: https://the-odds-api.com (500 requests/month free)
"""

import os
import sys
import requests
import mysql.connector
from datetime import datetime, timezone
from dotenv import load_dotenv

# Load Laravel .env from project root
load_dotenv(os.path.join(os.path.dirname(__file__), '..', '.env'))

# ── Config ────────────────────────────────────────────────────────────────────

API_KEY = os.getenv('ODDS_API_KEY', '')
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
DB_PORT = int(os.getenv('DB_PORT', 3306))
DB_NAME = os.getenv('DB_DATABASE', 'ballsignals')
DB_USER = os.getenv('DB_USERNAME', 'root')
DB_PASS = os.getenv('DB_PASSWORD', '')

# Top European leagues — sport key maps to The Odds API sport identifiers
LEAGUES = [
    {'key': 'soccer_epl',               'title': 'Premier League',   'country': 'England'},
    {'key': 'soccer_spain_la_liga',     'title': 'La Liga',          'country': 'Spain'},
    {'key': 'soccer_italy_serie_a',     'title': 'Serie A',          'country': 'Italy'},
    {'key': 'soccer_germany_bundesliga','title': 'Bundesliga',       'country': 'Germany'},
    {'key': 'soccer_uefa_champs_league','title': 'Champions League', 'country': 'Europe'},
]

BASE_URL = 'https://api.the-odds-api.com/v4/sports'

# ── API helpers ───────────────────────────────────────────────────────────────

def fetch_odds(sport_key: str) -> list:
    url = f"{BASE_URL}/{sport_key}/odds/"
    params = {
        'apiKey':      API_KEY,
        'regions':     'eu',
        'markets':     'h2h',
        'oddsFormat':  'decimal',
        'dateFormat':  'iso',
    }
    r = requests.get(url, params=params, timeout=15)
    r.raise_for_status()
    remaining = r.headers.get('x-requests-remaining', '?')
    print(f"    [API] Requests remaining this month: {remaining}")
    return r.json()


def average_h2h_odds(match: dict):
    """Average 1X2 odds across all bookmakers. Returns (home, draw, away) or None."""
    home_team = match['home_team']
    away_team = match['away_team']
    home_list, draw_list, away_list = [], [], []

    for bk in match.get('bookmakers', []):
        for market in bk.get('markets', []):
            if market['key'] != 'h2h':
                continue
            for outcome in market.get('outcomes', []):
                name, price = outcome['name'], outcome['price']
                if name == home_team:
                    home_list.append(price)
                elif name == away_team:
                    away_list.append(price)
                elif name == 'Draw':
                    draw_list.append(price)

    if not home_list or not away_list:
        return None

    home = round(sum(home_list) / len(home_list), 2)
    draw = round(sum(draw_list) / len(draw_list), 2) if draw_list else None
    away = round(sum(away_list) / len(away_list), 2)
    return home, draw, away


def pick_prediction(home_odds, draw_odds, away_odds, home_team, away_team):
    """Return (prediction_label, best_odds) based on the favorite (lowest odds)."""
    candidates = [('Home Win', home_odds), ('Away Win', away_odds)]
    if draw_odds:
        candidates.append(('Draw', draw_odds))
    return min(candidates, key=lambda x: x[1])


def calc_confidence(home_odds, draw_odds, away_odds) -> int:
    """
    Confidence 1–5 based on how dominant the favorite is.
    Larger gap between favorite and second-best = higher confidence.
    """
    all_odds = sorted(o for o in [home_odds, draw_odds, away_odds] if o)
    if len(all_odds) < 2:
        return 3
    gap = all_odds[1] - all_odds[0]
    if gap > 1.5:  return 5
    if gap > 1.0:  return 4
    if gap > 0.5:  return 3
    if gap > 0.2:  return 2
    return 1

# ── Database helpers ──────────────────────────────────────────────────────────

def connect_db():
    return mysql.connector.connect(
        host=DB_HOST, port=DB_PORT, database=DB_NAME,
        user=DB_USER, password=DB_PASS, charset='utf8mb4',
    )


def already_exists(cursor, home_team: str, away_team: str, match_time: str) -> bool:
    cursor.execute(
        "SELECT id FROM betting_tips WHERE home_team=%s AND away_team=%s AND match_time=%s LIMIT 1",
        (home_team, away_team, match_time),
    )
    return cursor.fetchone() is not None


def insert_tip(cursor, data: dict):
    cursor.execute("""
        INSERT INTO betting_tips
            (sport, home_team, away_team, league, country,
             prediction, confidence, odds,
             home_odds, draw_odds, away_odds,
             match_time, status, is_premium, analyst, created_at, updated_at)
        VALUES
            (%(sport)s, %(home_team)s, %(away_team)s, %(league)s, %(country)s,
             %(prediction)s, %(confidence)s, %(odds)s,
             %(home_odds)s, %(draw_odds)s, %(away_odds)s,
             %(match_time)s, 'pending', 0, 'AutoScraper', NOW(), NOW())
    """, data)

# ── Main ──────────────────────────────────────────────────────────────────────

def main():
    if not API_KEY:
        print("ERROR: ODDS_API_KEY is not set in your .env file.")
        print("       Get a free key at https://the-odds-api.com and add:")
        print("       ODDS_API_KEY=your_key_here")
        sys.exit(1)

    try:
        conn = connect_db()
        cursor = conn.cursor()
    except mysql.connector.Error as e:
        print(f"ERROR: Cannot connect to database — {e}")
        sys.exit(1)

    total_inserted = 0
    total_skipped  = 0
    total_errors   = 0

    for league in LEAGUES:
        print(f"\nFetching {league['title']} ({league['key']})...")
        try:
            matches = fetch_odds(league['key'])
        except requests.HTTPError as e:
            print(f"    HTTP error: {e}")
            total_errors += 1
            continue
        except requests.RequestException as e:
            print(f"    Network error: {e}")
            total_errors += 1
            continue

        league_inserted = 0
        for match in matches:
            result = average_h2h_odds(match)
            if not result:
                continue

            home_odds, draw_odds, away_odds = result
            prediction, best_odds = pick_prediction(
                home_odds, draw_odds, away_odds,
                match['home_team'], match['away_team'],
            )
            confidence = calc_confidence(home_odds, draw_odds, away_odds)

            # Convert UTC ISO timestamp → MySQL datetime string
            match_time = datetime.fromisoformat(
                match['commence_time'].replace('Z', '+00:00')
            ).astimezone(timezone.utc).strftime('%Y-%m-%d %H:%M:%S')

            if already_exists(cursor, match['home_team'], match['away_team'], match_time):
                total_skipped += 1
                continue

            try:
                insert_tip(cursor, {
                    'sport':      'Football',
                    'home_team':  match['home_team'],
                    'away_team':  match['away_team'],
                    'league':     league['title'],
                    'country':    league['country'],
                    'prediction': prediction,
                    'confidence': confidence,
                    'odds':       best_odds,
                    'home_odds':  home_odds,
                    'draw_odds':  draw_odds,
                    'away_odds':  away_odds,
                    'match_time': match_time,
                })
                league_inserted += 1
                total_inserted  += 1
            except mysql.connector.Error as e:
                print(f"    DB insert error ({match['home_team']} vs {match['away_team']}): {e}")
                total_errors += 1

        conn.commit()
        print(f"    Inserted {league_inserted} new tips  ({len(matches)} matches found)")

    cursor.close()
    conn.close()

    print(f"\n{'='*50}")
    print(f"  Done — {total_inserted} inserted | {total_skipped} skipped | {total_errors} errors")
    print(f"{'='*50}")


if __name__ == '__main__':
    main()
