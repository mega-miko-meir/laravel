<?php

namespace App\Http\Controllers;

use App\Notifications\EmployeeDeletedNotification;
use App\Notifications\NewFeedbackNotification;
use auth;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get()
            ->map(fn($n) => $this->withDisplay($n));

        return view('notifications', compact('notifications'));
    }

    public function show(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        return view('show-notification', [
            'notification' => $this->withDisplay($notification),
        ]);
    }

    /**
     * Разные типы уведомлений (все пишутся в одну и ту же таблицу notifications)
     * хранят в data разную форму — NewFeedbackNotification даёт title/user_name/
     * message/screenshot, EmployeeDeletedNotification — employee_full_name/
     * deleted_by. Вьюхи раньше жёстко читали data['title']/data['user_name'],
     * что падало с "Undefined array key" на уведомлении об удалении сотрудника.
     * Приводим обе формы к одному набору полей здесь, а не в шаблонах.
     */
    private function withDisplay(DatabaseNotification $notification): DatabaseNotification
    {
        $notification->display = match ($notification->type) {
            NewFeedbackNotification::class => [
                'title'      => $notification->data['title'] ?? 'Обратная связь',
                'subtitle'   => 'От: ' . ($notification->data['user_name'] ?? '—'),
                'message'    => $notification->data['message'] ?? null,
                'screenshot' => $notification->data['screenshot'] ?? null,
            ],
            EmployeeDeletedNotification::class => [
                'title'      => 'Сотрудник удалён: ' . ($notification->data['employee_full_name'] ?? '—'),
                'subtitle'   => 'Выполнил: ' . ($notification->data['deleted_by'] ?? '—'),
                'message'    => null,
                'screenshot' => null,
            ],
            default => [
                'title'      => 'Уведомление',
                'subtitle'   => null,
                'message'    => null,
                'screenshot' => null,
            ],
        };

        return $notification;
    }
}
