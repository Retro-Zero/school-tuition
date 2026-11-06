<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly database backup (needs `php artisan schedule:run` in cron).
Schedule::command('db:backup')->dailyAt('02:30')->withoutOverlapping();
