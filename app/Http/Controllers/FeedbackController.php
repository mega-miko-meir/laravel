<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedbackStoreRequest;
use App\Models\User;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewFeedbackNotification;

class FeedbackController extends Controller
{
    public function store(FeedbackStoreRequest $request)
    {
        $validated = $request->validated();

        $feedback = Feedback::create([
            'user_id' => auth()->id(),
            'title'   => $validated['title'],
            'message' => $validated['message'],
            'screenshot' => $validated['screenshot'] ?? null,
        ]);

        // все админы
        $admins = User::whereHas('role', function ($q) {
                $q->where('name', 'admin');
            })->get();

        // foreach ($admins as $admin) {
        //     info("Отправляем уведомление админу: {$admin->full_name}, email: {$admin->email}");
        // }

        Notification::send($admins, new NewFeedbackNotification($feedback));


        return back()->with('success', 'Сообщение отправлено');

    }

}
