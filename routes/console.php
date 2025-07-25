<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the PMS synchronization command if enabled
if (config('pms.cron.enabled', true)) {
    // Full sync - runs every 5 minutes by default
    Schedule::command('sync:bookings')
        ->everyFiveMinutes()
        ->withoutOverlapping()
        ->runInBackground()
        ->onFailure(function () {
            Log::error('PMS full synchronization failed');
        })
        ->onSuccess(function () {
            Log::info('PMS full synchronization completed successfully');
        });

    // Incremental sync - runs every hour by default
    Schedule::command('sync:bookings --since=' . config('pms.cron.incremental_since', '1 hour ago'))
        ->hourly()
        ->withoutOverlapping()
        ->runInBackground()
        ->onFailure(function () {
            Log::error('PMS incremental synchronization failed');
        })
        ->onSuccess(function () {
            Log::info('PMS incremental synchronization completed successfully');
        });
}
