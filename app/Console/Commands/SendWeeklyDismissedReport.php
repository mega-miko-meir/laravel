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
        $weekStart = now()->subWeek()->startOfWeek();
        $weekEnd   = now()->subWeek()->endOfWeek();
        $from      = $weekStart->toDateString();
        $to        = $weekEnd->toDateString();

        $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();
        if ($admins->isEmpty()) {
            $this->warn('Нет пользователей с ролью admin — отправлять некому.');
            return self::SUCCESS;
        }

        // По updated_at, а не по event_date: увольнение, отмеченное постфактум
        // (event_date — месяцы назад), должно попасть в ближайший отчёт, а не
        // быть пропущено из-за того, что сама дата увольнения вне диапазона недели.
        $employees = $stats->getByUpdatedRange('dismissed', $weekStart->toDateTimeString(), $weekEnd->toDateTimeString());
        $filePath  = $stats->buildEventsExcelFile($employees, "Уволенные, отмечено {$from} — {$to}");

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
