"""
Quick script to verify Sofascore tournament IDs for world leagues.
Run: python scraper/find_league_ids.py
"""
from curl_cffi import requests
from datetime import datetime, timezone

SESSION = requests.Session(impersonate="chrome124")
SESSION.headers.update({
    'Accept': 'application/json, text/plain, */*',
    'Referer': 'https://www.sofascore.com/',
    'Origin': 'https://www.sofascore.com',
})

CANDIDATES = {
    # Europe
    17:   'Premier League (England)',
    8:    'La Liga (Spain)',
    23:   'Serie A (Italy)',
    35:   'Bundesliga (Germany)',
    7:    'Champions League',
    34:   'Ligue 1 (France)',
    37:   'Eredivisie (Netherlands)',
    238:  'Primeira Liga (Portugal)',
    36:   'Scottish Premiership',
    52:   'Super Lig (Turkey)',
    679:  'Europa League',
    17015:'Conference League',
    38:   'Belgian First Division A',
    # Americas
    242:  'MLS (USA)',
    325:  'Brasileirao Serie A',
    155:  'Argentine Primera Division',
    10:   'Liga MX (Mexico)',
    435:  'Copa Libertadores',
    # Africa
    670:  'PSL (South Africa)',
    519:  'Egypt Premier League',
    390:  'CAF Champions League',
    1977: 'NPFL (Nigeria)',
    1361: 'Kenya Premier League',
    583:  'Ghana Premier League',
    1307: 'Uganda Premier League',
    329:  'Morocco Botola Pro',
    # Asia / Middle East
    955:  'Saudi Pro League',
    196:  'J1 League (Japan)',
    282:  'Chinese Super League',
    124:  'K League 1 (South Korea)',
    1267: 'Indian Super League',
    1084: 'UAE Pro League',
}

today = datetime.now(timezone.utc).strftime('%Y-%m-%d')
r = SESSION.get(
    f'https://api.sofascore.com/api/v1/sport/football/scheduled-events/{today}',
    timeout=20,
)
events = r.json().get('events', []) if r.status_code == 200 else []

# Build a set of IDs that appear in today's schedule
found_ids = {}
for e in events:
    tid = e.get('tournament', {}).get('uniqueTournament', {}).get('id')
    tname = e.get('tournament', {}).get('name', '')
    if tid and tid not in found_ids:
        found_ids[tid] = tname

print(f"Checking {len(CANDIDATES)} candidate leagues against today's schedule ({today}):\n")
active, inactive = [], []
for tid, label in CANDIDATES.items():
    if tid in found_ids:
        active.append((tid, label, found_ids[tid]))
    else:
        inactive.append((tid, label))

print("ACTIVE TODAY:")
for tid, label, actual in active:
    print(f"  {tid:6d}  {label}  (Sofascore name: {actual})")

print(f"\nNOT SCHEDULED TODAY ({len(inactive)}) — may still be valid on other dates:")
for tid, label in inactive:
    print(f"  {tid:6d}  {label}")
