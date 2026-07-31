<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('game:process-investments')->hourly();
Schedule::command('game:weekly-emails')->weeklyOn(1, '09:00');
Schedule::command('game:process-crises')->hourly();
Schedule::command('subscriptions:remind')->dailyAt('08:00');
Schedule::command('teachers:weekly-digest')->weeklyOn(1, '07:30');
Schedule::command('push:predictive-check')->everyThirtyMinutes();
Schedule::command('reallife:remind')->dailyAt('07:30');
Schedule::command('game:sweep-quests')->dailyAt('02:30');
