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

// Дайджест уволенных/декрет — раз в 2 недели по понедельникам в 08:00,
// первый раз 2026-11-02. Планировщик сам умеет только "каждую неделю",
// поэтому через ->when() пропускаем ровно через раз. Чётность считаем от
// числа полных недель с опорной даты (не от номера недели в году — тот в
// декабре/январе переходит 52/53 -> 1, и чётность могла бы сбиться раз в год).
Schedule::command('report:weekly-dismissed')->weeklyOn(1, '08:00')
    ->when(fn() => now()->diffInWeeks(\Illuminate\Support\Carbon::parse('2026-11-02')) % 2 === 0)
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/weekly_dismissed_report.log'));
