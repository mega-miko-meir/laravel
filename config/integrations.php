<?php

/**
 * Реестр интеграций для страницы «Интеграции» (/admin/integrations) —
 * ручной запуск синхронизации данных из внешних систем, на случай если
 * автозапуск по расписанию не сработал (см. routes/console.php).
 *
 * Чтобы добавить новую интеграцию (например, КМП):
 *   1. Написать Artisan-команду, которая выполняет синхронизацию и пишет
 *      статус через App\Services\IntegrationStatusStore::write($slug, ...)
 *      в начале и в конце (см. App\Console\Commands\RunNobelEtl как образец).
 *   2. Добавить сюда новый ключ (slug) с 'label', 'description', 'command'.
 *   3. Если у интеграции есть выбор месяца/конкретных отчётов — указать
 *      'supports_month'/'supports_reports' => true и список 'reports'.
 *      Если нет — просто не указывать (карточка покажет одну кнопку
 *      «Обновить» без доп. полей).
 * Страница и контроллер (IntegrationController) ничего менять не придётся.
 */
return [

    'nobel_crm' => [
        'label'            => 'Nobel CRM',
        'description'      => 'Визиты, врачи, ЛПУ/аптеки, бренды, CLM-презентации — из Nobel CRM API в nobeldb.',
        'command'          => 'etl:nobel',
        'supports_month'   => true,
        'supports_reports' => true,
        'reports'          => [
            1 => 'ЛПУ / Аптеки',
            2 => 'Врачи',
            3 => 'Визиты',
            4 => 'Бренды',
            5 => 'CLM-презентации',
        ],
    ],

];
