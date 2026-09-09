<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Отправка по запросу (кнопка "Отправить на почту" на странице списка сотрудников)
 * того же Excel-файла, что и при обычном скачивании — тому же адресату, который
 * нажал кнопку. Без ShouldQueue: файл удаляется сразу после отправки, гонка с
 * очередью (которая на этом проекте не гарантированно поднята) недопустима.
 */
class EmployeeExportEmailNotification extends Notification
{
    public function __construct(
        public string $title,
        public int $count,
        public string $filePath,
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject("Экспорт: {$this->title}")
            ->greeting('Здравствуйте!')
            ->line("Список «{$this->title}» — {$this->count} " . $this->pluralizeEmployees($this->count) . '.')
            ->line('Полный список — во вложении.');

        if (is_file($this->filePath)) {
            $message->attach($this->filePath, [
                'as'   => \Illuminate\Support\Str::slug($this->title) . '.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return $message->salutation('— ' . config('app.name'));
    }

    private function pluralizeEmployees(int $count): string
    {
        $mod10  = $count % 10;
        $mod100 = $count % 100;

        if ($mod10 === 1 && $mod100 !== 11) {
            return 'сотрудник';
        }
        if (in_array($mod10, [2, 3, 4]) && !in_array($mod100, [12, 13, 14])) {
            return 'сотрудника';
        }
        return 'сотрудников';
    }
}
