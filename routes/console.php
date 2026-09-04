<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Allows shared hosting to process queued school emails through one scheduler cron.
Schedule::command('queue:work database --queue=emails --stop-when-empty --tries=3 --timeout=180')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
