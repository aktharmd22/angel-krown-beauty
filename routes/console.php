<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send next-day WhatsApp reminders every morning.
Schedule::command('app:send-booking-reminders')->dailyAt('10:00');

// Dispatch queued marketing broadcasts in batches.
Schedule::command('app:send-broadcasts')->everyMinute()->withoutOverlapping();
