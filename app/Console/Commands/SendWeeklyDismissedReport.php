<?php

namespace App\Console\Commands;

use App\Models\ReportSendLog;
use App\Models\User;
use App\Notifications\WeeklyDismissedReportNotification;
use App\Services\EmployeeEventStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendWeeklyDismissedReport extends Command
{
    protected $signature = 'report:weekly-dismissed';
    protected $description = 'Отправить админам список уволенных и ушедших в декрет сотрудников с момента прошлой отправки (авто — раз в 2 недели, см. routes/console.php) на почту';

    private const REPORT_KEY = 'dismissed_maternity_digest';

    public function handle(EmployeeEventStatsService $stats): int
    {
        $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();
        if ($admins->isEmpty()) {
            $this->warn('Нет пользователей с ролью admin — отправлять некому.');
            return self::SUCCESS;
        }

        // Окно — "с прошлой успешной отправки до сейчас", а не фиксированные
        // N дней назад: если авто-рассылка однажды не сработала (сервер лежал,
        // деплой и т.п.) или отчёт долго не запускали кнопкой, следующий
        // запуск всё равно захватит весь накопившийся период целиком, без
        // пропусков. Для самого первого запуска (лога ещё нет) — те же
        // "прошлые 2 недели", что и раньше.
        $periodStart = ReportSendLog::lastSentAt(self::REPORT_KEY) ?? now()->subWeeks(2);
        $periodEnd   = now();
        $from        = $periodStart->toDateString();
        $to          = $periodEnd->toDateString();

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

        ReportSendLog::markSent(self::REPORT_KEY, $periodEnd);

        $this->info("Отправлено {$admins->count()} админам. За период {$from} — {$to}: уволено {$dismissedCount}, в декрете {$maternityCount}.");
        return self::SUCCESS;
    }
}
