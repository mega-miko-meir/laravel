<?php

namespace App\Services;

/**
 * Хранилище статуса последнего запуска для каждой интеграции (см.
 * config/integrations.php) — отдельный JSON-файл на интеграцию, читает
 * страница /admin/integrations (polling) и пишет сама Artisan-команда
 * интеграции в начале и в конце выполнения (см. RunNobelEtl как образец).
 *
 * Простой файл, а не БД/кэш — статус должен переживать перезапуск
 * очереди/веб-сервера и не зависеть от того, запущен ли воркер очереди
 * (на сервере его может и не быть, см. EtlController/IntegrationController).
 */
class IntegrationStatusStore
{
    private static function path(string $slug): string
    {
        return storage_path("app/integration_status/{$slug}.json");
    }

    public static function write(string $slug, array $data): void
    {
        $dir = dirname(self::path($slug));
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(self::path($slug), json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public static function read(string $slug): array
    {
        $path = self::path($slug);

        if (!file_exists($path)) {
            return ['status' => 'idle'];
        }

        return json_decode(file_get_contents($path), true) ?: ['status' => 'idle'];
    }
}
