<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Nobel CRM ETL — ежедневно в 02:00
Schedule::command('etl:nobel')->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/nobel_etl_scheduler.log'));

// Еженедельный дайджест уволенных (прошедшая пн-вс) — каждый понедельник в 08:00
Schedule::command('report:weekly-dismissed')->weeklyOn(1, '08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/weekly_dismissed_report.log'));
