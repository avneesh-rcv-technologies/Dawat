<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('blogs:publish-scheduled-blogs')
    ->everyMinute()
    ->withoutOverlapping();
// Activitylog maintenance: hourly dashboard rollups, daily cleanup/archiving, queue health checks.
Schedule::job(new \Modules\Activitylog\Jobs\AggregateRequestActivityLogRollupsJob())
    ->hourly()
    ->withoutOverlapping();

Schedule::job(new \Modules\Activitylog\Jobs\CleanupRequestLogsJob())
    ->dailyAt('02:30')
    ->withoutOverlapping();

Schedule::job(new \Modules\Activitylog\Jobs\MonitorActivityLogQueueJob())
    ->everyFifteenMinutes()
    ->withoutOverlapping();
