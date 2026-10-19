<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * birth_date/hiring_date были TIMESTAMP — MySQL TIMESTAMP хранит только
 * 1970-01-01..2038-01-19 (UTC), поэтому дата рождения раньше 1970 года
 * (т.е. любой сотрудник 56+ лет на 2026 год) не сохранялась вообще:
 * "Incorrect datetime value: '1969-09-15'". Заодно TIMESTAMP конвертируется
 * через часовой пояс сессии — этим объяснялся и более ранний артефакт
 * "дата минус 5 часов" у hiring_date. DATE не хранит время и не знает
 * часового пояса — обе проблемы уходят.
 *
 * doctrine/dbal не установлен (нужен для Blueprint::change()), поэтому
 * меняем тип колонки сырым SQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Битые "нулевые" даты (уже встречались в данных) MySQL не даёт
        // сконвертировать в DATE — это не настоящие значения, обнуляем.
        // Сравнение напрямую со строкой-нулём само падает под strict mode
        // (MySQL валидирует литерал как дату ещё на этапе сравнения) —
        // сравниваем через CAST в CHAR, чтобы просто увидеть байты как есть.
        DB::statement("UPDATE employees SET birth_date = NULL WHERE CAST(birth_date AS CHAR) = '0000-00-00 00:00:00'");
        DB::statement("UPDATE employees SET hiring_date = NULL WHERE CAST(hiring_date AS CHAR) = '0000-00-00 00:00:00'");

        DB::statement('ALTER TABLE employees MODIFY birth_date DATE NULL');
        DB::statement('ALTER TABLE employees MODIFY hiring_date DATE NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE employees MODIFY birth_date TIMESTAMP NULL');
        DB::statement('ALTER TABLE employees MODIFY hiring_date TIMESTAMP NULL');
    }
};
