<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('jobs:auto-close-filled')->hourly();
Schedule::command('jobs:auto-close-expired')->dailyAt('00:30');
Schedule::command('jobs:alert-stale')->dailyAt('08:00');
Schedule::command('noks:expire-pending')->hourly();
Schedule::command('selections:expire-pending')->hourly();

Schedule::command('workers:unfeature-expired')->dailyAt('00:15');
Schedule::command('iqama:expiry-alert')->dailyAt('08:30');


