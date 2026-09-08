<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemStatusController extends Controller
{
    /**
     * Лёгкая проверка доступности Nobel CRM для баннера в шапке.
     * Кэшируется на 30 секунд, чтобы не долбить Nobel DB проверками
     * при каждом открытии любой страницы каждым пользователем.
     */
    public function nobelStatus()
    {
        $available = Cache::remember('nobel_db_health', 30, function () {
            try {
                DB::connection('nobel')->select('SELECT 1');
                return true;
            } catch (\Exception $e) {
                return false;
            }
        });

        return response()->json(['available' => $available]);
    }
}
