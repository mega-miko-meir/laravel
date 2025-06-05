<?php

namespace App\Http\Controllers;

use OpenAI\Client;
use League\Uri\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $userMessage = $request->input('message');

        if (!$userMessage) {
            return response()->json(['reply' => 'Пожалуйста, введите сообщение.']);
        }

        // Запрос к OpenAI API
        $botReply = $this->getBotReply($userMessage);

        // Логируем ответ от OpenAI
        Log::info('Ответ от OpenAI:', ['reply' => $botReply]);

        return response()->json(['reply' => $botReply]);
    }

    private function getBotReply($message)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        // Логируем весь ответ
        Log::info('Ответ от OpenAI:', $response->json());

        if ($response->failed()) {
            Log::error('Ошибка при запросе к OpenAI:', $response->json());
            return 'Ошибка при запросе к OpenAI. Попробуйте позже.';
        }

        return $response->json('choices.0.message.content');
    }

}
