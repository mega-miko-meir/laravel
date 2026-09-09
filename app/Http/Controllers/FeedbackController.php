<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedbackStoreRequest;
use App\Models\User;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
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

        try {
            Notification::send($admins, new NewFeedbackNotification($feedback));
        } catch (\Exception $e) {
            // Обратная связь уже сохранена в БД — сбой отправки письма не должен ломать запрос пользователя
            Log::error('Feedback notification send failed', ['feedback_id' => $feedback->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Сообщение отправлено');

    }

}
