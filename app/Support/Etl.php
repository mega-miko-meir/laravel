<?php

namespace App\Support;

/**
 * Данные из Nobel CRM (qs_calls, qs_double_calls и т.п.) обновляются РОВНО РАЗ
 * В СУТКИ ночным ETL (см. routes/console.php, 02:00) — то есть в течение дня
 * они статичны, и Cache::remember() на аналитических страницах можно смело
 * держать не 10 минут, а до следующего запуска ETL: любая уже виденная сегодня
 * комбинация фильтров/месяца отдаётся из кэша мгновенно. Общий источник этой
 * логики для всех таких страниц (Визиты, Двойные визиты, Рейтинг МП, ...).
 */
class Etl
{
    public static function secondsUntilNextRun(): int
    {
        $next = now()->hour < 2 ? today()->setTime(2, 0) : today()->addDay()->setTime(2, 0);
        return max(60, now()->diffInSeconds($next));
    }
}
