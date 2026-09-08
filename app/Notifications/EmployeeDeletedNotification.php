<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Тестовое уведомление для обкатки почтовой отправки: письмо тому же
 * админу, который удалил сотрудника (см. EmployeeController::deleteEmployee).
 */
class EmployeeDeletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $employeeFullName,
        public string $deletedByName,
    ) {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Сотрудник удалён из системы')
            ->greeting('Здравствуйте!')
            ->line("Сотрудник «{$this->employeeFullName}» был удалён из системы.")
            ->line("Действие выполнил: {$this->deletedByName}")
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'employee_full_name' => $this->employeeFullName,
            'deleted_by'          => $this->deletedByName,
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'employee_full_name' => $this->employeeFullName,
        ];
    }
}
