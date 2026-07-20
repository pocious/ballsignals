"""
BallSignals — AI Blog Generator
Generates SEO-optimised football/basketball blog posts using Google Gemini (free tier).
Pulls upcoming tips from the DB as context — no external scraping required.

Usage:
    python scraper/fetch_news.py --sport football
    python scraper/fetch_news.py --sport basketball

Requires GEMINI_API_KEY in .env
Free key: https://aistudio.google.com/apikey  (1,500 req/day, no credit card)
"""

import os
import sys
import json
import argparse
import re
from datetime import datetime, timezone, timedelta

import requests
import mysql.connector
from dotenv import load_dotenv

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

load_dotenv(os.path.join(os.path.dirname(__file__), '..', '.env'))

GEMINI_API_KEY = os.getenv('GEMINI_API_KEY', '')
GEMINI_URL = (
    'https://generativelanguage.googleapis.com/v1beta'
    '/models/gemini-1.5-flash:generateContent'
)

DB_CONFIG = {
    'host':     os.getenv('DB_HOST', '127.0.0.1'),
    'port':     int(os.getenv('DB_PORT', 3306)),
    'database': os.getenv('DB_DATABASE', 'ballsignals'),
    'user':     os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'charset':  'utf8mb4',
}

SEO = {
    'football': {
        'title_prefix': 'Football Betting Tips & Predictions',
        'keywords': (
            'football betting tips, Premier League predictions, football tips today, '
            'Champions League tips, La Liga predictions, free football tips, '
            'weekend football predictions, football odds, soccer betting tips'
        ),
        'category': 'League News',
        'author':   'BallSignals',
        'cta': (
            '<h2>Free Football Betting Tips</h2>'
            '<p>Our analysts publish free daily football betting tips covering the Premier League, '
            'La Liga, Serie A, Bundesliga, Champions League, and more. Every pick is data-driven — '
            'built on form analysis, head-to-head stats, and live bookmaker odds.</p>'
            '<p>'
            '<a href="/" style="display:inline-block;padding:5px 14px;background:#16a34a;color:#fff;'
            'border-radius:999px;font-weight:700;font-size:0.75rem;text-decoration:none;'
            'margin-right:10px;box-shadow:0 1px 4px rgba(22,163,74,0.18);">View Free Football Tips &rarr;</a>'
            '<a href="/premium" style="display:inline-block;padding:5px 14px;background:#ca8a04;color:#fff;'
            'border-radius:999px;font-weight:700;font-size:0.75rem;text-decoration:none;'
            'box-shadow:0 1px 4px rgba(202,138,4,0.18);">Unlock VIP Picks &rarr;</a>'
            '</p>'
        ),
        'excerpt_tmpl': (
            'Football betting tips & predictions for {date}. '
            'Expert analysis covering Premier League, Champions League, La Liga, Bundesliga and more.'
        ),
    },
    'basketball': {
        'title_prefix': 'Basketball Betting Tips & Predictions',
        'keywords': (
            'basketball betting tips, NBA predictions, NBA tips today, EuroLeague predictions, '
            'basketball tips this week, NBA betting, free basketball tips, basketball odds, '
            'NBA picks today, basketball betting advice'
        ),
        'category': 'League News',
        'author':   'BallSignals',
        'cta': (
            '<h2>Free Basketball Betting Tips</h2>'
            '<p>Our analysts publish free basketball betting tips covering the NBA, EuroLeague, ACB, '
            'BBL, and more. Every pick is backed by form data, head-to-head records, and live odds.</p>'
            '<p>'
            '<a href="/basketball" style="display:inline-block;padding:5px 14px;background:#ea580c;color:#fff;'
            'border-radius:999px;font-weight:700;font-size:0.75rem;text-decoration:none;'
            'margin-right:10px;box-shadow:0 1px 4px rgba(234,88,12,0.18);">View Free Basketball Tips &rarr;</a>'
            '<a href="/premium" style="display:inline-block;padding:5px 14px;background:#ca8a04;color:#fff;'
            'border-radius:999px;font-weight:700;font-size:0.75rem;text-decoration:none;'
            'box-shadow:0 1px 4px rgba(202,138,4,0.18);">Unlock VIP Picks &rarr;</a>'
            '</p>'
        ),
        'excerpt_tmpl': (
            'Basketball betting tips & predictions for {date}. '
            'Expert NBA, EuroLeague, and ACB analysis from BallSignals.'
        ),
    },
}


def get_tips(sport: str) -> list:
    """Fetch upcoming pending tips from the DB for the next 7 days."""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cur  = conn.cursor(dictionary=True)
        sport_name = 'Football' if sport == 'football' else 'Basketball'
        today = datetime.now(timezone.utc).strftime('%Y-%m-%d')
        end   = (datetime.now(timezone.utc) + timedelta(days=7)).strftime('%Y-%m-%d')
        cur.execute("""
            SELECT home_team, away_team, league, country, prediction, odds, match_time
            FROM betting_tips
            WHERE sport=%s AND DATE(match_time) BETWEEN %s AND %s
              AND is_premium=0 AND status='pending'
            ORDER BY match_time ASC
            LIMIT 20
        """, (sport_name, today, end))
        rows = cur.fetchall()
        cur.close()
        conn.close()
        return rows
    except Exception as e:
        print(f"  DB error: {e}", file=sys.stderr)
        return []


def build_prompt(sport: str, tips: list, date_str: str) -> str:
    if tips:
        tips_text = '\n'.join(
            f"- {r['home_team']} vs {r['away_team']} "
            f"({r['league']}, {r['country']}): {r['prediction']} @ {r['odds']}"
            for r in tips[:15]
        )
    else:
        tips_text = 'No specific fixtures in the database yet.'

    sport_label  = 'football' if sport == 'football' else 'basketball'
    leagues_hint = (
        'Premier League, La Liga, Bundesliga, Serie A, Ligue 1, Champions League'
        if sport == 'football' else
        'NBA, EuroLeague, ACB Liga, Lega Basket'
    )

    return f"""You are a professional sports betting analyst writing for BallSignals, a trusted {sport_label} betting tips website.

Write an SEO-optimised weekly {sport_label} betting analysis blog post for the week of {date_str}.

STRICT RULES:
- Output HTML only — use <h2>, <h3>, <p>, <ul>, <li> tags
- No <html>, <body>, <head>, no CSS, no inline styles, no markdown
- 450–600 words
- Professional, engaging, data-driven tone
- Mention real leagues: {leagues_hint}
- Sections to include:
  1. Weekly overview paragraph (name the week/date)
  2. Key matches & betting angles (reference the fixtures below where relevant)
  3. Tips strategy advice (e.g. value bets, accumulators, form)
  4. A closing paragraph telling readers to check BallSignals for free daily tips
- Do NOT invent specific scorelines or claim guaranteed wins

Upcoming fixtures on BallSignals this week:
{tips_text}

Write the HTML now (no preamble — start directly with the first <h2> or <p> tag):"""


def call_gemini(prompt: str) -> str | None:
    if not GEMINI_API_KEY:
        print("ERROR: GEMINI_API_KEY not set in .env", file=sys.stderr)
        print("  Get a free key at: https://aistudio.google.com/apikey", file=sys.stderr)
        return None

    payload = {
        'contents': [{'parts': [{'text': prompt}]}],
        'generationConfig': {
            'temperature':     0.75,
            'maxOutputTokens': 1400,
        },
    }
    try:
        r    = requests.post(f'{GEMINI_URL}?key={GEMINI_API_KEY}', json=payload, timeout=30)
        data = r.json()

        if 'error' in data:
            print(f"  Gemini API error: {data['error'].get('message')}", file=sys.stderr)
            return None

        return data['candidates'][0]['content']['parts'][0]['text']
    except Exception as e:
        print(f"  Gemini request failed: {e}", file=sys.stderr)
        return None


def build_post(sport: str, content_html: str) -> dict:
    today    = datetime.now()
    date_str = today.strftime('%B %d, %Y')
    seo      = SEO[sport]

    # Strip markdown code fences Gemini sometimes adds
    content_html = re.sub(r'^```html?\s*', '', content_html.strip(), flags=re.IGNORECASE)
    content_html = re.sub(r'```\s*$',       '', content_html.strip())

    full_content = '\n'.join([
        seo['cta'],
        '<hr>',
        content_html,
        '<hr>',
        f'<p><strong>Related searches:</strong> <em>{seo["keywords"]}</em></p>',
    ])

    return {
        'title':    f"{seo['title_prefix']} — {date_str}",
        'slug':     f"{sport}-betting-tips-predictions-{today.strftime('%Y-%m-%d')}",
        'category': seo['category'],
        'excerpt':  seo['excerpt_tmpl'].format(date=date_str)[:255],
        'content':  full_content,
        'author':   seo['author'],
    }


def main():
    parser = argparse.ArgumentParser(description='BallSignals AI blog generator')
    parser.add_argument('--sport', choices=['football', 'basketball'], required=True)
    args = parser.parse_args()

    sport = args.sport
    tips  = get_tips(sport)
    print(f"  {len(tips)} upcoming {sport} tips loaded from DB", file=sys.stderr)

    prompt  = build_prompt(sport, tips, datetime.now().strftime('%Y-%m-%d'))
    content = call_gemini(prompt)

    if not content:
        print(json.dumps({'error': 'Gemini returned no content'}))
        sys.exit(1)

    post = build_post(sport, content)
    print(json.dumps(post, ensure_ascii=False))


if __name__ == '__main__':
    main()
