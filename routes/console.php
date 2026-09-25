<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('queue:work --sleep=3 --timeout=280 --max-time=280')
    ->everyMinute()
    ->withoutOverlapping(5) // Lock expires after 5 mins to prevent stuck locks
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/queue-work.log'));

Schedule::command('contests:transition')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/contests-transition.log'));

Schedule::command('support:flag-overdue')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/support-sla.log'));

Schedule::command('support:auto-close')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/support-sla.log'));
