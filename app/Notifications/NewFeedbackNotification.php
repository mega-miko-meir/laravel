<?php

namespace App\Notifications;

use App\Models\Feedback; // ← добавляем правильный use
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFeedbackNotification extends Notification
{
    use Queueable;

    public function __construct(public Feedback $feedback)
    {
        // Всё ок, теперь это модель Feedback
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'feedback_id' => $this->feedback->id,
            'title'       => $this->feedback->title,
            'message'       => $this->feedback->message,
            'screenshot'       => $this->feedback->screenshot,
            'user_name'   => $this->feedback->user->full_name,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Новое сообщение обратной связи')
            ->greeting('Здравствуйте!')
            ->line('Поступило новое сообщение от пользователя.')
            ->line('От: ' . $this->feedback->user->full_name)
            ->line('Заголовок: ' . $this->feedback->title)
            ->line('Сообщение:')
            ->line($this->feedback->message)
            ->action('Открыть в системе', route('admin.notifications.show', $this->feedback->id))
            ->salutation('— ' . config('app.name'));
    }

    public function toArray($notifiable)
    {
        return [
            'feedback_id' => $this->feedback->id,
        ];
    }
}
