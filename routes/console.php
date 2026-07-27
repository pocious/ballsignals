<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Log every scheduler invocation so we can verify cron frequency
Schedule::call(function () {
    file_put_contents(
        storage_path('logs/scheduler_heartbeat.log'),
        date('Y-m-d H:i:s') . " UTC schedule:run called\n",
        FILE_APPEND
    );
})->everyMinute();

// Scrape full week of fixtures every Monday at 5:00 AM (Sofascore)
Schedule::command('tips:scrape', ['--week', '--fixtures-only'])->weekly()->mondays()->at('05:00');

// Scrape full week from Odds API every Monday at 5:30 AM (fallback when Sofascore is blocked)
Schedule::command('tips:scrape', ['--odds-api'])->weekly()->mondays()->at('05:30');

// Scrape today + tomorrow football fixtures daily at 6:00 AM
Schedule::command('tips:scrape', ['--football-only'])->dailyAt('06:00');

// Scrape all other sports (basketball, tennis, cricket, MMA, etc.) weekly on Sunday at 6:30 AM
Schedule::command('tips:scrape', ['--all-sports'])->weekly()->sundays()->at('06:30');

// Form update disabled — saves API-Football credits
// Schedule::command('tips:scrape', ['--update-form'])->dailyAt('06:30');

// Send daily tips to all newsletter subscribers every day at 8:00 AM
Schedule::command('tips:send-daily')->dailyAt('08:00');

// Post today's free tips + premium teaser to Telegram at 8:05 AM
Schedule::command('tips:telegram')->dailyAt('08:05');

// Mark expired subscriptions + send renewal/expiry emails daily at 9:00 AM
Schedule::command('subscriptions:renew')->dailyAt('09:00');

// Update won/lost results every hour (reduced from 15 min to save Odds API credits)
Schedule::command('tips:scrape', ['--results-only'])->hourly();

// Fetch league standings daily at 10:00 AM
Schedule::command('tips:scrape', ['--standings'])->dailyAt('10:00');

// Fetch top scorers every Monday at 10:30 AM
Schedule::command('tips:scrape', ['--top-scorers'])->weekly()->mondays()->at('10:30');

// Fetch top assists every Monday at 10:45 AM
Schedule::command('tips:scrape', ['--top-assists'])->weekly()->mondays()->at('10:45');

// Fetch starting lineups at noon and 2 PM (not announced until ~2h before kick-off)
Schedule::command('tips:scrape', ['--lineups'])->dailyAt('12:00');
Schedule::command('tips:scrape', ['--lineups'])->dailyAt('14:00');
Schedule::command('tips:scrape', ['--lineups'])->dailyAt('16:00');

// Fetch match statistics for recently settled fixtures every 2 hours
Schedule::command('tips:scrape', ['--match-stats'])->everyTwoHours();

// Post yesterday's results summary to Telegram at 11:00 PM
Schedule::command('tips:telegram', ['--results'])->dailyAt('23:00');

// Fetch latest football news from RSS feeds every 6 hours
Schedule::command('news:fetch-football')->everySixHours();

// Auto-publish weekly football news roundup every Saturday at 9:00 AM
Schedule::command('news:post', ['--sport' => 'football'])->weekly()->saturdays()->at('09:00');

// Auto-publish weekly basketball news roundup every Wednesday at 9:00 AM
Schedule::command('news:post', ['--sport' => 'basketball'])->weekly()->wednesdays()->at('09:00');
