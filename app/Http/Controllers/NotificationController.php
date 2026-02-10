<?php

namespace App\Http\Controllers;

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
            ->get();

        return view('notifications', compact('notifications'));
    }

    public function show(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        return view('show-notification', [
            'notification' => $notification,
        ]);
    }
}
