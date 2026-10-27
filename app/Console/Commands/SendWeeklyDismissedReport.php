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
    protected $description = 'Отправить админам список уволенных и ушедших в декрет сотрудников за последние 2 недели на почту (раз в 2 недели, см. routes/console.php)';

    public function handle(EmployeeEventStatsService $stats): int
    {
        $periodEnd   = now()->subWeek()->endOfWeek();
        $periodStart = now()->subWeeks(2)->startOfWeek();
        $from        = $periodStart->toDateString();
        $to          = $periodEnd->toDateString();

        $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();
        if ($admins->isEmpty()) {
            $this->warn('Нет пользователей с ролью admin — отправлять некому.');
            return self::SUCCESS;
        }

        // По updated_at, а не по event_date: событие, отмеченное постфактум
        // (event_date — месяцы назад), должно попасть в ближайший отчёт, а не
        // быть пропущено из-за того, что сама дата события вне диапазона.
        $employees = $stats->getByUpdatedRange(['dismissed', 'maternity_leave'], $periodStart->toDateTimeString(), $periodEnd->toDateTimeString());
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
