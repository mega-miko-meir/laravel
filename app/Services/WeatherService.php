<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getCurrent(string $city)
    {
        return Cache::remember(
            "weather_{$city}",
            now()->addMinutes(30), // кэш на 30 минут
            function () use ($city) {
                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => $city,
                    'appid' => config('services.weather.key'),
                    'units' => 'metric',
                    'lang' => 'ru',
                ]);

                if ($response->failed()) {
                    return null;
                }

                return [
                    'temp' => round($response['main']['temp']),
                    'icon' => $response['weather'][0]['icon'],
                    'description' => $response['weather'][0]['description'],
                    'city' => $response['name'],
                ];
            }
        );
    }
}
