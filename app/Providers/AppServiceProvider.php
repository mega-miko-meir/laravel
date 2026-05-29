<?php

namespace App\Providers;

use App\Services\QuoteService;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QuoteService::class, fn() => new QuoteService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Удаляем устаревший hot-файл если Vite dev-сервер не отвечает
        $hotFile = public_path('hot');
        if (file_exists($hotFile)) {
            $url = rtrim(file_get_contents($hotFile));
            $ctx = stream_context_create(['http' => ['timeout' => 1], 'https' => ['timeout' => 1]]);
            $alive = @file_get_contents($url . '/@vite/client', false, $ctx) !== false;
            if (!$alive) {
                @unlink($hotFile);
            }
        }

        // 1. Админ может всё (Super Admin bypass)
        // Эта проверка сработает перед всеми остальными
        Gate::before(function (User $user) {
            if ($user->role->name === 'admin') {
                return true;
            }
        });

        // 2. Определяем конкретные действия
        Gate::define('admin', function (User $user) {
            return in_array($user->role->name, ['admin']);
        });

        Gate::define('editor', function (User $user) {
            return in_array($user->role->name, ['admin', 'editor']);
        });

        Gate::define('viewer', function (User $user) {
            return in_array($user->role->name, ['admin', 'editor', 'viewer']);
            });


        // View::composer('partials.__header', function ($view) {
        //     $weather = app(WeatherService::class)
        //         ->getCurrent(config('app.weather_city', 'Karaganda'));

        //     $view->with('weather', $weather);
        // });

        // View::composer('partials.__header', function ($view) {
        //     $cities = ['Almaty'];
        //     $weatherData = [];

        //     foreach ($cities as $city) {
        //         $weatherData[$city] = app(WeatherService::class)->getCurrent($city);
        //     }

        //     $view->with('weatherData', $weatherData);
        // });


        // View::composer('partials.__header', function ($view) {
        //     $quote = app(QuoteService::class)->getDailyQuote();
        //     $view->with('dailyQuote', $quote);
        // });


    }

}
