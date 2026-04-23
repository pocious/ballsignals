<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send daily tips to all contact form subscribers every day at 8:00 AM
Schedule::command('tips:send-daily')->dailyAt('08:00');
