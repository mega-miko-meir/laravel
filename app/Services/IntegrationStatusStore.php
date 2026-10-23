<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Хранилище статуса последнего запуска для каждой интеграции (см.
 * config/integrations.php) — читает страница /admin/integrations (polling),
 * пишет сама Artisan-команда интеграции в начале и в конце выполнения (см.
 * RunNobelEtl как образец).
 *
 * Раньше это был локальный JSON-файл на интеграцию — но тогда ручной прогон
 * ETL с другого компьютера (например, когда с продакшен-сервера заблокирован
 * исходящий порт до внешнего API, а с ноутбука разработчика — нет) не
 * отражался на карточке на сайте: разные машины, разные файлы на диске.
 * Теперь статус пишется в общую таблицу в nobeldb (та же БД, что видят и
 * сервер, и любой компьютер разработчика — она уже используется как общая
 * точка для ETL) через коннекшен 'nobel_write' (см. config/database.php —
 * обычный 'nobel'read-only, тут нужна запись).
 */
class IntegrationStatusStore
{
    private const TABLE = 'etl_integration_status';

    public static function write(string $slug, array $data): void
    {
        try {
            self::ensureTable();
            DB::connection('nobel_write')->statement(
                'REPLACE INTO `' . self::TABLE . '` (slug, status, started_at, finished_at, message, updated_at) VALUES (?, ?, ?, ?, ?, NOW())',
                [
                    $slug,
                    $data['status'] ?? 'idle',
                    $data['started_at'] ?? null,
                    $data['finished_at'] ?? null,
                    $data['message'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning("IntegrationStatusStore::write('{$slug}') failed: " . $e->getMessage());
        }
    }

    public static function read(string $slug): array
    {
        try {
            self::ensureTable();
            $row = DB::connection('nobel_write')
                ->table(self::TABLE)
                ->where('slug', $slug)
                ->first();

            if (!$row) {
                return ['status' => 'idle'];
            }

            return [
                'status'      => $row->status,
                'started_at'  => $row->started_at,
                'finished_at' => $row->finished_at,
                'message'     => $row->message,
            ];
        } catch (\Throwable $e) {
            Log::warning("IntegrationStatusStore::read('{$slug}') failed: " . $e->getMessage());
            return ['status' => 'idle'];
        }
    }

    private static function ensureTable(): void
    {
        DB::connection('nobel_write')->statement('
            CREATE TABLE IF NOT EXISTS `' . self::TABLE . '` (
                `slug` VARCHAR(64) NOT NULL PRIMARY KEY,
                `status` VARCHAR(32) NOT NULL,
                `started_at` DATETIME NULL,
                `finished_at` DATETIME NULL,
                `message` TEXT NULL,
                `updated_at` DATETIME NOT NULL
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ');
    }
}
