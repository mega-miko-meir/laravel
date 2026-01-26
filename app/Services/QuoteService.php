<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class QuoteService
{
    // Время кэширования в минутах (тест: 2 минуты)
    protected int $cacheMinutes = 2;

    public function getDailyQuote(bool $forceRefresh = false): array
    {
        $cacheKey = 'daily_quote';

        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Пример API: https://api.quotable.io/random
            $response = Http::get('https://api.quotable.io/random');
            if (!$response->ok()) {
                throw new \Exception('API error');
            }

            $quoteData = [
                'text' => $response->json('content'),
                'author' => $response->json('author'),
            ];

            // Сохраняем в кэш
            Cache::put($cacheKey, $quoteData, now()->addMinutes($this->cacheMinutes));

            return $quoteData;
        } catch (\Exception $e) {
            // На случай ошибки возвращаем дефолт
            return [
                'text' => "Couldn't fetch the quote :(",
                'author' => ''
            ];
        }
    }
}
