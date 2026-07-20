"""
BallSignals — API-Football Scraper
Fetches upcoming fixtures, odds, and predictions from api-sports.io (API-Football v3),
generates tips, and inserts them into betting_tips + tips tables.

Usage:
    python scraper/scrape_apifootball.py [--results-only] [--fixtures-only]

Requires API_FOOTBALL_KEY in .env
Get a free key: https://dashboard.api-football.com
Free plan: 100 requests/day — this scraper uses ~2 requests per date (6 total for 3 days).
"""

import os
import sys
import time
import argparse
import requests
import mysql.connector
from difflib import SequenceMatcher
from datetime import datetime, timezone, timedelta
from dotenv import load_dotenv

# ML predictor — same directory
sys.path.insert(0, os.path.dirname(__file__))
import ml_predictor

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

load_dotenv(os.path.join(os.path.dirname(__file__), '..', '.env'))

# ── Config ─────────────────────────────────────────────────────────────────────

API_KEY  = os.getenv('API_FOOTBALL_KEY', '')
BASE_URL = 'https://v3.football.api-sports.io'
LOCAL_TZ = timezone(timedelta(hours=int(os.getenv('TZ_OFFSET_HOURS', 3))))

ODDS_API_KEY  = os.getenv('ODDS_API_KEY', '')
ODDS_API_BASE = 'https://api.the-odds-api.com/v4/sports'
_odds_api_cache: dict = {}  # sport_key → list of event dicts (cached per run)

# Basketball leagues available on The Odds API
BASKETBALL_SPORTS: list[dict] = [
    {'key': 'basketball_nba',        'name': 'NBA',         'country': 'USA'},
    {'key': 'basketball_euroleague', 'name': 'EuroLeague',  'country': 'Europe'},
    {'key': 'basketball_ncaab',      'name': 'NCAA',        'country': 'USA'},
    {'key': 'basketball_wnba',       'name': 'WNBA',        'country': 'USA'},
    {'key': 'basketball_nbl',        'name': 'NBL',         'country': 'Australia'},
]

# API-Football league ID → The Odds API sport key
ODDS_API_SPORT_KEYS: dict[int, str] = {
    39:  'soccer_epl',
    140: 'soccer_spain_la_liga',
    135: 'soccer_italy_serie_a',
    78:  'soccer_germany_bundesliga',
    61:  'soccer_france_ligue_one',
    2:   'soccer_uefa_champs_league',
    3:   'soccer_uefa_europa_league',
    848: 'soccer_uefa_conference_league',
    88:  'soccer_netherlands_eredivisie',
    94:  'soccer_portugal_primeira_liga',
    179: 'soccer_scotland_premiership',
    203: 'soccer_turkey_super_league',
    144: 'soccer_belgium_first_division',
    253: 'soccer_usa_mls',
    71:  'soccer_brazil_campeonato',
    128: 'soccer_argentina_primera_division',
    262: 'soccer_mexico_ligamx',
    307: 'soccer_saudi_arabias_league',
    98:  'soccer_japan_j_league',
}

DB_CONFIG = {
    'host':     os.getenv('DB_HOST', '127.0.0.1'),
    'port':     int(os.getenv('DB_PORT', 3306)),
    'database': os.getenv('DB_DATABASE', 'ballsignals'),
    'user':     os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'charset':  'utf8mb4',
}

# ── Target leagues (API-Football league IDs) ───────────────────────────────────

LEAGUES = [
    # ── Europe — Top 5 + cups ──────────────────────────────────────────────────
    {'id': 39,  'name': 'Premier League',        'country': 'England'},
    {'id': 140, 'name': 'La Liga',               'country': 'Spain'},
    {'id': 135, 'name': 'Serie A',               'country': 'Italy'},
    {'id': 78,  'name': 'Bundesliga',            'country': 'Germany'},
    {'id': 61,  'name': 'Ligue 1',               'country': 'France'},
    {'id': 2,   'name': 'Champions League',      'country': 'Europe'},
    {'id': 3,   'name': 'Europa League',         'country': 'Europe'},
    {'id': 848, 'name': 'Conference League',     'country': 'Europe'},
    # ── Europe — Other major leagues ───────────────────────────────────────────
    {'id': 88,  'name': 'Eredivisie',            'country': 'Netherlands'},
    {'id': 94,  'name': 'Primeira Liga',         'country': 'Portugal'},
    {'id': 179, 'name': 'Scottish Premiership',  'country': 'Scotland'},
    {'id': 203, 'name': 'Super Lig',             'country': 'Turkey'},
    {'id': 144, 'name': 'Belgian Pro League',    'country': 'Belgium'},
    # ── Americas ──────────────────────────────────────────────────────────────
    {'id': 253, 'name': 'MLS',                   'country': 'USA'},
    {'id': 71,  'name': 'Brasileirao Serie A',   'country': 'Brazil'},
    {'id': 128, 'name': 'Primera Division',      'country': 'Argentina'},
    {'id': 262, 'name': 'Liga MX',               'country': 'Mexico'},
    {'id': 13,  'name': 'Copa Libertadores',     'country': 'South America'},
    # ── Africa ────────────────────────────────────────────────────────────────
    {'id': 288, 'name': 'Premier Soccer League', 'country': 'South Africa'},
    {'id': 233, 'name': 'Egypt Premier League',  'country': 'Egypt'},
    {'id': 20,  'name': 'CAF Champions League',  'country': 'Africa'},
    {'id': 332, 'name': 'NPFL',                  'country': 'Nigeria'},
    {'id': 663, 'name': 'Kenya Premier League',  'country': 'Kenya'},
    {'id': 650, 'name': 'Uganda Premier League', 'country': 'Uganda'},
    {'id': 200, 'name': 'Botola Pro',            'country': 'Morocco'},
    # ── Asia / Middle East ────────────────────────────────────────────────────
    {'id': 307, 'name': 'Saudi Pro League',      'country': 'Saudi Arabia'},
    {'id': 98,  'name': 'J1 League',             'country': 'Japan'},
    {'id': 169, 'name': 'Chinese Super League',  'country': 'China'},
    {'id': 292, 'name': 'K League 1',            'country': 'South Korea'},
    {'id': 323, 'name': 'Indian Super League',   'country': 'India'},
    {'id': 435, 'name': 'UAE Pro League',        'country': 'UAE'},
]

TARGET_IDS = {l['id'] for l in LEAGUES}
LEAGUE_MAP = {l['id']: l for l in LEAGUES}

# ── API session ────────────────────────────────────────────────────────────────

_session = requests.Session()
_session.headers.update({
    'x-apisports-key': API_KEY,
    'Accept': 'application/json',
})

_remaining      = 9999  # daily quota remaining (from response headers)
_request_times  = []    # sliding window for per-minute rate limiting
_PER_MINUTE_MAX = 8     # stay safely under the 10/min free-plan limit


def _rate_limit():
    """Block until we're safely under 8 requests/minute."""
    now = time.time()
    _request_times[:] = [t for t in _request_times if now - t < 60]
    if len(_request_times) >= _PER_MINUTE_MAX:
        wait = 60 - (now - _request_times[0]) + 1.0
        if wait > 0:
            print(f"    [rate-limit] waiting {wait:.0f}s ...")
            time.sleep(wait)
    _request_times.append(time.time())


def api_get(endpoint: str, params: dict | None = None) -> dict | None:
    global _remaining
    if _remaining < 3:
        print("  [WARN] daily API quota nearly exhausted — aborting")
        return None

    _rate_limit()
    url = f"{BASE_URL}{endpoint}"
    try:
        r   = _session.get(url, params=params, timeout=15)
        rem = r.headers.get('x-ratelimit-requests-remaining')
        if rem is not None:
            _remaining = int(rem)

        data = r.json()
        errs = data.get('errors', {})
        if isinstance(errs, dict) and any(v for v in errs.values()):
            print(f"  API error on {endpoint}: {errs}")
            return None

        return data
    except Exception as e:
        print(f"  Request error [{endpoint}]: {e}")
        return None


# ── Data fetchers ──────────────────────────────────────────────────────────────

def get_fixtures(date_str: str) -> list:
    data = api_get('/fixtures', {'date': date_str, 'timezone': 'UTC'})
    return data.get('response', []) if data else []


def get_predictions(fixture_id: int) -> dict | None:
    """
    Fetch AI win-probability predictions (free plan endpoint).
    Converts win percentages to synthetic decimal odds and derives O/U + BTTS.
    """
    data = api_get('/predictions', {'fixture': fixture_id})
    if not data or not data.get('response'):
        return None

    pred_block = data['response'][0].get('predictions', {})
    pct        = pred_block.get('percent', {})

    home_odds = _pct_to_odds(pct.get('home', '0%'))
    draw_odds = _pct_to_odds(pct.get('draw', '0%'))
    away_odds = _pct_to_odds(pct.get('away', '0%'))

    if not home_odds or not away_odds:
        return None

    markets = {'1x2': {'home': home_odds, 'draw': draw_odds, 'away': away_odds}}
    derived = _derive_markets(home_odds, draw_odds or 3.5, away_odds)
    markets['over_under']    = derived['over_under']
    markets['over_under_05'] = derived['over_under_05']
    markets['over_under_15'] = derived['over_under_15']
    markets['over_under_35'] = derived['over_under_35']
    markets['btts']          = derived['btts']
    return markets


def _pct_to_odds(pct_str: str, margin: float = 0.90) -> float | None:
    try:
        p = float(str(pct_str).strip('%')) / 100
        return round(1 / p * margin, 2) if p > 0.01 else None
    except (ValueError, TypeError):
        return None


def _fetch_odds_api_league(sport_key: str) -> list:
    """
    Fetch all upcoming events for a sport_key from The Odds API.
    Results are cached in _odds_api_cache for the lifetime of the run.
    Returns list of event dicts (may be empty on error or quota exhaustion).
    """
    if sport_key in _odds_api_cache:
        return _odds_api_cache[sport_key]

    if not ODDS_API_KEY:
        _odds_api_cache[sport_key] = []
        return []

    try:
        url = f'{ODDS_API_BASE}/{sport_key}/odds/'
        r = requests.get(url, params={
            'apiKey':      ODDS_API_KEY,
            'regions':     'eu',
            'markets':     'h2h,totals',
            'oddsFormat':  'decimal',
            'dateFormat':  'iso',
        }, timeout=15)

        remaining = r.headers.get('x-requests-remaining', '?')
        used      = r.headers.get('x-requests-used', '?')

        if r.status_code == 401:
            print(f'  [OddsAPI] Invalid API key — check ODDS_API_KEY in .env')
            _odds_api_cache[sport_key] = []
            return []
        if r.status_code == 422:
            # Sport key not found / off-season
            _odds_api_cache[sport_key] = []
            return []
        if r.status_code != 200:
            print(f'  [OddsAPI] HTTP {r.status_code} for {sport_key}')
            _odds_api_cache[sport_key] = []
            return []

        events = r.json()
        print(f'  [OddsAPI] {sport_key}: {len(events)} events (used={used}, remaining={remaining})')
        _odds_api_cache[sport_key] = events
        return events

    except Exception as e:
        print(f'  [OddsAPI] Error fetching {sport_key}: {e}')
        _odds_api_cache[sport_key] = []
        return []


def _get_odds_api_markets(home: str, away: str, sport_key: str) -> dict | None:
    """
    Look up averaged bookmaker odds from The Odds API cache for a given fixture.
    Uses fuzzy matching on team names.  Returns a markets dict or None.
    """
    events = _fetch_odds_api_league(sport_key)
    if not events:
        return None

    h_low, a_low = home.lower(), away.lower()
    best_event, best_score = None, 0.0
    for ev in events:
        eh = (ev.get('home_team') or '').lower()
        ea = (ev.get('away_team') or '').lower()
        score = (SequenceMatcher(None, h_low, eh).ratio() +
                 SequenceMatcher(None, a_low, ea).ratio()) / 2
        if score > best_score:
            best_score, best_event = score, ev

    if best_score < 0.68 or not best_event:
        return None

    # Average odds across all bookmakers
    h2h_home_vals, h2h_draw_vals, h2h_away_vals = [], [], []
    over25_vals, under25_vals = [], []

    home_name = best_event['home_team']
    away_name = best_event['away_team']

    for bm in (best_event.get('bookmakers') or []):
        for mkt in (bm.get('markets') or []):
            key = mkt.get('key', '')
            outcomes = mkt.get('outcomes', [])
            if key == 'h2h':
                by_name = {o['name']: o['price'] for o in outcomes if 'name' in o and 'price' in o}
                h_p = by_name.get(home_name)
                a_p = by_name.get(away_name)
                d_p = by_name.get('Draw')
                if h_p and a_p:
                    h2h_home_vals.append(h_p)
                    h2h_away_vals.append(a_p)
                    if d_p:
                        h2h_draw_vals.append(d_p)
            elif key == 'totals':
                for o in outcomes:
                    if o.get('point') == 2.5:
                        if o.get('name') == 'Over':
                            over25_vals.append(o['price'])
                        elif o.get('name') == 'Under':
                            under25_vals.append(o['price'])

    if not h2h_home_vals or not h2h_away_vals:
        return None

    def _avg(lst): return round(sum(lst) / len(lst), 2) if lst else None

    home_odds = _avg(h2h_home_vals)
    away_odds = _avg(h2h_away_vals)
    draw_odds = _avg(h2h_draw_vals) if h2h_draw_vals else None
    over25    = _avg(over25_vals)
    under25   = _avg(under25_vals)

    markets = {
        '1x2': {'home': home_odds, 'draw': draw_odds, 'away': away_odds},
    }

    derived = _derive_markets(home_odds, draw_odds or 3.5, away_odds)
    markets['over_under']    = {'over': over25, 'under': under25} if over25 and under25 else derived['over_under']
    markets['over_under_05'] = derived['over_under_05']
    markets['over_under_15'] = derived['over_under_15']
    markets['over_under_35'] = derived['over_under_35']
    markets['btts']          = derived['btts']

    return markets


def _parse_bookmakers(bookmakers: list) -> dict | None:
    parsed = {'1x2': None, 'over_under': None, 'btts': None}

    for bm in bookmakers:
        for bet in bm.get('bets', []):
            bid  = bet.get('id', 0)
            name = bet.get('name', '').lower()
            vals = {}
            for v in bet.get('values', []):
                try:
                    vals[v['value']] = round(float(v['odd']), 2)
                except (KeyError, ValueError):
                    pass

            if (bid == 1 or 'match winner' in name) and not parsed['1x2']:
                h = vals.get('Home')
                d = vals.get('Draw')
                a = vals.get('Away')
                if h and a:
                    parsed['1x2'] = {'home': h, 'draw': d, 'away': a}

            elif (bid == 5 or 'goals over/under' in name or 'over/under' in name) and not parsed['over_under']:
                over  = vals.get('Over 2.5')  or vals.get('Over 2,5')
                under = vals.get('Under 2.5') or vals.get('Under 2,5')
                if over and under:
                    parsed['over_under'] = {'over': over, 'under': under}

            elif (bid == 8 or 'both teams' in name) and not parsed['btts']:
                yes = vals.get('Yes')
                no  = vals.get('No')
                if yes and no:
                    parsed['btts'] = {'yes': yes, 'no': no}

    if not parsed['1x2']:
        return None

    m = parsed['1x2']
    derived = _derive_markets(m['home'], m.get('draw') or 3.5, m['away'])
    if not parsed['over_under']:
        parsed['over_under'] = derived['over_under']
    if not parsed['btts']:
        parsed['btts'] = derived['btts']
    # Always fill derived lines (not available from paid bookmakers endpoint on free plan)
    parsed['over_under_05'] = derived['over_under_05']
    parsed['over_under_15'] = derived['over_under_15']
    parsed['over_under_35'] = derived['over_under_35']

    return parsed


# ── Prediction logic ───────────────────────────────────────────────────────────

def _derive_markets(home_odds: float, draw_odds: float, away_odds: float) -> dict:
    h = home_odds or 2.0
    d = draw_odds or 3.50
    a = away_odds or 2.0
    ph = 1 / h; pd = 1 / d; pa = 1 / a
    balance  = 1.0 - abs(ph - pa)
    # Over 2.5 is the anchor probability; scale other lines from it
    p25      = max(0.35, min(0.70, 0.45 + balance * 0.12 - pd * 0.40))
    p05      = min(0.97, p25 + 0.40)
    p15      = min(0.88, p25 + 0.22)
    p35      = max(0.08, p25 - 0.22)
    p_btts   = max(0.30, min(0.65, 0.42 + balance * 0.18 - pd * 0.25))

    def _ou(p):
        return {'over': round(1 / p * 0.92, 2), 'under': round(1 / (1 - p) * 0.92, 2)}

    return {
        'over_under':    _ou(p25),
        'over_under_05': _ou(p05),
        'over_under_15': _ou(p15),
        'over_under_35': _ou(p35),
        'btts':          {'yes': round(1 / p_btts * 0.92, 2), 'no': round(1 / (1 - p_btts) * 0.92, 2)},
    }


def _dc_odds(o1: float, o2: float) -> float:
    try:
        return round(1 / (1 / o1 + 1 / o2) * 0.95, 2)
    except ZeroDivisionError:
        return 99.0


def pick_prediction(markets: dict) -> tuple[str, float]:
    m    = markets.get('1x2', {})
    ou25 = markets.get('over_under') or {}
    ou15 = markets.get('over_under_15') or {}
    ou35 = markets.get('over_under_35') or {}
    bt   = markets.get('btts') or {}
    h    = m.get('home') or 99
    d    = m.get('draw') or 99
    a    = m.get('away') or 99
    gap  = abs(h - a)

    o15 = ou15.get('over')
    u15 = ou15.get('under')
    o25 = ou25.get('over')
    u25 = ou25.get('under')
    o35 = ou35.get('over')
    u35 = ou35.get('under')

    # Certainties — back the obvious winner directly (DC odds would be sub-1.10)
    if h < 1.30:                                          return 'Home Win', h
    if a < 1.30:                                          return 'Away Win', a

    # Massive underdog on one side — DC odds collapse to ~0.95, just back the winner
    if h > 4.00:                                          return 'Away Win', a
    if a > 4.00:                                          return 'Home Win', h

    # Strong favourite (1.30–1.52): Over 1.5 — safer than 2.5 while keeping decent odds
    if 1.30 <= h <= 1.52 and o15:                        return 'Over 1.5', o15
    if 1.30 <= a <= 1.52 and o15:                        return 'Over 1.5', o15

    # Moderate-strong favourite (1.52–1.80): Over 2.5 — attacking output expected
    if 1.52 < h <= 1.80 and o25:                         return 'Over 2.5', o25
    if 1.52 < a <= 1.80 and o25:                         return 'Over 2.5', o25

    # Even match, draw strongly priced: back the stalemate first
    if gap <= 0.40 and d < 3.20:                         return 'Draw', d

    # Even match, draw unlikely: both sides expected to score
    if gap <= 0.30 and bt.get('yes'):                    return 'BTTS - Yes', bt['yes']

    # Wider gap but draw still decent value
    if gap <= 0.60 and d < 3.80:                         return 'Draw', d

    # Even, high-scoring: Over 3.5 offers value
    if gap < 0.50 and o35 and o35 < 2.25:                return 'Over 3.5', o35

    # Moderate favourite (1.80–2.10): Double Chance — only if odds are worthwhile (≥ 1.25)
    dc_1x = _dc_odds(h, d);  dc_x2 = _dc_odds(d, a)
    if 1.80 < h <= 2.10 and dc_1x >= 1.25:              return 'Double Chance 1X', dc_1x
    if 1.80 < a <= 2.10 and dc_x2 >= 1.25:              return 'Double Chance X2', dc_x2

    # Defensive outlook — low goals expected
    if d < 3.20 and u15 and u15 < 2.00:                 return 'Under 1.5', u15

    # Wider gap, both teams can still score
    if h <= 2.50 and a <= 2.50 and bt.get('yes'):        return 'BTTS - Yes', bt['yes']

    # No clear winner: Under 3.5 as controlled hedge, or DC 12
    if h > 2.30 and a > 2.30 and u35 and u35 < 1.60:    return 'Under 3.5', u35
    if h > 2.30 and a > 2.30:                            return 'Double Chance 12', _dc_odds(h, a)

    if h <= a:                                            return 'Home Win', m.get('home')
    return 'Away Win', m.get('away')


def calc_confidence(markets: dict) -> int:
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


def generate_analysis(home_team: str, away_team: str, prediction: str,
                      markets: dict | None, league: str, country: str,
                      source: str = 'odds') -> str:
    m    = (markets or {}).get('1x2', {})
    ou25 = (markets or {}).get('over_under') or {}
    ou15 = (markets or {}).get('over_under_15') or {}
    ou35 = (markets or {}).get('over_under_35') or {}
    ou05 = (markets or {}).get('over_under_05') or {}
    bt   = (markets or {}).get('btts') or {}
    h    = m.get('home') or 2.0
    d    = m.get('draw') or 3.5
    a    = m.get('away') or 2.0

    margin = 1 / h + 1 / d + 1 / a
    ph = round((1 / h / margin) * 100)
    pd = round((1 / d / margin) * 100)
    pa = round((1 / a / margin) * 100)

    if h < a - 0.05:
        fav, fav_prob, und, und_prob, fav_o, und_o = home_team, ph, away_team, pa, h, a
    elif a < h - 0.05:
        fav, fav_prob, und, und_prob, fav_o, und_o = away_team, pa, home_team, ph, a, h
    else:
        fav = fav_prob = und = und_prob = fav_o = und_o = None

    o05 = ou05.get('over');  u05 = ou05.get('under')
    o15 = ou15.get('over');  u15 = ou15.get('under')
    o25 = ou25.get('over');  u25 = ou25.get('under')
    o35 = ou35.get('over');  u35 = ou35.get('under')
    btts_yes = bt.get('yes')
    btts_no  = bt.get('no')

    lines = []
    ctx = f" in the {league}" if league else (f" in {country}" if country else "")
    lines.append(f"{home_team} host {away_team}{ctx}.")

    if source == 'predictions':
        lines.append("Win probabilities are sourced from AI-powered match analysis.")

    if fav:
        strength = "strong" if fav_prob >= 60 else "moderate"
        lines.append(
            f"The market makes {fav} the {strength} favourite at {fav_o} "
            f"({fav_prob}% implied) — {und} are priced at {und_o} ({und_prob}%), "
            f"with the draw at {d} ({pd}%)."
        )
    else:
        lines.append(
            f"The bookmakers price this as an open contest — "
            f"{home_team} {ph}% ({h}), draw {pd}% ({d}), {away_team} {pa}% ({a})."
        )

    # Goals market context based on the selected prediction
    if prediction in ('Over 0.5', 'Under 0.5') and o05 and u05:
        gm = 1/o05 + 1/u05
        lines.append(f"Over 0.5 is priced at {o05} ({round(1/o05/gm*100)}% implied) — at least one goal is almost certain.")
    elif prediction in ('Over 1.5', 'Under 1.5') and o15 and u15:
        gm = 1/o15 + 1/u15
        p15 = round(1/o15/gm*100)
        if prediction == 'Over 1.5':
            lines.append(f"Over 1.5 goals is priced at {o15} ({p15}% implied), supporting an open, scoring contest.")
        else:
            lines.append(f"Under 1.5 goals at {u15} ({100-p15}% implied) aligns with a tight, low-scoring encounter.")
    elif prediction in ('Over 3.5', 'Under 3.5') and o35 and u35:
        gm = 1/o35 + 1/u35
        p35 = round(1/o35/gm*100)
        if prediction == 'Over 3.5':
            lines.append(f"Over 3.5 goals is priced at {o35} ({p35}% implied) — the market signals a high-scoring thriller.")
        else:
            lines.append(f"Under 3.5 goals at {u35} ({100-p35}% implied) points to a controlled, disciplined game.")
    elif o25 and u25:
        gm      = 1 / o25 + 1 / u25
        p_over  = round(1 / o25 / gm * 100)
        p_under = round(1 / u25 / gm * 100)
        if prediction == 'Over 2.5':
            lines.append(f"Over 2.5 is priced at {o25} ({p_over}% implied), signalling an open, goal-filled match.")
        elif prediction == 'Under 2.5':
            lines.append(f"Under 2.5 at {u25} ({p_under}% implied) aligns with our low-scoring outlook.")
        elif p_over >= 58:
            lines.append(f"The goals market leans towards a high-scoring affair (O2.5 at {o25}, {p_over}% implied).")
        elif p_under >= 58:
            lines.append(f"The market expects few goals (U2.5 at {u25}, {p_under}% implied).")

    if btts_yes and btts_no and prediction not in ('BTTS - Yes', 'BTTS - No'):
        bm     = 1 / btts_yes + 1 / btts_no
        p_btts = round((1 / btts_yes / bm) * 100)
        if p_btts >= 62:
            lines.append(f"Both Teams to Score is firmly favoured at {btts_yes} ({p_btts}% implied).")
        elif p_btts <= 35:
            lines.append(f"At least one side is expected to keep a clean sheet (BTTS No implied at {100 - p_btts}%).")

    rationale = {
        'Home Win':
            f"Our selection is {home_team} to win — home advantage and market pricing converge on this outcome.",
        'Away Win':
            f"We back {away_team} to take all three points — form and market support a confident away selection.",
        'Draw':
            f"With both sides closely matched and draw odds at {d}, a share of the spoils is the clearest value.",
        'Double Chance 1X':
            f"The Double Chance 1X ({home_team} or draw) captures the most likely outcomes while protecting against a surprise away win.",
        'Double Chance X2':
            f"The Double Chance X2 ({away_team} or draw) retains strong value while hedging the full away risk.",
        'Double Chance 12':
            f"Draw odds of {d} suggest a decisive result — Double Chance 12 covers either winner and eliminates the draw risk.",
        'Over 0.5':
            f"Goals are near-certain here — Over 0.5 at {o05 or 'current odds'} is a high-conviction safety pick.",
        'Over 1.5':
            f"With two or more goals firmly expected, Over 1.5 at {o15 or 'current odds'} is a solid, well-supported pick.",
        'Over 2.5':
            f"Both attacks carry a genuine threat — Over 2.5 goals at {o25 or 'current odds'} is the standout pick.",
        'Over 3.5':
            f"The attacking quality on show points to a high-scoring game — Over 3.5 at {o35 or 'current odds'} represents great value.",
        'Under 0.5':
            f"An exceptionally tight tactical battle is expected — Under 0.5 is a bold defensive pick.",
        'Under 1.5':
            f"Defensive solidity and a low-scoring market signal back Under 1.5 goals at {u15 or 'current odds'}.",
        'Under 2.5':
            f"Tight defensive structures and the goals market point to Under 2.5 as the safest route.",
        'Under 3.5':
            f"While goals are likely, a cricket score is not — Under 3.5 at {u35 or 'current odds'} is the controlled pick.",
        'BTTS - Yes':
            f"With both teams in regular scoring form, Both Teams to Score at {btts_yes or 'current odds'} is well-supported.",
        'BTTS - No':
            f"Defensive solidity from at least one side — expect a clean sheet and back BTTS No at {btts_no or 'current odds'}.",
    }
    lines.append(rationale.get(
        prediction,
        f"Our analysts back {prediction} based on current market indicators and statistical trends.",
    ))

    pred_o = {
        'Home Win': h,    'Away Win': a,   'Draw': d,
        'Over 0.5': o05,  'Under 0.5': u05,
        'Over 1.5': o15,  'Under 1.5': u15,
        'Over 2.5': o25,  'Under 2.5': u25,
        'Over 3.5': o35,  'Under 3.5': u35,
        'BTTS - Yes': btts_yes, 'BTTS - No': btts_no,
    }.get(prediction)
    gap = abs(h - a)

    if pred_o and pred_o < 1.45:
        lines.append(f"At {pred_o} these are short odds, but conviction is high — the data leaves little room for doubt.")
    elif pred_o and pred_o > 3.2:
        lines.append(f"At {pred_o} this selection carries genuine value — our model rates it more likely than implied.")
    elif gap > 1.5 or (fav_prob and fav_prob >= 65):
        lines.append("Clear market pricing and situational factors all align on this outcome.")
    else:
        lines.append("The weight of evidence — market odds and statistical indicators — points clearly to this outcome.")

    return " ".join(lines)


def evaluate_result(home_score: int, away_score: int, prediction: str) -> str | None:
    total = home_score + away_score
    if prediction == 'Home Win':         return 'won' if home_score > away_score else 'lost'
    if prediction == 'Away Win':         return 'won' if away_score > home_score else 'lost'
    if prediction == 'Draw':             return 'won' if home_score == away_score else 'lost'
    if prediction == 'Double Chance 1X': return 'won' if home_score >= away_score else 'lost'
    if prediction == 'Double Chance X2': return 'won' if away_score >= home_score else 'lost'
    if prediction == 'Double Chance 12': return 'won' if home_score != away_score else 'lost'
    if prediction == 'Over 0.5':         return 'won' if total > 0 else 'lost'
    if prediction == 'Over 1.5':         return 'won' if total > 1 else 'lost'
    if prediction == 'Over 2.5':         return 'won' if total > 2 else 'lost'
    if prediction == 'Over 3.5':         return 'won' if total > 3 else 'lost'
    if prediction == 'Under 0.5':        return 'won' if total < 1 else 'lost'
    if prediction == 'Under 1.5':        return 'won' if total < 2 else 'lost'
    if prediction == 'Under 2.5':        return 'won' if total < 3 else 'lost'
    if prediction == 'Under 3.5':        return 'won' if total < 4 else 'lost'
    if prediction == 'BTTS - Yes':       return 'won' if home_score > 0 and away_score > 0 else 'lost'
    if prediction == 'BTTS - No':        return 'won' if home_score == 0 or away_score == 0 else 'lost'
    return None


def select_premium_three(match_rows: list) -> list:
    from itertools import combinations as combos
    candidates = []
    for row in match_rows:
        if not row['markets']:
            continue
        pred, odds = pick_prediction(row['markets'])
        conf = row['confidence'] or 0
        if odds and odds >= 1.10:
            candidates.append({'row': row, 'pred': pred, 'odds': odds, 'conf': conf})
    if not candidates:
        return []
    candidates.sort(key=lambda x: (-x['conf'], -x['odds']))
    pool = candidates[:20]
    best_picks, best_score = None, -1
    for trio in combos(pool, 3):
        combined = trio[0]['odds'] * trio[1]['odds'] * trio[2]['odds']
        if combined < 2.00:
            continue
        score = sum(t['conf'] for t in trio)
        if score > best_score or (score == best_score and best_picks and combined > best_picks[1]):
            best_score  = score
            best_picks  = (trio, combined)
    if best_picks:
        return [(c['row'], c['pred'], c['odds']) for c in best_picks[0]]
    return [(c['row'], c['pred'], c['odds']) for c in candidates[:3]]


# ── Database helpers ───────────────────────────────────────────────────────────

def connect_db():
    return mysql.connector.connect(**DB_CONFIG)


def get_sport_id(cursor) -> int | None:
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
             %(match_time)s, 'pending', %(is_premium)s, %(analyst)s, %(reasoning)s,
             %(home_form)s, %(away_form)s, NOW(), NOW())
    """, d)
    return cursor.lastrowid


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


# ── Main jobs ──────────────────────────────────────────────────────────────────

def scrape_fixtures(conn, cursor, sport_id, user_id):
    today = datetime.now(timezone.utc)
    dates = [(today + timedelta(days=i)).strftime('%Y-%m-%d') for i in range(2)]

    total_bt = total_t = skipped = 0

    for date_str in dates:
        print(f"\n  Date: {date_str}")

        fixtures = get_fixtures(date_str)
        target   = [f for f in fixtures if f.get('league', {}).get('id') in TARGET_IDS]
        print(f"    {len(target)} target-league matches (from {len(fixtures)} total) — quota remaining: {_remaining}")

        if not target:
            continue

        # ── Pass 1: build match rows ──────────────────────────────────────────
        match_rows  = []
        sf_odds_hit = 0
        print(f"    Fetching predictions ({len(target)} matches) — quota remaining: {_remaining}")
        for fix in target:
            if _remaining < 5:
                print("    [WARN] Daily quota low — stopping early")
                break

            fid       = fix['fixture']['id']
            home_team = fix['teams']['home']['name']
            away_team = fix['teams']['away']['name']
            league_id = fix['league']['id']
            info      = LEAGUE_MAP.get(league_id, {})
            league    = info.get('name', fix['league']['name'])
            country   = info.get('country', fix['league']['country'])

            ts = fix['fixture'].get('timestamp')
            if ts:
                match_dt = datetime.fromtimestamp(ts, tz=timezone.utc).astimezone(LOCAL_TZ)
            else:
                match_dt = datetime.strptime(date_str, '%Y-%m-%d').replace(tzinfo=LOCAL_TZ)

            match_time = match_dt.strftime('%Y-%m-%d %H:%M:%S')
            match_date = match_dt.strftime('%Y-%m-%d')

            # ── Odds source priority: Odds API → API-Football predictions ──────
            markets = None
            source  = 'predictions'

            # 1. The Odds API (official bookmaker odds) — only if key + league mapped
            odds_sport_key = ODDS_API_SPORT_KEYS.get(league_id) if ODDS_API_KEY else None
            if odds_sport_key:
                markets = _get_odds_api_markets(home_team, away_team, odds_sport_key)
                if markets:
                    source = 'odds_api'
                    sf_odds_hit += 1

            # 2. API-Football AI predictions — fallback when Odds API misses
            if not markets:
                markets = get_predictions(fid)
                source  = 'predictions'

            if not markets:
                print(f"      [SKIP] No odds for {home_team} vs {away_team}")
                continue

            home_form = ''
            away_form = ''
            m1x2 = markets['1x2']

            # ── ML prediction (form + odds → outcome) ─────────────────────────
            ml_result = None
            if home_form or away_form:
                ml_result = ml_predictor.predict(
                    home_form, away_form,
                    m1x2['home'], m1x2.get('draw'), m1x2['away'],
                )

            if ml_result and ml_result[1] >= ml_predictor.CONFIDENCE_THRESHOLD:
                ml_pred, ml_conf = ml_result
                # Get the bookmaker odds for the ML-predicted outcome
                if ml_pred == 'Home Win':
                    pred_odds = m1x2['home']
                elif ml_pred == 'Draw':
                    pred_odds = m1x2.get('draw') or 3.5
                else:
                    pred_odds = m1x2['away']
                pred   = ml_pred
                conf   = min(5, max(3, round(ml_conf * 6)))
                source = source + '+ml'
            else:
                pred, pred_odds = pick_prediction(markets)
                conf = calc_confidence(markets)

            match_rows.append({
                'fixture_id': fid,
                'home_team':  home_team,
                'away_team':  away_team,
                'league':     league,
                'country':    country,
                'match_time': match_time,
                'match_date': match_date,
                'markets':    markets,
                'home_odds':  m1x2['home'],
                'draw_odds':  m1x2.get('draw'),
                'away_odds':  m1x2['away'],
                'prediction': pred,
                'best_odds':  pred_odds,
                'confidence': conf,
                'source':     source,
                'home_form':  home_form or None,
                'away_form':  away_form or None,
            })

        print(f"    Odds API real odds: {sf_odds_hit}/{len(target)} matches")

        if not match_rows:
            continue

        # ── Pass 2a: select 3 premium accumulator picks ────────────────────────
        premium_picks = select_premium_three(match_rows)
        if premium_picks:
            combined = (
                round(premium_picks[0][2] * premium_picks[1][2] * premium_picks[2][2], 2)
                if len(premium_picks) == 3 else 0
            )
            print(f"    >> {len(premium_picks)} premium pick(s) for {date_str} (combined: {combined})")

        # ── Pass 2b: insert free tips ─────────────────────────────────────────
        for row in match_rows:
            if betting_tip_exists(cursor, row['home_team'], row['away_team'], row['match_time']):
                skipped += 1
                continue

            reasoning = generate_analysis(
                row['home_team'], row['away_team'], row['prediction'],
                row['markets'], row['league'], row['country'], source=row['source'],
            )

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
                'analyst':    'API-Football',
                'reasoning':  reasoning,
                'home_form':  row.get('home_form'),
                'away_form':  row.get('away_form'),
            })
            total_bt += 1
            print(f"      [NEW] {row['home_team']} vs {row['away_team']} → {row['prediction']} @ {row['best_odds']}")

            if sport_id and user_id:
                if not tip_exists(cursor, row['home_team'], row['away_team'], row['match_date']):
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
                    total_t += 1

        # ── Pass 2c: insert 3 premium accumulator tips ────────────────────────
        for row, prem_pred, prem_odds in premium_picks:
            cursor.execute(
                "SELECT id FROM betting_tips WHERE home_team=%s AND away_team=%s AND match_time=%s AND is_premium=1 LIMIT 1",
                (row['home_team'], row['away_team'], row['match_time']),
            )
            if cursor.fetchone():
                continue

            reasoning = generate_analysis(
                row['home_team'], row['away_team'], prem_pred,
                row['markets'], row['league'], row['country'], source=row['source'],
            )

            insert_betting_tip(cursor, {
                'sport':      'Football',
                'home_team':  row['home_team'],
                'away_team':  row['away_team'],
                'league':     row['league'],
                'country':    row['country'],
                'prediction': prem_pred,
                'confidence': 5,
                'odds':       prem_odds,
                'home_odds':  row['home_odds'],
                'draw_odds':  row['draw_odds'],
                'away_odds':  row['away_odds'],
                'match_time': row['match_time'],
                'is_premium': 1,
                'analyst':    'API-Football',
                'reasoning':  reasoning,
                'home_form':  row.get('home_form'),
                'away_form':  row.get('away_form'),
            })
            total_bt += 1
            print(f"      [VIP] {row['home_team']} vs {row['away_team']} → {prem_pred} @ {prem_odds}")

        conn.commit()

    print(f"\n  Fixtures done — betting_tips: +{total_bt} | tips: +{total_t} | skipped: {skipped}")


def update_results(conn, cursor):
    """Fetch yesterday's finished fixtures and update tip statuses."""
    today = datetime.now(timezone.utc)
    dates = [(today - timedelta(days=i)).strftime('%Y-%m-%d') for i in range(1, 3)]

    updated_bt = updated_t = 0

    for date_str in dates:
        print(f"\n  Checking results for {date_str}...")
        fixtures = get_fixtures(date_str)
        finished = [
            f for f in fixtures
            if f.get('fixture', {}).get('status', {}).get('short') in ('FT', 'AET', 'PEN')
            and f.get('league', {}).get('id') in TARGET_IDS
        ]
        print(f"    {len(finished)} finished target-league matches")

        for fix in finished:
            home = fix['teams']['home']['name']
            away = fix['teams']['away']['name']
            gh   = fix.get('goals', {}).get('home')
            ga   = fix.get('goals', {}).get('away')

            if gh is None or ga is None:
                continue
            gh, ga = int(gh), int(ga)

            cursor.execute("""
                SELECT id, prediction FROM betting_tips
                WHERE home_team=%s AND away_team=%s AND DATE(match_time)=%s
                  AND analyst='API-Football' AND status='pending'
                LIMIT 1
            """, (home, away, date_str))
            row = cursor.fetchone()
            if row:
                tip_id, pred = row
                new_status = evaluate_result(gh, ga, pred)
                if new_status:
                    cursor.execute(
                        "UPDATE betting_tips SET status=%s, updated_at=NOW() WHERE id=%s",
                        (new_status, tip_id),
                    )
                    updated_bt += 1
                    print(f"      {home} vs {away} {gh}:{ga} — {pred} → {new_status.upper()}")

            cursor.execute("""
                SELECT id, prediction FROM tips
                WHERE home_team=%s AND away_team=%s AND match_date=%s AND status='pending'
                LIMIT 1
            """, (home, away, date_str))
            tip_row = cursor.fetchone()
            if tip_row:
                tip_id, pred = tip_row
                new_status = evaluate_result(gh, ga, pred)
                if new_status:
                    cursor.execute(
                        "UPDATE tips SET status=%s, updated_at=NOW() WHERE id=%s",
                        (new_status, tip_id),
                    )
                    updated_t += 1

        conn.commit()

    print(f"\n  Results done — betting_tips: {updated_bt} updated | tips: {updated_t} updated")


# ── Basketball (The Odds API) ──────────────────────────────────────────────────

def _pick_basketball(home_odds: float, away_odds: float) -> tuple[str, float]:
    return ('Home Win', home_odds) if home_odds <= away_odds else ('Away Win', away_odds)


def _basketball_analysis(home: str, away: str, prediction: str,
                         home_odds: float, away_odds: float, league: str, country: str) -> str:
    margin = 1 / home_odds + 1 / away_odds
    ph = round((1 / home_odds / margin) * 100)
    pa = round((1 / away_odds / margin) * 100)
    ctx = f" in the {league}" if league else (f" in {country}" if country else "")
    if home_odds < away_odds - 0.05:
        fav, fav_p, fav_o, und, und_p, und_o = home, ph, home_odds, away, pa, away_odds
    elif away_odds < home_odds - 0.05:
        fav, fav_p, fav_o, und, und_p, und_o = away, pa, away_odds, home, ph, home_odds
    else:
        fav = None

    lines = [f"{home} host {away}{ctx}."]
    if fav:
        strength = "strong" if fav_p >= 65 else "moderate"
        lines.append(
            f"The market makes {fav} the {strength} favourite at {fav_o} ({fav_p}% implied), "
            f"with {und} priced at {und_o} ({und_p}%)."
        )
    else:
        lines.append(
            f"The market prices this as a tight contest — {home} {ph}% ({home_odds}) vs {away} {pa}% ({away_odds})."
        )
    if prediction == 'Home Win':
        lines.append(f"Our call is {home} to use home-court advantage and secure the win at {home_odds}.")
    else:
        lines.append(f"Our call is {away} to earn the road victory at {away_odds}.")
    gap = abs(home_odds - away_odds)
    if gap > 0.8:
        lines.append("Clear market pricing supports this as a high-confidence basketball pick.")
    elif gap > 0.3:
        lines.append("Market signals lean our way — a solid selection in today's basketball slate.")
    else:
        lines.append("A competitive matchup, but the weight of evidence tips in favour of our selection.")
    return " ".join(lines)


def scrape_basketball_fixtures(conn, cursor):
    """Fetch upcoming basketball fixtures + odds from The Odds API and insert tips."""
    if not ODDS_API_KEY:
        print("  [Basketball] No ODDS_API_KEY set — skipping")
        return

    inserted = skipped = 0

    for sport in BASKETBALL_SPORTS:
        sport_key = sport['key']
        league    = sport['name']
        country   = sport['country']
        events    = _fetch_odds_api_league(sport_key)

        if not events:
            continue

        print(f"  [Basketball] {league}: {len(events)} events")

        for ev in events:
            home_team = ev.get('home_team', '')
            away_team = ev.get('away_team', '')
            if not home_team or not away_team:
                continue

            # Parse match time
            commence = ev.get('commence_time', '')
            try:
                match_dt   = datetime.fromisoformat(commence.replace('Z', '+00:00')).astimezone(LOCAL_TZ)
                match_time = match_dt.strftime('%Y-%m-%d %H:%M:%S')
            except Exception:
                continue

            # Only take events in the next 3 days
            now = datetime.now(LOCAL_TZ)
            if match_dt < now or match_dt > now + timedelta(days=3):
                continue

            if betting_tip_exists(cursor, home_team, away_team, match_time):
                skipped += 1
                continue

            # Average h2h odds across bookmakers
            home_vals, away_vals = [], []
            for bm in (ev.get('bookmakers') or []):
                for mkt in (bm.get('markets') or []):
                    if mkt.get('key') != 'h2h':
                        continue
                    by_name = {o['name']: o['price'] for o in mkt.get('outcomes', [])
                               if 'name' in o and 'price' in o}
                    h_p = by_name.get(home_team)
                    a_p = by_name.get(away_team)
                    if h_p and a_p:
                        home_vals.append(h_p)
                        away_vals.append(a_p)

            if not home_vals or not away_vals:
                skipped += 1
                continue

            home_odds = round(sum(home_vals) / len(home_vals), 2)
            away_odds = round(sum(away_vals) / len(away_vals), 2)
            prediction, odds = _pick_basketball(home_odds, away_odds)

            # Flag the most one-sided match per league as premium
            is_vip = 0  # set below after collecting all rows for this league
            insert_betting_tip(cursor, {
                'sport':      'Basketball',
                'home_team':  home_team,
                'away_team':  away_team,
                'league':     league,
                'country':    country,
                'prediction': prediction,
                'confidence': 3,
                'odds':       odds,
                'home_odds':  home_odds,
                'draw_odds':  None,
                'away_odds':  away_odds,
                'match_time': match_time,
                'is_premium': 0,
                'analyst':    'OddsAPI',
                'reasoning':  _basketball_analysis(
                    home_team, away_team, prediction,
                    home_odds, away_odds, league, country,
                ),
                'home_form':  None,
                'away_form':  None,
            })
            inserted += 1
            print(f"    [NEW] {home_team} vs {away_team} → {prediction} @ {odds}")

        conn.commit()

    print(f"\n  [Basketball] Done — {inserted} inserted, {skipped} skipped")


def update_basketball_results(conn, cursor):
    """Use The Odds API scores endpoint to mark finished basketball tips won/lost."""
    if not ODDS_API_KEY:
        return

    updated = 0

    for sport in BASKETBALL_SPORTS:
        sport_key = sport['key']
        try:
            r = requests.get(
                f'{ODDS_API_BASE}/{sport_key}/scores/',
                params={'apiKey': ODDS_API_KEY, 'daysFrom': 3},
                timeout=15,
            )
            if r.status_code != 200:
                continue
            scores = r.json()
        except Exception:
            continue

        for ev in scores:
            if not ev.get('completed'):
                continue

            home_team = ev.get('home_team', '')
            away_team = ev.get('away_team', '')
            commence  = ev.get('commence_time', '')

            try:
                match_dt   = datetime.fromisoformat(commence.replace('Z', '+00:00')).astimezone(LOCAL_TZ)
                match_time = match_dt.strftime('%Y-%m-%d %H:%M:%S')
            except Exception:
                continue

            score_map = {s['name']: s.get('score') for s in (ev.get('scores') or [])}
            gh = score_map.get(home_team)
            ga = score_map.get(away_team)
            if gh is None or ga is None:
                continue

            try:
                gh, ga = int(gh), int(ga)
            except (ValueError, TypeError):
                continue

            cursor.execute("""
                SELECT id, prediction FROM betting_tips
                WHERE home_team=%s AND away_team=%s AND match_time=%s
                  AND sport='Basketball' AND status='pending'
            """, (home_team, away_team, match_time))

            for bt_id, pred in cursor.fetchall():
                new_status = evaluate_result(gh, ga, pred)
                if new_status:
                    cursor.execute(
                        "UPDATE betting_tips SET status=%s, updated_at=NOW() WHERE id=%s",
                        (new_status, bt_id),
                    )
                    updated += 1
                    print(f"  [Basketball] {home_team} vs {away_team} {gh}:{ga} — {pred} → {new_status.upper()}")

        conn.commit()

    print(f"  [Basketball] Results updated: {updated}")


# ── Entry point ────────────────────────────────────────────────────────────────

def main():
    if not API_KEY:
        print("ERROR: API_FOOTBALL_KEY is not set in .env")
        print("  Get a free key at: https://dashboard.api-football.com")
        sys.exit(1)

    parser = argparse.ArgumentParser(description='BallSignals API-Football scraper')
    parser.add_argument('--results-only',  action='store_true')
    parser.add_argument('--fixtures-only', action='store_true')
    args = parser.parse_args()

    print("=== BallSignals — API-Football Scraper ===")

    conn     = connect_db()
    cursor   = conn.cursor()
    sport_id = get_sport_id(cursor)
    user_id  = get_admin_user_id(cursor)

    # ── ML model: train if needed, then report status ─────────────────────────
    print("\n[ML] Checking prediction model...")
    if ml_predictor.needs_training():
        ml_predictor.train(cursor)
    else:
        print(f"  [ML] Model ready — {ml_predictor.model_info()}")

    try:
        if not args.results_only:
            print("\n[1/3] Scraping football fixtures + odds...")
            scrape_fixtures(conn, cursor, sport_id, user_id)

            print("\n[2/3] Scraping basketball fixtures + odds...")
            scrape_basketball_fixtures(conn, cursor)

        if not args.fixtures_only:
            print("\n[3/3] Updating results...")
            update_results(conn, cursor)
            update_basketball_results(conn, cursor)
    finally:
        cursor.close()
        conn.close()

    print("\n=== Done ===")


if __name__ == '__main__':
    main()
