<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Еженедельный дайджест уволенных и ушедших в декрет за прошедшую неделю (пн-вс),
 * с Excel-вложением. Отправляется синхронно из SendWeeklyDismissedReport сразу
 * после сборки файла — без ShouldQueue, чтобы не было гонки между удалением
 * временного файла командой и обработкой очереди воркером (который на этом
 * проекте не гарантированно поднят).
 */
class WeeklyDismissedReportNotification extends Notification
{
    public function __construct(
        public int $dismissedCount,
        public int $maternityCount,
        public string $from,
        public string $to,
        public string $filePath,
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $fromFmt = \Carbon\Carbon::parse($this->from)->format('d.m.Y');
        $toFmt   = \Carbon\Carbon::parse($this->to)->format('d.m.Y');
        $total   = $this->dismissedCount + $this->maternityCount;

        $message = (new MailMessage)
            ->subject("Уволенные и в декрете — отмечено на неделе {$fromFmt} — {$toFmt}")
            ->greeting('Здравствуйте!');

        if ($total > 0) {
            $message->line("На неделе {$fromFmt} — {$toFmt} в системе отмечено: уволено — {$this->dismissedCount}, ушло в декрет — {$this->maternityCount}.")
                     ->line('Обратите внимание: дата отметки в системе может отличаться от фактической даты события (см. колонку «Дата» во вложении) — в отчёт попадают все события, зафиксированные за эту неделю, включая отмеченные задним числом.')
                     ->line('Полный список — во вложении.');
        } else {
            $message->line("На неделе {$fromFmt} — {$toFmt} новых увольнений и уходов в декрет в системе отмечено не было.");
        }

        if (is_file($this->filePath)) {
            $message->attach($this->filePath, [
                'as'   => 'uvoleno_i_dekret_' . $this->from . '_' . $this->to . '.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return $message->salutation('— ' . config('app.name'));
    }
}
