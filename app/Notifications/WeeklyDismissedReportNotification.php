<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Еженедельный дайджест уволенных за прошедшую неделю (пн-вс), с Excel-вложением.
 * Отправляется синхронно из SendWeeklyDismissedReport сразу после сборки файла —
 * без ShouldQueue, чтобы не было гонки между удалением временного файла командой
 * и обработкой очереди воркером (который на этом проекте не гарантированно поднят).
 */
class WeeklyDismissedReportNotification extends Notification
{
    public function __construct(
        public int $count,
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

        $message = (new MailMessage)
            ->subject("Уволенные за неделю ({$fromFmt} — {$toFmt})")
            ->greeting('Здравствуйте!');

        if ($this->count > 0) {
            $message->line("За период {$fromFmt} — {$toFmt} уволено сотрудников: {$this->count}.")
                     ->line('Полный список — во вложении.');
        } else {
            $message->line("За период {$fromFmt} — {$toFmt} увольнений не было.");
        }

        if (is_file($this->filePath)) {
            $message->attach($this->filePath, [
                'as'   => 'dismissed_' . $this->from . '_' . $this->to . '.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return $message->salutation('— ' . config('app.name'));
    }
}
