<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\WeeklyDismissedReportNotification;
use App\Services\EmployeeEventStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendWeeklyDismissedReport extends Command
{
    protected $signature = 'report:weekly-dismissed';
    protected $description = 'Отправить админам еженедельный список уволенных сотрудников (прошедшая пн-вс) на почту';

    public function handle(EmployeeEventStatsService $stats): int
    {
        $from = now()->subWeek()->startOfWeek()->toDateString();
        $to   = now()->subWeek()->endOfWeek()->toDateString();

        $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();
        if ($admins->isEmpty()) {
            $this->warn('Нет пользователей с ролью admin — отправлять некому.');
            return self::SUCCESS;
        }

        $employees = $stats->getByDateRange('dismissed', $from, $to);
        $filePath  = $stats->buildEventsExcelFile($employees, "Уволенные {$from} — {$to}");

        Notification::send($admins, new WeeklyDismissedReportNotification(
            $employees->count(),
            $from,
            $to,
            $filePath,
        ));

        @unlink($filePath);

        $this->info("Отправлено {$admins->count()} админам. Уволенных за период {$from} — {$to}: {$employees->count()}.");
        return self::SUCCESS;
    }
}
