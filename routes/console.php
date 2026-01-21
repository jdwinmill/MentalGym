<?php

use App\Jobs\SendWeeklyBlindSpotEmails;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly blind spot emails - Sunday at 6pm ET
Schedule::job(new SendWeeklyBlindSpotEmails)
    ->weeklyOn(0, '18:00') // 0 = Sunday
    ->timezone('America/New_York')
    ->withoutOverlapping();

// Manual command to trigger weekly emails (for testing)
Artisan::command('blindspots:send-weekly', function () {
    SendWeeklyBlindSpotEmails::dispatch();
    $this->info('Weekly blind spot emails job dispatched.');
})->purpose('Dispatch the weekly blind spot emails job');
