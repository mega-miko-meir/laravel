<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private const SESSION_KEY = 'chatbot_history';
    private const MAX_HISTORY  = 20;

    private string $systemPrompt = 'Ты AI-ассистент компании Nobel (фармацевтическая компания Казахстана). '
        . 'Помогай сотрудникам с рабочими и общими вопросами. '
        . 'Будь профессиональным, дружелюбным и лаконичным. '
        . 'Отвечай на том языке, на котором написан вопрос (русский или казахский по умолчанию).';

    public function index(Request $request)
    {
        $history = $request->session()->get(self::SESSION_KEY, []);
        return view('chatbot', compact('history'));
    }

    public function handle(Request $request)
    {
        $userMessage = trim($request->input('message', ''));

        if ($userMessage === '') {
            return response()->json(['error' => 'Сообщение не может быть пустым.'], 422);
        }

        $history   = $request->session()->get(self::SESSION_KEY, []);
        $history[] = ['role' => 'user', 'content' => $userMessage];

        $botReply  = $this->callClaude($history);
        $history[] = ['role' => 'assistant', 'content' => $botReply];

        if (count($history) > self::MAX_HISTORY) {
            $history = array_slice($history, -self::MAX_HISTORY);
        }

        $request->session()->put(self::SESSION_KEY, $history);

        return response()->json(['reply' => $botReply]);
    }

    public function clearHistory(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);
        return response()->json(['status' => 'ok']);
    }

    private function callClaude(array $history): string
    {
        $apiKey = config('services.anthropic.key');
        $model  = config('services.anthropic.model');

        $messages = array_map(fn($msg) => [
            'role'    => $msg['role'],
            'content' => $msg['content'],
        ], $history);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => $model,
                    'max_tokens' => 1024,
                    'system'     => $this->systemPrompt,
                    'messages'   => $messages,
                ]);

            if ($response->failed()) {
                Log::error('Claude API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return 'Не удалось получить ответ. Попробуйте позже.';
            }

            return $response->json('content.0.text') ?? 'Нет ответа.';

        } catch (\Exception $e) {
            Log::error('Claude exception', ['message' => $e->getMessage()]);
            return 'Ошибка соединения с AI. Попробуйте позже.';
        }
    }
}
