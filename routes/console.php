<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scrape full week of fixtures every Monday at 5:00 AM
Schedule::command('tips:scrape', ['--week', '--fixtures-only'])->weekly()->mondays()->at('05:00');

// Scrape today + tomorrow fixtures daily at 6:00 AM (catches late additions)
Schedule::command('tips:scrape', ['--fixtures-only'])->dailyAt('06:00');

// Backfill team form on any tips missing it — runs at 6:30 AM after fixture scrape
Schedule::command('tips:scrape', ['--update-form'])->dailyAt('06:30');

// Send daily tips to all newsletter subscribers every day at 8:00 AM
Schedule::command('tips:send-daily')->dailyAt('08:00');

// Post today's free tips + premium teaser to Telegram at 8:05 AM
Schedule::command('tips:telegram')->dailyAt('08:05');

// Mark expired subscriptions + send renewal/expiry emails daily at 9:00 AM
Schedule::command('subscriptions:renew')->dailyAt('09:00');

// Update won/lost results every 15 minutes — within 15 min of any match finishing
Schedule::command('tips:scrape', ['--results-only'])->everyFifteenMinutes();

// Post yesterday's results summary to Telegram at 11:00 PM
Schedule::command('tips:telegram', ['--results'])->dailyAt('23:00');

// Auto-publish weekly football news roundup every Saturday at 9:00 AM
Schedule::command('news:post', ['--sport' => 'football'])->weekly()->saturdays()->at('09:00');

// Auto-publish weekly basketball news roundup every Wednesday at 9:00 AM
Schedule::command('news:post', ['--sport' => 'basketball'])->weekly()->wednesdays()->at('09:00');
