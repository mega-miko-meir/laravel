# Nobel System — CLAUDE.md

## Проект
Laravel 11 HR/CRM система для Nobel. Деплой на Linux-сервер: `/var/www/laravel`.
Локальная разработка: `c:\xampp\htdocs\laravel-project` (XAMPP, Windows).

## Стек
- **Backend**: Laravel 11, PHP 8.2, MySQL (основная БД)
- **Frontend**: Blade, Alpine.js, Tailwind CSS (через Vite), Chart.js 4.4.0 (CDN)
- **Внешняя БД**: Nobel CRM MySQL на `192.168.33.39` — connection `nobel` в Laravel
- **ETL**: Python 3 скрипт (`scripts/nobel_etl.py`) — запускается через Artisan или cron
- **Excel**: `phpoffice/phpspreadsheet` 3.6.0 установлен (также есть устаревший `maatwebsite/excel` 1.1.x и `phpoffice/phpexcel` — не использовать)

## Архитектура БД

### Основная БД (Laravel default)
- `employees` — сотрудники (rep/rm/ffm), есть поле `crm_employee_id` (FK → nobeldb.qs_calls.employee_id)
- `territories`, `employee_territory` — территории и назначения
- `tablets`, `employee_tablet` — планшеты
- `employee_events` — история событий (hired/dismissed/maternity_leave/...)

### Nobel CRM БД (`nobeldb` @ 192.168.33.39, connection `nobel`)
Всё это MySQL VIEWs (не таблицы) — нет `id`, нет `created_at/updated_at`:
- `qs_calls` — визиты. Ключевые поля: `employee_id`, `employee`, `employee_department`, `employee_position`, `manager`, `organization`, `organization_type`, `customer_spesiality`, `appointment_Date`, `appointment_status`, `appointment_type`, `appointment_duration`, `province`, `town`
- `qs_onekey_doctors` — врачи OneKey: `customer`, `customer_spesiality`, `organization`, `organization_address`, `province`, `town`, `customer_id`
- `qs_onekey_pharmacy` — аптеки OneKey: `organization`, `organization_address`, `province`, `town`, `organization_id`

## Модели Nobel CRM
- `app/Models/Nobel/Call.php` — connection `nobel`, table `qs_calls`, no timestamps
- `app/Models/Nobel/OnekeyDoctor.php` — connection `nobel`, table `qs_onekey_doctors`, `$primaryKey = 'customer_id'`, `$incrementing = false`, no timestamps
- `app/Models/Nobel/OnekeyPharmacy.php` — connection `nobel`, table `qs_onekey_pharmacy`, `$primaryKey = 'organization_id'`, `$incrementing = false`, no timestamps

**Важно про VIEWs**: у них нет колонки `id` — обязательно указывать `$primaryKey` иначе `chunk()` упадёт с `ORDER BY id`. `$incrementing = false` тоже обязателен.

## Ключевые правила для данных визитов (qs_calls)
Везде при запросах к визитам применять **два обязательных фильтра**:
```php
->whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку'])
->where('appointment_status', 'Выполнено')
```
Эти фильтры встроены в `CallController::filtered()` — не добавлять их вручную сверху.

## Маршруты
- `/` — список сотрудников
- `/dashboard` — дашборд KPI
- `/calls` — дашборд визитов CRM (CallController)
- `/clients` — База OneKey (врачи + аптеки), экспорт в Excel
- `/admin/crm-mapping` — привязка CRM-сотрудников к сотрудникам системы

## Контроллеры
- `CallController` — дашборд визитов. `filtered(Request)` — базовый запрос с постоянными фильтрами (тип + статус). Поддерживает фильтры: `date_from/to`, `province`, `town`, `employee` (LIKE), `crm_employee_id` (exact), `organization_type`, `customer_spesiality`, `employee_department`. KPI-метрики — один `selectRaw` запрос. Опции фильтров кэшируются на 1 час через `Cache::remember`. Весь `index()` обёрнут в try-catch.
- `CrmMappingController` — страница привязки CRM-сотрудников. CRM-список первичен. `getCrmEmployees()` кэшируется на 1 час. Весь Nobel DB код в try-catch.
- `EmployeeController::showEmployee()` — карточка сотрудника. Если у сотрудника есть `crm_employee_id`, загружает `$visitStats` из nobeldb. Nobel DB блок обёрнут в try-catch.
- `ClientController` — База OneKey. `export()` выгружает Excel через PhpSpreadsheet. Использует `chunk(500)` для обхода больших VIEW.

## Компоненты
- `resources/views/components/visit-stats.blade.php` — блок визитов в карточке сотрудника. Props: `$stats` (массив с ключами: `total`, `avgDur`, `lastDate`, `thisMonth`, `lastMonth`, `monthly`, `topSpec`, `crmId`, `doctorVisits`, `pharmacyVisits`). Ссылка "Подробнее" ведёт на `/calls?crm_employee_id={crmId}`.

## ETL
- `scripts/nobel_etl.py` — загружает данные из Nobel CRM API в nobeldb
- `app/Console/Commands/RunNobelEtl.php` — Artisan-обёртка: `php artisan etl:nobel`
- `routes/console.php` — расписание: каждый день в 02:00
- Cron на сервере: `* * * * * cd /var/www/laravel && php artisan schedule:run`

## Привязка сотрудников CRM
- Поле `employees.crm_employee_id` → `qs_calls.employee_id`
- Авто-матчинг: `php artisan crm:match-employees` (по первым двум словам имени). Опция `--force` перепривязывает уже привязанных.
- Ручная привязка: `/admin/crm-mapping` — список CRM-сотрудников с поиском по имени (Alpine.js combobox с `position:fixed` dropdown). Вкладки: Все / Не привязано (по умолчанию) / Привязано.
- На сервере для долгих команд: `nohup php artisan crm:match-employees >> /tmp/crm_match.log 2>&1 &`

## UI-стиль
- Sidebar: синий `#1e3a8a`, компонент `x-side-menu`
- Карточки: белый фон, `border-radius:12px`, `border:1px solid #f0f0f0`
- Акценты: синий `#1d4ed8` / `#2563eb`, зелёный `#16a34a`, фиолетовый `#6366f1`, голубой `#0ea5e9`
- Дашборд визитов: CSS custom properties (`--bg`, `--card`, `--border`, `--text1/2/3`), поддержка dark mode через `.dash-dark`
- Без эмодзи, без лишних комментариев в коде
- Alpine.js: НЕ использовать `:style` для tab-кнопок — Alpine заменяет весь `style=""` при инициализации. Использовать CSS классы + `:class`.
- Выпадающие списки внутри таблиц с `overflow:hidden` — использовать `position:fixed` + `getBoundingClientRect()` чтобы dropdown не обрезался.

## Resilience / Nobel DB недоступна
Nobel DB (`192.168.33.39`) недоступна с локального Windows-окружения разработчика. Все запросы к Nobel DB обёрнуты в try-catch в: `CallController`, `CrmMappingController`, `EmployeeController`. При недоступности DB страницы деградируют gracefully (показываются без блока визитов).

## Кэширование
- `Cache::remember('calls_filter_provinces', 3600, ...)` — опции фильтров визитов
- `Cache::remember('calls_filter_towns', 3600, ...)`
- `Cache::remember('calls_filter_specialties', 3600, ...)`
- `Cache::remember('calls_filter_departments', 3600, ...)`
- `Cache::remember('crm_employees_list', 3600, ...)` — список CRM-сотрудников для страницы привязки

## Деплой
- Сервер: Linux, `/var/www/laravel`, IP `192.168.33.39` (сервер сам и есть БД Nobel CRM)
- Трансфер файлов: WinSCP
- На сервере в `.env`: `NOVEL_DB_HOST=127.0.0.1` (не `192.168.33.39`)
- Python на сервере: `python3`, установка пакетов через `pip3 install --user`
- WinSCP Terminal не поддерживает интерактивные команды (sudo с паролем, crontab -e)
- Для crontab: `(crontab -l 2>/dev/null; echo "...") | crontab -`

## Локальная разработка (XAMPP)
- Apache VirtualHost: `c:\xampp\apache\conf\extra\httpd-vhosts.conf` → `localhost` → `laravel-project/public`
- `.env`: `NOBEL_DB_PASSWORD="!!Shadow2023"` — кавычки обязательны, `!!` ломает dotenv без них
- После изменений `.env`: `php artisan config:clear`
