<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Дайджест уволенных и ушедших в декрет за период с прошлой отправки (см.
 * SendWeeklyDismissedReport — раз в 2 недели по расписанию, либо вручную по
 * кнопке), с Excel-вложением. Отправляется синхронно сразу после сборки
 * файла — без ShouldQueue, чтобы не было гонки между удалением временного
 * файла командой и обработкой очереди воркером (который на этом проекте не
 * гарантированно поднят).
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
            ->subject("Уволенные и в декрете — отмечено за период {$fromFmt} — {$toFmt}")
            ->greeting('Здравствуйте!');

        if ($total > 0) {
            $message->line("За период {$fromFmt} — {$toFmt}: уволено — {$this->dismissedCount}, в декрете — {$this->maternityCount}.")
                     ->line('Список — во вложении. Дата в файле — фактическая дата события, она может отличаться от даты добавления в систему.');
        } else {
            $message->line("За период {$fromFmt} — {$toFmt} увольнений и декретов не было.");
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
