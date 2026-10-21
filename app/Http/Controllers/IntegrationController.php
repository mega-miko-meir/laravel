<?php

namespace App\Http\Controllers;

use App\Services\IntegrationStatusStore;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Страница «Интеграции» (/admin/integrations) — ручной запуск синхронизации
 * данных из внешних систем (см. config/integrations.php), на случай если
 * автозапуск по расписанию (cron, 02:00) не срабатывает из-за сетевых
 * ограничений сервера. Каждая интеграция запускается в фоне (не блокирует
 * HTTP-запрос — полный прогон может занимать несколько минут), прогресс и
 * результат карточка узнаёт через polling status().
 */
class IntegrationController extends Controller
{
    private const STALE_AFTER_MINUTES = 20;

    public function index()
    {
        $integrations = collect(config('integrations'))
            ->map(function (array $config, string $slug) {
                return array_merge($config, [
                    'slug'   => $slug,
                    'status' => IntegrationStatusStore::read($slug),
                ]);
            })
            ->values();

        return view('admin.integrations', compact('integrations'));
    }

    public function trigger(Request $request, string $slug)
    {
        $config = config("integrations.{$slug}");
        if (!$config) {
            abort(404);
        }

        $status = IntegrationStatusStore::read($slug);
        if (($status['status'] ?? null) === 'running' && !$this->isStale($status)) {
            return response()->json($status, 409);
        }

        $args = [];

        if ($config['supports_month'] ?? false) {
            $month = $request->input('month');
            if ($month && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
                return response()->json(['status' => 'idle', 'message' => 'Неверный формат месяца'], 422);
            }
            if ($month) {
                $args[] = '--month=' . $month;
            }
        }

        if ($config['supports_reports'] ?? false) {
            $reports = array_values(array_unique(array_intersect(
                array_map('intval', (array) $request->input('reports', [])),
                array_keys($config['reports'] ?? [])
            )));
            if (empty($reports)) {
                return response()->json(['status' => 'idle', 'message' => 'Выберите хотя бы один отчёт'], 422);
            }
            $args = array_merge($args, array_map('strval', $reports));
        }

        IntegrationStatusStore::write($slug, [
            'status'      => 'running',
            'started_at'  => now()->toDateTimeString(),
            'finished_at' => null,
            'message'     => null,
        ]);

        $artisan = escapeshellarg(base_path('artisan'));
        $php     = escapeshellarg(PHP_BINARY);
        $logFile = escapeshellarg(storage_path("logs/integration_{$slug}.log"));
        $argsStr = $args ? ' ' . implode(' ', array_map('escapeshellarg', $args)) : '';
        $command = $config['command'];

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /B \"\" {$php} {$artisan} {$command}{$argsStr} > {$logFile} 2>&1", 'r'));
        } else {
            exec("{$php} {$artisan} {$command}{$argsStr} > {$logFile} 2>&1 &");
        }

        return response()->json(['status' => 'running', 'started_at' => now()->toDateTimeString()]);
    }

    public function status(string $slug)
    {
        if (!config("integrations.{$slug}")) {
            abort(404);
        }

        $status = IntegrationStatusStore::read($slug);

        if (($status['status'] ?? null) === 'running' && $this->isStale($status)) {
            $status['status']  = 'failed';
            $status['message'] = 'Процесс не ответил вовремя (возможно, прерван) — попробуйте запустить снова.';
        }

        return response()->json($status);
    }

    private function isStale(array $status): bool
    {
        if (empty($status['started_at'])) {
            return false;
        }

        return Carbon::parse($status['started_at'])->diffInMinutes(now()) > self::STALE_AFTER_MINUTES;
    }
}
