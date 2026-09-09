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
    protected $description = 'Отправить админам еженедельный список уволенных и ушедших в декрет сотрудников (прошедшая пн-вс) на почту';

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

        // По updated_at, а не по event_date: событие, отмеченное постфактум
        // (event_date — месяцы назад), должно попасть в ближайший отчёт, а не
        // быть пропущено из-за того, что сама дата события вне диапазона недели.
        $employees = $stats->getByUpdatedRange(['dismissed', 'maternity_leave'], $weekStart->toDateTimeString(), $weekEnd->toDateTimeString());
        $dismissedCount = $employees->where('event_type', 'dismissed')->count();
        $maternityCount = $employees->where('event_type', 'maternity_leave')->count();
        $filePath  = $stats->buildEventsExcelFile($employees, "Уволенные и в декрете, отмечено {$from} — {$to}");

        Notification::send($admins, new WeeklyDismissedReportNotification(
            $dismissedCount,
            $maternityCount,
            $from,
            $to,
            $filePath,
        ));

        @unlink($filePath);

        $this->info("Отправлено {$admins->count()} админам. За период {$from} — {$to}: уволено {$dismissedCount}, в декрете {$maternityCount}.");
        return self::SUCCESS;
    }
}
