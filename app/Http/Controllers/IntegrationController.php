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

        // Если exec()/popen() отключены в php.ini (disable_functions — частая
        // практика безопасности на хостингах), фоновый процесс молча не
        // стартует вообще, а карточка зависает на "Выполняется..." до
        // срабатывания STALE_AFTER_MINUTES. Проверяем ДО записи статуса
        // "running", чтобы сразу вернуть понятную ошибку, а не ложный прогресс.
        $launcher = PHP_OS_FAMILY === 'Windows' ? 'popen' : 'exec';
        if (!function_exists($launcher)) {
            return response()->json([
                'status'  => 'idle',
                'message' => "Функция {$launcher}() отключена на сервере (disable_functions в php.ini) — фоновый запуск невозможен. Нужно включить её в настройках PHP либо запускать синхронизацию через консоль/cron напрямую.",
            ], 500);
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
            // setsid — обязателен: без него процесс остаётся в той же сессии/
            // группе процессов, что и PHP-FPM воркер, и часто убивается вместе
            // с ним, когда воркер перерабатывается/завершает запрос — именно
            // так карточка зависала на "Выполняется..." на проде (процесс
            // реально не доживал до конца, хотя команда вроде бы стартовала).
            // </dev/null — чтобы фоновый процесс не ждал stdin родителя.
            exec("setsid {$php} {$artisan} {$command}{$argsStr} > {$logFile} 2>&1 < /dev/null &");
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

    /**
     * Снять "зависший" статус вручную — например, если фоновый процесс
     * реально не стартовал на сервере (отключён exec, убит вместе с
     * PHP-FPM воркером и т.п.) и ждать STALE_AFTER_MINUTES не хочется, а
     * файл статуса на диске принадлежит веб-серверу (www-data) и недоступен
     * для правки через SFTP-пользователя без sudo. Пишет этот же файл —
     * выполняется от имени веб-сервера, так что прав всегда достаточно,
     * в отличие от ручного rm через консоль хостинга.
     *
     * Важно: это НЕ останавливает реально работающий в фоне процесс, если
     * он ещё жив — только сбрасывает отображаемый статус. Подтверждение
     * (confirm()) на фронте явно предупреждает об этом.
     */
    public function reset(string $slug)
    {
        if (!config("integrations.{$slug}")) {
            abort(404);
        }

        IntegrationStatusStore::write($slug, ['status' => 'idle']);

        return response()->json(['status' => 'idle']);
    }

    private function isStale(array $status): bool
    {
        if (empty($status['started_at'])) {
            return false;
        }

        return Carbon::parse($status['started_at'])->diffInMinutes(now()) > self::STALE_AFTER_MINUTES;
    }
}
