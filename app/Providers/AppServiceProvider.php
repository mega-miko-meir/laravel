<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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


    }

}
