from curl_cffi import requests
from datetime import datetime, timezone

headers = {
    'Accept': 'application/json, text/plain, */*',
    'Referer': 'https://www.sofascore.com/',
    'Origin': 'https://www.sofascore.com',
}

TARGET = {17, 8, 23, 35, 7, 34}
today = datetime.now(timezone.utc).strftime('%Y-%m-%d')

print(f"Testing Sofascore API for date: {today}")
r = requests.get(
    f'https://api.sofascore.com/api/v1/sport/football/scheduled-events/{today}',
    headers=headers, timeout=15, impersonate="chrome124"
)
print(f"Status: {r.status_code}")

if r.status_code == 200:
    events = r.json().get('events', [])
    filtered = [e for e in events if e.get('tournament', {}).get('uniqueTournament', {}).get('id') in TARGET]
    print(f"Total events today: {len(events)}")
    print(f"Target league matches: {len(filtered)}")
    print()
    for e in filtered[:5]:
        home = e['homeTeam']['name']
        away = e['awayTeam']['name']
        league = e['tournament']['name']
        status = e.get('status', {}).get('type', 'unknown')
        print(f"  [{status}] {home} vs {away} ({league})")

    # Test odds for first event
    if filtered:
        eid = filtered[0]['id']
        print(f"\nTesting odds for event {eid}...")
        ro = requests.get(
            f'https://api.sofascore.com/api/v1/event/{eid}/odds/1/featured',
            headers=headers, timeout=15, impersonate="chrome124"
        )
        print(f"Odds status: {ro.status_code}")
        if ro.status_code == 200:
            import json
            print(json.dumps(ro.json(), indent=2)[:800])
else:
    print("Response:", r.text[:300])
