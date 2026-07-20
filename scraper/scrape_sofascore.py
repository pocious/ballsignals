"""
BallSignals — Sofascore Scraper
Scrapes football fixtures, 1X2 odds, and results from Sofascore's
unofficial JSON API. No API key required.

What it does:
  1. Fetches upcoming fixtures (today + next 2 days) for top European leagues
  2. Fetches 1X2 odds for each fixture
  3. Inserts new tips into both `betting_tips` and `tips` tables
  4. Fetches recent results and auto-updates tip status (won / lost)

Usage:
    python scraper/scrape_sofascore.py [--results-only] [--fixtures-only]
"""

import os
import sys
import time
import argparse
import mysql.connector
from curl_cffi import requests
from datetime import datetime, timezone, timedelta
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), '..', '.env'))

# Local timezone — must match APP_TIMEZONE in .env (Africa/Kampala = UTC+3)
LOCAL_TZ = timezone(timedelta(hours=int(os.getenv('TZ_OFFSET_HOURS', 3))))

# ── Database config ────────────────────────────────────────────────────────────

DB_CONFIG = {
    'host':     os.getenv('DB_HOST', '127.0.0.1'),
    'port':     int(os.getenv('DB_PORT', 3306)),
    'database': os.getenv('DB_DATABASE', 'ballsignals'),
    'user':     os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'charset':  'utf8mb4',
}

# ── Target leagues (Sofascore unique tournament IDs) ───────────────────────────

LEAGUES = [
    # ── Europe — Top 5 + cups ──────────────────────────────────────────────────
    {'id': 17,    'name': 'Premier League',          'country': 'England'},
    {'id': 8,     'name': 'La Liga',                 'country': 'Spain'},
    {'id': 23,    'name': 'Serie A',                 'country': 'Italy'},
    {'id': 35,    'name': 'Bundesliga',              'country': 'Germany'},
    {'id': 34,    'name': 'Ligue 1',                 'country': 'France'},
    {'id': 7,     'name': 'Champions League',        'country': 'Europe'},
    {'id': 679,   'name': 'Europa League',           'country': 'Europe'},
    {'id': 17015, 'name': 'Conference League',       'country': 'Europe'},
    # ── Europe — Other major leagues ──────────────────────────────────────────
    {'id': 37,    'name': 'Eredivisie',              'country': 'Netherlands'},
    {'id': 238,   'name': 'Primeira Liga',           'country': 'Portugal'},
    {'id': 36,    'name': 'Scottish Premiership',    'country': 'Scotland'},
    {'id': 52,    'name': 'Super Lig',               'country': 'Turkey'},
    {'id': 38,    'name': 'Belgian First Division A','country': 'Belgium'},
    # ── Americas ──────────────────────────────────────────────────────────────
    {'id': 242,   'name': 'MLS',                     'country': 'USA'},
    {'id': 325,   'name': 'Brasileirao Serie A',     'country': 'Brazil'},
    {'id': 155,   'name': 'Primera Division',        'country': 'Argentina'},
    {'id': 10,    'name': 'Liga MX',                 'country': 'Mexico'},
    {'id': 435,   'name': 'Copa Libertadores',       'country': 'South America'},
    # ── Africa ────────────────────────────────────────────────────────────────
    {'id': 670,   'name': 'Premier Soccer League',   'country': 'South Africa'},
    {'id': 519,   'name': 'Egypt Premier League',    'country': 'Egypt'},
    {'id': 390,   'name': 'CAF Champions League',    'country': 'Africa'},
    {'id': 1977,  'name': 'NPFL',                    'country': 'Nigeria'},
    {'id': 1361,  'name': 'Kenya Premier League',    'country': 'Kenya'},
    {'id': 1307,  'name': 'Uganda Premier League',   'country': 'Uganda'},
    {'id': 329,   'name': 'Botola Pro',              'country': 'Morocco'},
    # ── Asia / Middle East ────────────────────────────────────────────────────
    {'id': 955,   'name': 'Saudi Pro League',        'country': 'Saudi Arabia'},
    {'id': 196,   'name': 'J1 League',               'country': 'Japan'},
    {'id': 282,   'name': 'Chinese Super League',    'country': 'China'},
    {'id': 124,   'name': 'K League 1',              'country': 'South Korea'},
    {'id': 1267,  'name': 'Indian Super League',     'country': 'India'},
    {'id': 1084,  'name': 'UAE Pro League',          'country': 'UAE'},
]

TARGET_LEAGUE_IDS = {l['id'] for l in LEAGUES}
LEAGUE_MAP        = {l['id']: l for l in LEAGUES}

# ── Basketball target leagues ──────────────────────────────────────────────────

BASKETBALL_LEAGUES = [
    {'id': 132,  'name': 'NBA',               'country': 'USA'},
    {'id': 461,  'name': 'EuroLeague',        'country': 'Europe'},
    {'id': 148,  'name': 'EuroCup',           'country': 'Europe'},
    {'id': 120,  'name': 'ACB Liga',          'country': 'Spain'},
    {'id': 130,  'name': 'BSL',               'country': 'Turkey'},
    {'id': 129,  'name': 'Lega Basket',       'country': 'Italy'},
    {'id': 126,  'name': 'Pro A',             'country': 'France'},
    {'id': 131,  'name': 'BBL',               'country': 'Germany'},
    {'id': 128,  'name': 'A1 League',         'country': 'Greece'},
    {'id': 21,   'name': 'ABA League',        'country': 'Adriatic'},
    {'id': 141,  'name': 'VTB United League', 'country': 'Europe'},
]

BASKETBALL_LEAGUE_IDS = {l['id'] for l in BASKETBALL_LEAGUES}
BASKETBALL_LEAGUE_MAP = {l['id']: l for l in BASKETBALL_LEAGUES}

# ── HTTP session with browser-like headers ─────────────────────────────────────

BASE_URL = 'https://api.sofascore.com/api/v1'

SESSION = requests.Session(impersonate="chrome124")
SESSION.headers.update({
    'Accept':          'application/json, text/plain, */*',
    'Accept-Language': 'en-US,en;q=0.9',
    'Referer':         'https://www.sofascore.com/',
    'Origin':          'https://www.sofascore.com',
    'Cache-Control':   'no-cache',
})

# ── API helpers ────────────────────────────────────────────────────────────────

def api_get(path: str, retries: int = 3):
    """GET from Sofascore API with rate-limit handling and retries."""
    url = f"{BASE_URL}/{path}"
    for attempt in range(retries):
        try:
            r = SESSION.get(url, timeout=20)
            if r.status_code == 429:
                wait = 30 * (attempt + 1)
                print(f"    Rate limited — waiting {wait}s ...")
                time.sleep(wait)
                continue
            if r.status_code == 404:
                return None
            r.raise_for_status()
            time.sleep(0.6)   # be polite between requests
            return r.json()
        except Exception as e:
            if attempt < retries - 1:
                time.sleep(5)
            else:
                print(f"    Request error [{path}]: {e}")
                return None
    return None


def get_scheduled_events(date_str: str) -> list:
    """All football events on a given date (YYYY-MM-DD)."""
    data = api_get(f"sport/football/scheduled-events/{date_str}")
    return data.get('events', []) if data else []


_form_cache: dict = {}

def get_team_form(team_id: int) -> list[str]:
    """
    Fetch last 5 finished results for a team from Sofascore.
    Returns a list like ['W','W','D','L','W'] ordered oldest → newest.
    Results are cached so each team is only fetched once per scraper run.
    """
    if team_id in _form_cache:
        return _form_cache[team_id]

    data = api_get(f"team/{team_id}/events/last/0")
    if not data:
        _form_cache[team_id] = []
        return []

    events = data.get('events', [])
    form = []
    for e in events:
        if e.get('status', {}).get('type') != 'finished':
            continue
        h = e.get('homeScore', {}).get('current')
        a = e.get('awayScore', {}).get('current')
        if h is None or a is None:
            continue
        h, a = int(h), int(a)
        is_home = e.get('homeTeam', {}).get('id') == team_id
        if is_home:
            form.append('W' if h > a else ('D' if h == a else 'L'))
        else:
            form.append('W' if a > h else ('D' if a == h else 'L'))

    result = form[-5:]   # last 5 only
    _form_cache[team_id] = result
    return result


def _derive_markets(home_odds: float, draw_odds: float, away_odds: float) -> dict:
    """
    Derive Over/Under 2.5 and BTTS odds mathematically from 1X2 prices.
    Uses implied probability relationships and a 92% payout margin.
    """
    h = home_odds or 2.0
    d = draw_odds or 3.50
    a = away_odds or 2.0

    ph = 1 / h
    pd = 1 / d
    pa = 1 / a

    # Balance factor: 1.0 = perfectly even, 0.0 = one-sided
    balance = 1.0 - abs(ph - pa)

    # Over 2.5: rises when match is balanced and draw is unlikely
    over_prob  = max(0.35, min(0.70, 0.45 + balance * 0.12 - pd * 0.40))
    over_odds  = round(1 / over_prob  * 0.92, 2)
    under_odds = round(1 / (1 - over_prob) * 0.92, 2)

    # BTTS - Yes: rises when both teams are competitive attackers
    btts_prob     = max(0.30, min(0.65, 0.42 + balance * 0.18 - pd * 0.25))
    btts_yes_odds = round(1 / btts_prob * 0.92, 2)
    btts_no_odds  = round(1 / (1 - btts_prob) * 0.92, 2)

    return {
        'over_under': {'over': over_odds, 'under': under_odds},
        'btts':       {'yes': btts_yes_odds, 'no': btts_no_odds},
    }


def get_all_markets(event_id: int) -> dict | None:
    """
    Fetch 1X2 odds from Sofascore. Also tries to pull Over/Under 2.5 and BTTS
    from the API response; falls back to mathematically derived values if not found.
    Returns dict with keys '1x2', 'over_under', 'btts' — or None on failure.
    """
    data = api_get(f"event/{event_id}/odds/1/featured")
    if not data:
        return None

    result = {'1x2': None, 'over_under': None, 'btts': None}
    featured = data.get('featured', {})

    for key, market in featured.items():
        if not isinstance(market, dict):
            continue
        choices_list = market.get('choices', [])
        if not choices_list:
            continue

        market_name = (market.get('marketName') or key or '').lower()
        choices = {c.get('name', ''): c for c in choices_list}

        # 1X2 / Match Winner
        if key == 'default' or any(k in market_name for k in ('1x2', 'match winner', 'full time')):
            h = _parse_odds(choices.get('1'))
            d = _parse_odds(choices.get('X'))
            a = _parse_odds(choices.get('2'))
            if h and a:
                result['1x2'] = {'home': h, 'draw': d, 'away': a}

        # Over/Under 2.5 goals
        elif '2.5' in market_name and any(k in market_name for k in ('over', 'goal', 'total')):
            over  = _parse_odds(choices.get('Over') or choices.get('Over 2.5'))
            under = _parse_odds(choices.get('Under') or choices.get('Under 2.5'))
            if over and under:
                result['over_under'] = {'over': over, 'under': under}

        # Both Teams To Score
        elif any(k in market_name for k in ('both teams', 'btts', 'both to score')):
            yes = _parse_odds(choices.get('Yes'))
            no  = _parse_odds(choices.get('No'))
            if yes and no:
                result['btts'] = {'yes': yes, 'no': no}

    if not result['1x2']:
        return None

    # Fill missing markets with derived values
    m = result['1x2']
    derived = _derive_markets(m['home'], m.get('draw'), m['away'])
    if not result['over_under']:
        result['over_under'] = derived['over_under']
    if not result['btts']:
        result['btts'] = derived['btts']

    return result


def _parse_odds(choice: dict) -> float | None:
    """Extract decimal price from a Sofascore odds choice dict."""
    if not choice:
        return None
    # Try sourceValue first (decimal string), then fractionalValue
    raw = choice.get('sourceValue') or choice.get('fractionalValue')
    if not raw:
        return None
    raw = str(raw).strip()
    try:
        if '/' in raw:                      # fractional e.g. "11/10"
            num, den = raw.split('/')
            return round(float(num) / float(den) + 1, 2)
        return round(float(raw), 2)         # already decimal
    except (ValueError, ZeroDivisionError):
        return None

# ── Prediction logic ───────────────────────────────────────────────────────────

def _dc_odds(o1: float, o2: float) -> float:
    """Derive Double Chance decimal odds from two 1X2 prices."""
    try:
        return round(1 / (1 / o1 + 1 / o2) * 0.95, 2)
    except ZeroDivisionError:
        return 99.0


def pick_prediction(markets: dict) -> tuple[str, float]:
    """
    Return (label, odds) with a broad mix across all prediction types:
    Home Win, Away Win, Draw, Double Chance 1X/X2/12, Over/Under 2.5, BTTS Yes/No.

    Logic bands based on 1X2 odds:
      h < 1.35                 → Double Chance 1X  (very safe cover)
      a < 1.35                 → Double Chance X2
      h in 1.35–1.55, bal < 2  → Over 2.5          (attacking favourite)
      a in 1.35–1.55, bal < 2  → Over 2.5
      |h-a| ≤ 0.30, d < 3.50  → BTTS - Yes         (evenly matched, both score)
      |h-a| ≤ 0.60, d < 3.80  → Draw
      h in 1.55–1.80           → Double Chance 1X
      a in 1.55–1.80           → Double Chance X2
      h in 1.80–2.30, bal high → BTTS - Yes
      both > 2.30              → Double Chance 12   (no clear winner)
      default h ≤ a            → Home Win
      default h > a            → Away Win
    """
    m  = markets.get('1x2', {})
    ou = markets.get('over_under') or {}
    bt = markets.get('btts') or {}

    h = m.get('home') or 99
    d = m.get('draw') or 99
    a = m.get('away') or 99

    gap = abs(h - a)

    # Very heavy favourite
    if h < 1.35:
        return 'Double Chance 1X', _dc_odds(h, d)
    if a < 1.35:
        return 'Double Chance X2', _dc_odds(d, a)

    # Moderate favourite in attacking range → Over 2.5
    if 1.35 <= h <= 1.60 and ou.get('over'):
        return 'Over 2.5', ou['over']
    if 1.35 <= a <= 1.60 and ou.get('over'):
        return 'Over 2.5', ou['over']

    # Very even match → BTTS - Yes
    if gap <= 0.30 and d < 3.50 and bt.get('yes'):
        return 'BTTS - Yes', bt['yes']

    # Evenly matched → Draw
    if gap <= 0.60 and d < 3.80:
        return 'Draw', m.get('draw')

    # Moderate favourite → Double Chance
    if 1.60 < h <= 1.85:
        return 'Double Chance 1X', _dc_odds(h, d)
    if 1.60 < a <= 1.85:
        return 'Double Chance X2', _dc_odds(d, a)

    # Open match where both teams attack → BTTS
    if 1.85 < h <= 2.40 and a < 3.00 and bt.get('yes'):
        return 'BTTS - Yes', bt['yes']

    # No clear winner → Double Chance 12
    if h > 2.30 and a > 2.30:
        return 'Double Chance 12', _dc_odds(h, a)

    # Default
    if h <= a:
        return 'Home Win', m.get('home')
    return 'Away Win', m.get('away')


def calc_confidence(markets: dict) -> int:
    """Confidence 1–5 based on the gap between the best and second-best outcome."""
    m = markets.get('1x2', {})
    all_odds = sorted(o for o in [m.get('home'), m.get('draw'), m.get('away')] if o)
    if len(all_odds) < 2:
        return 3
    gap = all_odds[1] - all_odds[0]
    if gap > 1.5: return 5
    if gap > 1.0: return 4
    if gap > 0.5: return 3
    if gap > 0.2: return 2
    return 1

# ── Result evaluation ──────────────────────────────────────────────────────────

def evaluate_result(event: dict, prediction: str) -> str | None:
    """
    Given a finished Sofascore event and our stored prediction,
    return 'won', 'lost', or None (if result unclear).
    Supports: Home Win, Away Win, Draw, Double Chance 1X/X2/12, Over/Under 2.5, BTTS Yes/No.
    """
    status_type = event.get('status', {}).get('type', '')
    if status_type not in ('finished',):
        return None

    home_score = event.get('homeScore', {}).get('current')
    away_score = event.get('awayScore', {}).get('current')
    if home_score is None or away_score is None:
        return None

    h = int(home_score)
    a = int(away_score)
    total = h + a

    if prediction == 'Home Win':
        return 'won' if h > a else 'lost'
    if prediction == 'Away Win':
        return 'won' if a > h else 'lost'
    if prediction == 'Draw':
        return 'won' if h == a else 'lost'
    if prediction == 'Double Chance 1X':
        return 'won' if h >= a else 'lost'   # home win or draw
    if prediction == 'Double Chance X2':
        return 'won' if a >= h else 'lost'   # away win or draw
    if prediction == 'Double Chance 12':
        return 'won' if h != a else 'lost'   # home win or away win (no draw)
    if prediction == 'Over 2.5':
        return 'won' if total > 2 else 'lost'
    if prediction == 'Under 2.5':
        return 'won' if total < 3 else 'lost'
    if prediction == 'BTTS - Yes':
        return 'won' if h > 0 and a > 0 else 'lost'
    if prediction == 'BTTS - No':
        return 'won' if h == 0 or a == 0 else 'lost'
    return None

# ── Database helpers ───────────────────────────────────────────────────────────

def connect_db():
    return mysql.connector.connect(**DB_CONFIG)


def get_football_sport_id(cursor) -> int | None:
    try:
        cursor.execute("SELECT id FROM sports WHERE name LIKE '%Football%' OR slug LIKE '%football%' LIMIT 1")
        row = cursor.fetchone()
        return row[0] if row else None
    except Exception:
        return None


def get_admin_user_id(cursor) -> int | None:
    try:
        cursor.execute("SELECT id FROM users WHERE role='admin' OR id=1 ORDER BY id LIMIT 1")
        row = cursor.fetchone()
        return row[0] if row else None
    except Exception:
        return None


def betting_tip_exists(cursor, home_team, away_team, match_time) -> int | None:
    """Returns the existing tip id if found, else None."""
    cursor.execute(
        "SELECT id FROM betting_tips WHERE home_team=%s AND away_team=%s AND match_time=%s LIMIT 1",
        (home_team, away_team, match_time),
    )
    row = cursor.fetchone()
    return row[0] if row else None


def tip_exists(cursor, home_team, away_team, match_date) -> int | None:
    cursor.execute(
        "SELECT id FROM tips WHERE home_team=%s AND away_team=%s AND match_date=%s LIMIT 1",
        (home_team, away_team, match_date),
    )
    row = cursor.fetchone()
    return row[0] if row else None


def insert_betting_tip(cursor, d: dict):
    cursor.execute("""
        INSERT INTO betting_tips
            (sport, home_team, away_team, league, country,
             prediction, confidence, odds, home_odds, draw_odds, away_odds,
             match_time, status, is_premium, analyst, reasoning, home_form, away_form,
             created_at, updated_at)
        VALUES
            (%(sport)s, %(home_team)s, %(away_team)s, %(league)s, %(country)s,
             %(prediction)s, %(confidence)s, %(odds)s, %(home_odds)s, %(draw_odds)s, %(away_odds)s,
             %(match_time)s, 'pending', %(is_premium)s, 'Sofascore', %(reasoning)s,
             %(home_form)s, %(away_form)s, NOW(), NOW())
    """, d)
    return cursor.lastrowid


def _form_label(form: list[str]) -> str:
    """Turn ['W','D','L','W','W'] into 'WDLWW'."""
    return ''.join(form) if form else 'N/A'


def _form_summary(team: str, form: list[str]) -> str:
    """Human-readable recent form sentence for a team."""
    if not form:
        return ''
    wins   = form.count('W')
    draws  = form.count('D')
    losses = form.count('L')
    n      = len(form)
    last   = _form_label(form)

    if wins >= 4:
        mood = 'in excellent form'
    elif wins >= 3:
        mood = 'in good form'
    elif losses >= 3:
        mood = 'struggling for form'
    elif draws >= 3:
        mood = 'in an inconsistent run'
    else:
        mood = 'in mixed form'

    return f"{team} are {mood} — {wins}W {draws}D {losses}L in their last {n} ({last})."


def _streak(team: str, form: list[str]) -> str | None:
    """Return a streak sentence if the team's last 2+ results are identical."""
    if len(form) < 2:
        return None
    last = form[-1]
    count = 1
    for r in reversed(form[:-1]):
        if r == last:
            count += 1
        else:
            break
    if count < 2:
        return None
    label = {'W': 'winning', 'D': 'drawing', 'L': 'losing'}.get(last)
    if not label:
        return None
    return f"{team} are on a {count}-game {label} streak going into this fixture."


def generate_analysis(home_team: str, away_team: str, prediction: str,
                      markets: dict | None, league: str, country: str,
                      home_id: int | None = None, away_id: int | None = None) -> str:
    """
    Generate a detailed match analysis paragraph combining odds, market context,
    and live recent-form data from Sofascore.
    """
    m  = (markets or {}).get('1x2', {})
    ou = (markets or {}).get('over_under') or {}
    bt = (markets or {}).get('btts') or {}

    h = m.get('home') or 2.0
    d = m.get('draw') or 3.5
    a = m.get('away') or 2.0

    # O/U 2.5 and BTTS odds
    ou_over  = ou.get('over')
    ou_under = ou.get('under')
    btts_yes = bt.get('yes')
    btts_no  = bt.get('no')

    # Normalised implied probabilities (1x2)
    margin = 1/h + 1/d + 1/a
    ph = round((1/h / margin) * 100)
    pd = round((1/d / margin) * 100)
    pa = round((1/a / margin) * 100)

    # Implied probabilities for goals market
    p_over = p_under = None
    if ou_over and ou_under:
        goals_margin = 1/ou_over + 1/ou_under
        p_over  = round((1/ou_over  / goals_margin) * 100)
        p_under = round((1/ou_under / goals_margin) * 100)

    # Implied BTTS probability
    p_btts = None
    if btts_yes and btts_no:
        btts_margin = 1/btts_yes + 1/btts_no
        p_btts = round((1/btts_yes / btts_margin) * 100)

    # Determine 1x2 favourite
    if h < a - 0.05:
        fav, fav_prob, und, und_prob = home_team, ph, away_team, pa
    elif a < h - 0.05:
        fav, fav_prob, und, und_prob = away_team, pa, home_team, ph
    else:
        fav = fav_prob = und = und_prob = None

    lines = []

    # ── Line 1: fixture context ──────────────────────────────────────────────
    ctx = f" in the {league}" if league else (f" in {country}" if country else "")
    lines.append(f"{home_team} host {away_team}{ctx}.")

    # ── Line 2: recent form ──────────────────────────────────────────────────
    home_form = get_team_form(home_id) if home_id else []
    away_form = get_team_form(away_id) if away_id else []

    if home_form or away_form:
        form_parts = []
        if home_form:
            form_parts.append(_form_summary(home_team, home_form))
        if away_form:
            form_parts.append(_form_summary(away_team, away_form))
        lines.append(' '.join(form_parts))

    # ── Streak detection ─────────────────────────────────────────────────────
    if home_form:
        s = _streak(home_team, home_form)
        if s:
            lines.append(s)
    if away_form:
        s = _streak(away_team, away_form)
        if s:
            lines.append(s)

    # ── Form-vs-prediction narrative ─────────────────────────────────────────
    if home_form and away_form:
        h_wins   = home_form.count('W')
        a_wins   = away_form.count('W')
        h_draws  = home_form.count('D')
        a_draws  = away_form.count('D')
        h_losses = home_form.count('L')
        a_losses = away_form.count('L')
        h_label  = _form_label(home_form)
        a_label  = _form_label(away_form)

        if prediction in ('Home Win', 'Double Chance 1X'):
            if h_wins >= 3 and a_losses >= 2:
                lines.append(
                    f"Form data is firmly on {home_team}'s side — {h_wins} wins from their last 5 ({h_label}) "
                    f"against an {away_team} side that has lost {a_losses} of their last 5 ({a_label})."
                )
            elif h_wins >= 3:
                lines.append(
                    f"Recent form strongly backs this — {home_team} ({h_label}) have won {h_wins} of their last 5, "
                    f"demonstrating consistent quality coming into this home fixture."
                )
            elif a_losses >= 2:
                lines.append(
                    f"{away_team} arrive in poor shape ({a_label}: {a_losses} defeats in last 5), "
                    f"giving {home_team} a clear psychological edge in front of their home crowd."
                )
            else:
                lines.append(
                    f"Home advantage is a significant factor here — {home_team} ({h_label}) "
                    f"hold the edge over {away_team} ({a_label}) based on current form."
                )

        elif prediction in ('Away Win', 'Double Chance X2'):
            if a_wins >= 3 and h_losses >= 2:
                lines.append(
                    f"{away_team} ({a_label}) are in impressive form with {a_wins} wins from 5, "
                    f"while {home_team} ({h_label}) have suffered {h_losses} defeats recently — a concerning trend at home."
                )
            elif a_wins >= 3:
                lines.append(
                    f"{away_team} ({a_label}) arrive in confident form with {a_wins} wins from their last 5, "
                    f"making them a genuine away threat in this fixture."
                )
            elif h_losses >= 2:
                lines.append(
                    f"{home_team}'s recent struggles ({h_label}: {h_losses} defeats) suggest vulnerability, "
                    f"which {away_team} ({a_label}) will look to exploit."
                )
            else:
                lines.append(
                    f"Current form suggests {away_team} ({a_label}) carry enough quality "
                    f"to trouble {home_team} ({h_label}) despite playing away from home."
                )

        elif prediction == 'Draw':
            if h_draws >= 2 and a_draws >= 2:
                lines.append(
                    f"Both sides have a strong draw tendency in recent matches ({home_team}: {h_label}, {away_team}: {a_label}), "
                    f"with {h_draws + a_draws} draws between them in their last 10 games."
                )
            elif h_draws >= 2 or a_draws >= 2:
                lines.append(
                    f"Recent form shows a pattern of shared points — "
                    f"{home_team} ({h_label}) and {away_team} ({a_label}) have drawn {h_draws + a_draws} of their last 10 combined, reinforcing the draw selection."
                )
            elif h_wins >= 2 and a_wins >= 2:
                lines.append(
                    f"Both teams arrive in similar winning form ({home_team}: {h_label}, {away_team}: {a_label}) — "
                    f"neither side has a clear edge, making a stalemate the logical outcome."
                )
            else:
                lines.append(
                    f"Form for both sides ({home_team}: {h_label}, {away_team}: {a_label}) suggests "
                    f"a closely contested match that could end level."
                )

        elif prediction in ('Over 2.5', 'BTTS - Yes'):
            decisive_h = h_wins + h_losses
            decisive_a = a_wins + a_losses
            if decisive_h >= 4 or decisive_a >= 4:
                lines.append(
                    f"Both teams have played in high-scoring, decisive fixtures recently "
                    f"({home_team}: {h_label} | {away_team}: {a_label}) — few draws in the recent run "
                    f"signals open, attacking intent from both sides."
                )
            else:
                lines.append(
                    f"Form indicators ({home_team}: {h_label} | {away_team}: {a_label}) "
                    f"point to an open contest with scoring opportunities at both ends."
                )

        elif prediction in ('Under 2.5', 'BTTS - No'):
            if h_draws >= 2 or a_draws >= 2:
                lines.append(
                    f"The frequency of low-scoring draws in recent results ({home_team}: {h_label}, {away_team}: {a_label}) "
                    f"supports a tight, controlled fixture with few goals."
                )
            else:
                lines.append(
                    f"Defensive solidity from both camps ({home_team}: {h_label}, {away_team}: {a_label}) "
                    f"makes a low-scoring affair the likely outcome here."
                )

    # ── Market picture (1x2) ─────────────────────────────────────────────────
    if fav:
        strength = "strong" if fav_prob >= 60 else "moderate"
        fav_odds = h if fav == home_team else a
        lines.append(
            f"The market makes {fav} the {strength} favourite at {fav_odds} "
            f"({fav_prob}% implied) — {und} are priced at {a if und == away_team else h} "
            f"({und_prob}%), with the draw at {d} ({pd}%)."
        )
    else:
        lines.append(
            f"The bookmakers price this as a genuinely open contest — "
            f"{home_team} {ph}% ({h}), draw {pd}% ({d}), {away_team} {pa}% ({a})."
        )

    # ── Goals market context ─────────────────────────────────────────────────
    if p_over is not None and p_under is not None:
        if prediction == 'Over 2.5':
            lines.append(
                f"The goals market firmly supports this view — Over 2.5 is priced at {ou_over} "
                f"({p_over}% implied), signalling genuine bookmaker expectation of a goal-filled match."
            )
        elif prediction == 'Under 2.5':
            lines.append(
                f"The Under 2.5 market at {ou_under} ({p_under}% implied) "
                f"aligns with our cautious, low-scoring outlook for this fixture."
            )
        else:
            if p_over >= 58:
                goal_env = f"an open, high-scoring affair (O2.5 at {ou_over}, {p_over}% implied)"
            elif p_under >= 58:
                goal_env = f"a tight, low-scoring match (U2.5 at {ou_under}, {p_under}% implied)"
            else:
                goal_env = f"a balanced goal environment (O2.5: {ou_over} | U2.5: {ou_under})"
            lines.append(f"The goals market anticipates {goal_env}.")

    # ── BTTS context ─────────────────────────────────────────────────────────
    if p_btts is not None and prediction not in ('BTTS - Yes', 'BTTS - No'):
        if p_btts >= 62:
            lines.append(
                f"Both Teams to Score is heavily favoured at {btts_yes} ({p_btts}% implied), "
                f"confirming that both attacks are expected to find the net."
            )
        elif p_btts <= 35:
            lines.append(
                f"The market leans strongly against BTTS ({btts_yes}), "
                f"suggesting at least one defence is likely to keep a clean sheet."
            )

    # ── Prediction rationale ─────────────────────────────────────────────────
    rationale = {
        'Home Win':
            f"Our selection is {home_team} to win — home advantage, current form, "
            f"and market pricing all converge on this outcome.",
        'Away Win':
            f"We back {away_team} to take all three points on the road — "
            f"their form and market support make this a well-grounded away selection.",
        'Draw':
            f"With both sides closely matched and draw odds of {d}, "
            f"a share of the spoils represents the clearest value available.",
        'Double Chance 1X':
            f"The Double Chance 1X ({home_team} or draw) is our preferred route — "
            f"it captures the most likely outcomes while protecting against a surprise away win.",
        'Double Chance X2':
            f"The Double Chance X2 ({away_team} or draw) provides smart coverage — "
            f"retaining strong value while hedging against the full away risk.",
        'Double Chance 12':
            f"Draw odds of {d} imply the market expects a decisive result — "
            f"the Double Chance 12 covers either winner and eliminates the draw risk.",
        'Over 2.5':
            f"Both attacks carry a genuine threat and the market agrees — "
            f"Over 2.5 goals at {ou_over or 'current odds'} is our standout selection.",
        'Under 2.5':
            f"Tight defensive structures, cautious form, and the goals market all point "
            f"to Under 2.5 goals as the safest route in this fixture.",
        'BTTS - Yes':
            f"With both teams in regular scoring form, Both Teams to Score "
            f"at {btts_yes or 'current odds'} represents a well-supported value selection.",
        'BTTS - No':
            f"Defensive solidity from at least one side gives us confidence — "
            f"expect a clean sheet and back BTTS No at {btts_no or 'current odds'}.",
    }
    lines.append(rationale.get(
        prediction,
        f"Our analysts back {prediction} based on current form, market indicators, and statistical trends."
    ))

    # ── Value and confidence conclusion ──────────────────────────────────────
    pred_odds = {
        'Home Win': h, 'Away Win': a, 'Draw': d,
        'Over 2.5': ou_over, 'Under 2.5': ou_under,
        'BTTS - Yes': btts_yes, 'BTTS - No': btts_no,
    }.get(prediction)

    gap = abs(h - a)
    if pred_odds and pred_odds < 1.45:
        lines.append(
            f"At {pred_odds} these are short odds, but the conviction is high — "
            f"the data leaves little room for doubt on this outcome."
        )
    elif pred_odds and pred_odds > 3.2:
        lines.append(
            f"At {pred_odds}, this selection carries real value — "
            f"our model rates this significantly more likely than the market implies."
        )
    elif gap > 1.5 or (fav and fav_prob and fav_prob >= 65):
        lines.append(
            f"This is a high-confidence selection: clear market pricing, "
            f"strong form metrics, and situational factors all point in the same direction."
        )
    elif gap > 0.7:
        lines.append(
            f"Market signals are well-aligned with our analysis — "
            f"this sits firmly in our high-confidence tier for today."
        )
    else:
        lines.append(
            f"While competitive on paper, the weight of evidence — form, market odds, "
            f"and statistical indicators — points clearly to this outcome."
        )

    return " ".join(lines)


def select_premium_three(match_rows: list) -> list:
    """
    Select exactly 3 high-confidence matches for the daily premium accumulator.
    Combined odds must be >= 2.00.
    Uses the standard pick_prediction for each match (same as free tips).
    Returns list of (row, prediction, odds) — always 3 items or fewer if not enough data.
    """
    from itertools import combinations as combos

    candidates = []
    for row in match_rows:
        if not row['markets']:
            continue
        pred, odds = pick_prediction(row['markets'])
        conf = row['confidence'] or 0
        if odds and odds >= 1.10:
            candidates.append({
                'row':  row,
                'pred': pred,
                'odds': odds,
                'conf': conf,
            })

    if not candidates:
        return []

    # Sort by confidence desc, then odds desc
    candidates.sort(key=lambda x: (-x['conf'], -x['odds']))
    pool = candidates[:20]  # consider top 20 by confidence

    best_picks = None
    best_score = -1

    # Find the 3-match combo with highest total confidence AND combined odds >= 2.00
    for trio in combos(pool, 3):
        combined = trio[0]['odds'] * trio[1]['odds'] * trio[2]['odds']
        if combined < 2.00:
            continue
        score = sum(t['conf'] for t in trio)
        if score > best_score or (score == best_score and combined > best_picks[1]):
            best_score = score
            best_picks = (trio, combined)

    if best_picks:
        return [(c['row'], c['pred'], c['odds']) for c in best_picks[0]]

    # Fallback: top 3 by confidence even if combined < 2.00
    return [(c['row'], c['pred'], c['odds']) for c in candidates[:3]]


def insert_tip(cursor, d: dict):
    cursor.execute("""
        INSERT INTO tips
            (user_id, sport_id, match_date, home_team, away_team, league,
             prediction, tip_type, odds, confidence, status, is_published, published_at, created_at, updated_at)
        VALUES
            (%(user_id)s, %(sport_id)s, %(match_date)s, %(home_team)s, %(away_team)s, %(league)s,
             %(prediction)s, %(tip_type)s, %(odds)s, %(confidence)s, 'pending', 1, NOW(), NOW(), NOW())
    """, d)
    return cursor.lastrowid


def update_betting_tip_status(cursor, tip_id: int, new_status: str):
    cursor.execute(
        "UPDATE betting_tips SET status=%s, updated_at=NOW() WHERE id=%s",
        (new_status, tip_id),
    )


def update_tip_status(cursor, tip_id: int, new_status: str):
    cursor.execute(
        "UPDATE tips SET status=%s, updated_at=NOW() WHERE id=%s",
        (new_status, tip_id),
    )

# ── Main jobs ──────────────────────────────────────────────────────────────────

def scrape_fixtures(conn, cursor, sport_id: int, user_id: int):
    """
    Two-pass fixture scraper:
      Pass 1 — collect all match data + markets for each date
      Pass 2 — insert free tips (all matches) + 2 premium picks per day
                 (highest confidence, odds ≥ 2.00)
    """
    today = datetime.now(timezone.utc)
    dates = [(today + timedelta(days=i)).strftime('%Y-%m-%d') for i in range(3)]

    total_inserted_bt = 0
    total_inserted_t  = 0
    total_skipped     = 0

    for date_str in dates:
        print(f"\n  Date: {date_str}")
        events = get_scheduled_events(date_str)

        target_events = [
            e for e in events
            if e.get('tournament', {}).get('uniqueTournament', {}).get('id') in TARGET_LEAGUE_IDS
        ]
        print(f"    {len(target_events)} target-league matches found (from {len(events)} total)")

        # ── Pass 1: fetch markets for every match ─────────────────────────────
        match_rows = []
        for event in target_events:
            home_team   = event['homeTeam']['name']
            away_team   = event['awayTeam']['name']
            home_id     = event['homeTeam'].get('id')
            away_id     = event['awayTeam'].get('id')
            league_id   = event['tournament']['uniqueTournament']['id']
            league_info = LEAGUE_MAP.get(league_id, {})
            league      = league_info.get('name', event['tournament'].get('name', ''))
            country     = league_info.get('country', '')
            ts          = event.get('startTimestamp', 0)
            match_dt    = datetime.fromtimestamp(ts, tz=timezone.utc).astimezone(LOCAL_TZ)
            match_time  = match_dt.strftime('%Y-%m-%d %H:%M:%S')
            match_date  = match_dt.strftime('%Y-%m-%d')

            markets = get_all_markets(event['id'])

            if markets:
                m1x2       = markets['1x2']
                home_odds  = m1x2['home']
                draw_odds  = m1x2['draw']
                away_odds  = m1x2['away']
                prediction, best_odds = pick_prediction(markets)
                confidence = calc_confidence(markets)
            else:
                home_odds = draw_odds = away_odds = None
                prediction = 'Home Win'
                best_odds  = None
                confidence = None

            match_rows.append({
                'home_team':  home_team,
                'away_team':  away_team,
                'home_id':    home_id,
                'away_id':    away_id,
                'league':     league,
                'country':    country,
                'match_time': match_time,
                'match_date': match_date,
                'markets':    markets,
                'home_odds':  home_odds,
                'draw_odds':  draw_odds,
                'away_odds':  away_odds,
                'prediction': prediction,
                'best_odds':  best_odds,
                'confidence': confidence,
            })

        # ── Pass 2a: select 3 premium picks (accumulator) for this date ──────
        premium_picks = select_premium_three(match_rows)   # list of (row, pred, odds)
        premium_keys  = {(r['home_team'], r['away_team']) for r, _, _ in premium_picks}
        if premium_picks:
            combined = round(premium_picks[0][2] * premium_picks[1][2] * premium_picks[2][2], 2) if len(premium_picks) == 3 else 0
            print(f"    >> {len(premium_picks)} premium tip(s) selected for {date_str} (combined odds: {combined})")

        # ── Pass 2b: insert free tips ─────────────────────────────────────────
        for row in match_rows:
            bt_id = betting_tip_exists(cursor, row['home_team'], row['away_team'], row['match_time'])
            if bt_id:
                total_skipped += 1
                continue

            _hf = get_team_form(row['home_id']) if row.get('home_id') else []
            _af = get_team_form(row['away_id']) if row.get('away_id') else []
            insert_betting_tip(cursor, {
                'sport':      'Football',
                'home_team':  row['home_team'],
                'away_team':  row['away_team'],
                'league':     row['league'],
                'country':    row['country'],
                'prediction': row['prediction'],
                'confidence': row['confidence'],
                'odds':       row['best_odds'],
                'home_odds':  row['home_odds'],
                'draw_odds':  row['draw_odds'],
                'away_odds':  row['away_odds'],
                'match_time': row['match_time'],
                'is_premium': 0,
                'reasoning':  generate_analysis(
                    row['home_team'], row['away_team'], row['prediction'],
                    row['markets'], row['league'], row['country'],
                    home_id=row.get('home_id'), away_id=row.get('away_id'),
                ),
                'home_form':  _form_label(_hf) if _hf else None,
                'away_form':  _form_label(_af) if _af else None,
            })
            total_inserted_bt += 1

            if sport_id and user_id and row['markets']:
                t_id = tip_exists(cursor, row['home_team'], row['away_team'], row['match_date'])
                if not t_id:
                    insert_tip(cursor, {
                        'user_id':    user_id,
                        'sport_id':   sport_id,
                        'match_date': row['match_date'],
                        'home_team':  row['home_team'],
                        'away_team':  row['away_team'],
                        'league':     row['league'],
                        'prediction': row['prediction'],
                        'tip_type':   '1X2',
                        'odds':       row['best_odds'],
                        'confidence': row['confidence'],
                    })
                    total_inserted_t += 1

        # ── Pass 2c: insert 3 premium accumulator tips ────────────────────────
        prem_inserted = 0
        for row, prem_label, prem_odds in premium_picks:
            cursor.execute(
                "SELECT id FROM betting_tips WHERE home_team=%s AND away_team=%s AND match_time=%s AND is_premium=1 LIMIT 1",
                (row['home_team'], row['away_team'], row['match_time']),
            )
            if cursor.fetchone():
                continue

            _hf = get_team_form(row['home_id']) if row.get('home_id') else []
            _af = get_team_form(row['away_id']) if row.get('away_id') else []
            insert_betting_tip(cursor, {
                'sport':      'Football',
                'home_team':  row['home_team'],
                'away_team':  row['away_team'],
                'league':     row['league'],
                'country':    row['country'],
                'prediction': prem_label,
                'confidence': 5,
                'odds':       prem_odds,
                'home_odds':  row['home_odds'],
                'draw_odds':  row['draw_odds'],
                'away_odds':  row['away_odds'],
                'match_time': row['match_time'],
                'is_premium': 1,
                'reasoning':  generate_analysis(
                    row['home_team'], row['away_team'], prem_label,
                    row['markets'], row['league'], row['country'],
                    home_id=row.get('home_id'), away_id=row.get('away_id'),
                ),
                'home_form':  _form_label(_hf) if _hf else None,
                'away_form':  _form_label(_af) if _af else None,
            })
            prem_inserted += 1
            total_inserted_bt += 1

        conn.commit()

    print(f"\n  Fixtures done — betting_tips: +{total_inserted_bt} | tips: +{total_inserted_t} | skipped: {total_skipped}")
    return total_inserted_bt, total_inserted_t


def get_basketball_events(date_str: str) -> list:
    """All basketball events on a given date (YYYY-MM-DD)."""
    data = api_get(f"sport/basketball/scheduled-events/{date_str}")
    return data.get('events', []) if data else []


def get_basketball_markets(event_id: int) -> dict | None:
    """Fetch Home/Away winner odds for a basketball event."""
    data = api_get(f"event/{event_id}/odds/1/featured")
    if not data:
        return None
    featured = data.get('featured', {})
    for key, market in featured.items():
        if not isinstance(market, dict):
            continue
        choices_list = market.get('choices', [])
        if not choices_list:
            continue
        market_name = (market.get('marketName') or key or '').lower()
        choices = {c.get('name', ''): c for c in choices_list}
        if key == 'default' or any(k in market_name for k in ('winner', 'match winner', 'home/away', 'moneyline', '12')):
            h = _parse_odds(choices.get('1') or choices.get('Home') or choices.get('1 (incl. OT)'))
            a = _parse_odds(choices.get('2') or choices.get('Away') or choices.get('2 (incl. OT)'))
            if h and a:
                return {'1x2': {'home': h, 'draw': None, 'away': a}}
    return None


def pick_basketball_prediction(markets: dict) -> tuple[str, float]:
    """Return (label, odds) for a basketball match — Home Win or Away Win."""
    m = markets.get('1x2', {})
    h = m.get('home') or 99
    a = m.get('away') or 99
    return ('Home Win', h) if h <= a else ('Away Win', a)


def generate_basketball_analysis(home_team: str, away_team: str, prediction: str,
                                  markets: dict | None, league: str, country: str,
                                  home_id: int | None = None, away_id: int | None = None) -> str:
    """Generate a basketball match analysis paragraph."""
    m = (markets or {}).get('1x2', {})
    h = m.get('home') or 2.0
    a = m.get('away') or 2.0

    margin = 1/h + 1/a
    ph = round((1/h / margin) * 100)
    pa = round((1/a / margin) * 100)

    if h < a - 0.05:
        fav, fav_prob, und, und_prob, fav_odds, und_odds = home_team, ph, away_team, pa, h, a
    elif a < h - 0.05:
        fav, fav_prob, und, und_prob, fav_odds, und_odds = away_team, pa, home_team, ph, a, h
    else:
        fav = fav_prob = und = und_prob = fav_odds = und_odds = None

    home_form = get_team_form(home_id) if home_id else []
    away_form = get_team_form(away_id) if away_id else []

    lines = []
    ctx = f" in the {league}" if league else (f" in {country}" if country else "")
    lines.append(f"{home_team} host {away_team}{ctx}.")

    if home_form or away_form:
        form_parts = []
        if home_form:
            form_parts.append(_form_summary(home_team, home_form))
        if away_form:
            form_parts.append(_form_summary(away_team, away_form))
        lines.append(' '.join(form_parts))

    if home_form:
        s = _streak(home_team, home_form)
        if s:
            lines.append(s)
    if away_form:
        s = _streak(away_team, away_form)
        if s:
            lines.append(s)

    if home_form and away_form:
        h_wins = home_form.count('W')
        a_wins = away_form.count('W')
        h_label = _form_label(home_form)
        a_label = _form_label(away_form)
        if prediction == 'Home Win':
            if h_wins >= 3:
                lines.append(f"{home_team} ({h_label}) have been dominant recently with {h_wins} wins from their last 5 — home court gives them an extra edge.")
            elif a_wins <= 1:
                lines.append(f"{away_team}'s poor form ({a_label}: {a_wins}W in last 5) makes life easier for {home_team} at home.")
            else:
                lines.append(f"Home court advantage is a significant factor — {home_team} ({h_label}) hold the edge over {away_team} ({a_label}).")
        elif prediction == 'Away Win':
            if a_wins >= 3:
                lines.append(f"{away_team} ({a_label}) are travelling in excellent form with {a_wins} wins from 5, making them a real road threat.")
            elif h_wins <= 1:
                lines.append(f"{home_team}'s struggling form ({h_label}: {h_wins}W) opens the door for {away_team} ({a_label}) to win on the road.")
            else:
                lines.append(f"{away_team} ({a_label}) carry enough quality to prevail despite playing away from home.")

    if fav:
        strength = "strong" if fav_prob >= 65 else "moderate"
        lines.append(
            f"The market makes {fav} the {strength} favourite at {fav_odds} ({fav_prob}% implied), "
            f"with {und} priced at {und_odds} ({und_prob}%)."
        )
    else:
        lines.append(f"The market prices this as a tight contest — {home_team} {ph}% ({h}) vs {away_team} {pa}% ({a}).")

    if prediction == 'Home Win':
        lines.append(f"Our call is {home_team} to use home-court advantage and secure the win at {h}.")
    else:
        lines.append(f"Our call is {away_team} to earn the road victory at {a}.")

    gap = abs(h - a)
    if gap > 0.8:
        lines.append("Clear market pricing supports this as a high-confidence basketball pick.")
    elif gap > 0.3:
        lines.append("Market signals lean our way — a solid selection in today's basketball slate.")
    else:
        lines.append("A competitive matchup, but the weight of evidence tips in favour of our selection.")

    return " ".join(lines)


def scrape_basketball_fixtures(conn, cursor):
    """Scrape basketball fixtures for the next 3 days and insert as free + 1 VIP tip per day."""
    today = datetime.now(timezone.utc)
    dates = [(today + timedelta(days=i)).strftime('%Y-%m-%d') for i in range(3)]
    inserted = skipped = 0

    for date_str in dates:
        print(f"\n  [Basketball] Date: {date_str}")
        events = get_basketball_events(date_str)
        target = [e for e in events if e.get('tournament', {}).get('uniqueTournament', {}).get('id') in BASKETBALL_LEAGUE_IDS]
        print(f"    {len(target)} target-league basketball matches (from {len(events)} total)")

        # Build match rows with markets
        match_rows = []
        for event in target:
            home_team   = event['homeTeam']['name']
            away_team   = event['awayTeam']['name']
            home_id     = event['homeTeam'].get('id')
            away_id     = event['awayTeam'].get('id')
            league_id   = event['tournament']['uniqueTournament']['id']
            league_info = BASKETBALL_LEAGUE_MAP.get(league_id, {})
            league      = league_info.get('name', event['tournament'].get('name', ''))
            country     = league_info.get('country', '')
            ts          = event.get('startTimestamp', 0)
            match_dt    = datetime.fromtimestamp(ts, tz=timezone.utc).astimezone(LOCAL_TZ)
            match_time  = match_dt.strftime('%Y-%m-%d %H:%M:%S')

            markets = get_basketball_markets(event['id'])
            if not markets:
                skipped += 1
                continue

            prediction, odds = pick_basketball_prediction(markets)
            m = markets['1x2']
            match_rows.append({
                'home_team': home_team, 'away_team': away_team,
                'home_id': home_id, 'away_id': away_id,
                'league': league, 'country': country,
                'match_time': match_time,
                'markets': markets, 'prediction': prediction,
                'odds': odds, 'home_odds': m['home'], 'away_odds': m['away'],
            })

        # Pick 1 VIP pick per day — biggest odds gap (most one-sided market)
        vip_key = None
        if match_rows:
            best = max(match_rows, key=lambda r: abs(r['home_odds'] - r['away_odds']))
            vip_key = (best['home_team'], best['away_team'], best['match_time'])

        # Insert free tips + VIP
        for row in match_rows:
            key = (row['home_team'], row['away_team'], row['match_time'])
            is_vip = 1 if key == vip_key else 0

            # Skip if free version already exists
            if not is_vip and betting_tip_exists(cursor, row['home_team'], row['away_team'], row['match_time']):
                skipped += 1
                continue
            # Skip if premium version already exists
            if is_vip:
                cursor.execute(
                    "SELECT id FROM betting_tips WHERE home_team=%s AND away_team=%s AND match_time=%s AND is_premium=1 LIMIT 1",
                    (row['home_team'], row['away_team'], row['match_time']),
                )
                if cursor.fetchone():
                    skipped += 1
                    continue

            _hf = get_team_form(row['home_id']) if row.get('home_id') else []
            _af = get_team_form(row['away_id']) if row.get('away_id') else []

            insert_betting_tip(cursor, {
                'sport':      'Basketball',
                'home_team':  row['home_team'],
                'away_team':  row['away_team'],
                'league':     row['league'],
                'country':    row['country'],
                'prediction': row['prediction'],
                'confidence': 5 if is_vip else 3,
                'odds':       row['odds'],
                'home_odds':  row['home_odds'],
                'draw_odds':  None,
                'away_odds':  row['away_odds'],
                'match_time': row['match_time'],
                'is_premium': is_vip,
                'reasoning':  generate_basketball_analysis(
                    row['home_team'], row['away_team'], row['prediction'],
                    row['markets'], row['league'], row['country'],
                    home_id=row.get('home_id'), away_id=row.get('away_id'),
                ),
                'home_form':  _form_label(_hf) if _hf else None,
                'away_form':  _form_label(_af) if _af else None,
            })
            inserted += 1

        conn.commit()

    print(f"\n  [Basketball] Fixtures done — {inserted} inserted, {skipped} skipped")
    return inserted


def update_basketball_results(conn, cursor):
    """Update basketball tip statuses from finished game scores."""
    today = datetime.now(timezone.utc)
    dates = [(today - timedelta(days=i)).strftime('%Y-%m-%d') for i in range(4)]
    updated = 0

    for date_str in dates:
        events = get_basketball_events(date_str)
        finished = [
            e for e in events
            if e.get('status', {}).get('type') == 'finished'
            and e.get('tournament', {}).get('uniqueTournament', {}).get('id') in BASKETBALL_LEAGUE_IDS
        ]
        for event in finished:
            home_team  = event['homeTeam']['name']
            away_team  = event['awayTeam']['name']
            ts         = event.get('startTimestamp', 0)
            match_dt   = datetime.fromtimestamp(ts, tz=timezone.utc).astimezone(LOCAL_TZ)
            match_time = match_dt.strftime('%Y-%m-%d %H:%M:%S')

            cursor.execute(
                "SELECT id, prediction FROM betting_tips WHERE home_team=%s AND away_team=%s AND match_time=%s AND status='pending' AND sport='Basketball'",
                (home_team, away_team, match_time),
            )
            for bt_id, pred in cursor.fetchall():
                new_status = evaluate_result(event, pred)
                if new_status:
                    update_betting_tip_status(cursor, bt_id, new_status)
                    updated += 1

        conn.commit()

    print(f"  [Basketball] Results updated: {updated}")
    return updated


def update_results(conn, cursor):
    """
    Fetch the last 3 days of results from Sofascore,
    match them against pending tips, and update status to won/lost.
    """
    today = datetime.now(timezone.utc)
    dates = [(today - timedelta(days=i)).strftime('%Y-%m-%d') for i in range(4)]

    bt_updated = 0
    t_updated  = 0

    print("\n  Checking results for last 3 days ...")

    for date_str in dates:
        events = get_scheduled_events(date_str)
        finished = [
            e for e in events
            if e.get('status', {}).get('type') == 'finished'
            and e.get('tournament', {}).get('uniqueTournament', {}).get('id') in TARGET_LEAGUE_IDS
        ]

        for event in finished:
            home_team = event['homeTeam']['name']
            away_team = event['awayTeam']['name']
            ts        = event.get('startTimestamp', 0)
            match_dt  = datetime.fromtimestamp(ts, tz=timezone.utc).astimezone(LOCAL_TZ)
            match_time = match_dt.strftime('%Y-%m-%d %H:%M:%S')
            match_date = match_dt.strftime('%Y-%m-%d')

            # Update betting_tips — update ALL rows for this match (free + premium)
            cursor.execute(
                "SELECT id, prediction FROM betting_tips WHERE home_team=%s AND away_team=%s AND match_time=%s AND status='pending'",
                (home_team, away_team, match_time),
            )
            bt_rows = cursor.fetchall()
            for bt_row in bt_rows:
                new_status = evaluate_result(event, bt_row[1])
                if new_status:
                    update_betting_tip_status(cursor, bt_row[0], new_status)
                    bt_updated += 1

            # Update tips
            cursor.execute(
                "SELECT id, prediction FROM tips WHERE home_team=%s AND away_team=%s AND match_date=%s AND status='pending' LIMIT 1",
                (home_team, away_team, match_date),
            )
            t_row = cursor.fetchone()
            if t_row:
                new_status = evaluate_result(event, t_row[1])
                if new_status:
                    update_tip_status(cursor, t_row[0], new_status)
                    t_updated += 1

        conn.commit()

    print(f"  Results done — betting_tips updated: {bt_updated} | tips updated: {t_updated}")
    return bt_updated, t_updated

# ── Entry point ────────────────────────────────────────────────────────────────

def main():
    parser = argparse.ArgumentParser(description='BallSignals Sofascore scraper')
    parser.add_argument('--results-only',  action='store_true', help='Only update results, skip fixture scraping')
    parser.add_argument('--fixtures-only', action='store_true', help='Only scrape fixtures, skip result updates')
    args = parser.parse_args()

    print("=" * 55)
    print("  BallSignals — Sofascore Scraper")
    print("=" * 55)

    try:
        conn   = connect_db()
        cursor = conn.cursor()
    except mysql.connector.Error as e:
        print(f"ERROR: Cannot connect to database — {e}")
        sys.exit(1)

    sport_id = get_football_sport_id(cursor)
    user_id  = get_admin_user_id(cursor)

    if not sport_id:
        print("  WARNING: Football sport not found in sports table — tips table will be skipped")
    if not user_id:
        print("  WARNING: No admin user found — tips table will be skipped")

    if not args.results_only:
        print("\n[1/4] Scraping football fixtures + odds ...")
        scrape_fixtures(conn, cursor, sport_id, user_id)
        print("\n[2/4] Scraping basketball fixtures + odds ...")
        scrape_basketball_fixtures(conn, cursor)

    if not args.fixtures_only:
        print("\n[3/4] Updating football results ...")
        update_results(conn, cursor)
        print("\n[4/4] Updating basketball results ...")
        update_basketball_results(conn, cursor)

    cursor.close()
    conn.close()

    print("\n" + "=" * 55)
    print("  Done.")
    print("=" * 55)


if __name__ == '__main__':
    main()
