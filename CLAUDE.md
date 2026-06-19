# Nobel System — CLAUDE.md

## Проект
Laravel 11 HR/CRM система для Nobel. Деплой на Linux-сервер: `/var/www/laravel`.
Локальная разработка: `c:\xampp\htdocs\laravel-project` (XAMPP, Windows).

## Стек
- **Backend**: Laravel 11, PHP 8.2, MySQL (основная БД)
- **Frontend**: Blade, Alpine.js, Tailwind CSS (через Vite), Chart.js 4.4.0 (CDN)
- **Внешняя БД**: Nobel CRM MySQL на `192.168.33.39` — connection `nobel` в Laravel
- **ETL**: Python 3 скрипт (`scripts/nobel_etl.py`) — запускается через Artisan или cron

## Архитектура БД

### Основная БД (Laravel default)
- `employees` — сотрудники (rep/rm/ffm), есть поле `crm_employee_id` (FK → nobeldb.qs_calls.employee_id)
- `territories`, `employee_territory` — территории и назначения
- `tablets`, `employee_tablet` — планшеты
- `employee_events` — история событий (hired/dismissed/maternity_leave/...)

### Nobel CRM БД (`nobeldb` @ 192.168.33.39, connection `nobel`)
Views (не таблицы):
- `qs_calls` — визиты. Ключевые поля: `employee_id`, `employee`, `employee_department`, `employee_position`, `manager`, `organization`, `organization_type`, `customer_spesiality`, `appointment_Date`, `appointment_status`, `appointment_type`, `appointment_duration`, `province`, `town`
- `qs_onekey_doctors` — врачи OneKey: `customer`, `customer_spesiality`, `organization`, `province`, `town`, `customer_id`
- `qs_onekey_pharmacy` — аптеки OneKey: `organization`, `organization_address`, `province`, `town`, `organization_id`

## Модели Nobel CRM
- `app/Models/Nobel/Call.php` — connection `nobel`, table `qs_calls`, no timestamps
- `app/Models/Nobel/OnekeyDoctor.php` — connection `nobel`, table `qs_onekey_doctors`
- `app/Models/Nobel/OnekeyPharmacy.php` — connection `nobel`, table `qs_onekey_pharmacy`

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
- `/clients` — База OneKey (врачи + аптеки)
- `/admin/crm-mapping` — привязка CRM-сотрудников к сотрудникам системы

## Контроллеры
- `CallController` — дашборд визитов. `filtered(Request)` — базовый запрос с постоянными фильтрами (тип + статус). Поддерживает фильтры: `date_from/to`, `province`, `town`, `employee` (LIKE), `crm_employee_id` (exact), `organization_type`, `customer_spesiality`, `employee_department`.
- `CrmMappingController` — страница привязки CRM-сотрудников. CRM-список первичен (182 сотрудника), к каждому выбирается сотрудник системы.
- `EmployeeController::showEmployee()` — карточка сотрудника. Если у сотрудника есть `crm_employee_id`, загружает `$visitStats` из nobeldb.

## Компоненты
- `resources/views/components/visit-stats.blade.php` — блок визитов в карточке сотрудника. Props: `$stats` (массив с ключами: `total`, `avgDur`, `lastDate`, `thisMonth`, `lastMonth`, `monthly`, `topSpec`, `crmId`, `doctorVisits`, `pharmacyVisits`). Ссылка "Подробнее" ведёт на `/calls?crm_employee_id={crmId}`.

## ETL
- `scripts/nobel_etl.py` — загружает данные из Nobel CRM API в nobeldb
- `app/Console/Commands/RunNobelEtl.php` — Artisan-обёртка: `php artisan etl:nobel`
- `routes/console.php` — расписание: каждый день в 02:00
- Cron на сервере: `* * * * * cd /var/www/laravel && php artisan schedule:run`

## Привязка сотрудников CRM
- Поле `employees.crm_employee_id` → `qs_calls.employee_id`
- Авто-матчинг: `php artisan crm:match-employees` (по первым двум словам имени)
- Ручная привязка: `/admin/crm-mapping` (список 182 CRM-сотрудников с дропдауном)

## UI-стиль
- Sidebar: синий `#1e3a8a`, компонент `x-side-menu`
- Карточки: белый фон, `border-radius:12px`, `border:1px solid #f0f0f0`
- Акценты: синий `#1d4ed8` / `#2563eb`, зелёный `#16a34a`, фиолетовый `#6366f1`, голубой `#0ea5e9`
- Дашборд визитов: CSS custom properties (`--bg`, `--card`, `--border`, `--text1/2/3`), поддержка dark mode через `.dash-dark`
- Без эмодзи, без лишних комментариев в коде

## Деплой
- Сервер: Linux, `/var/www/laravel`
- Трансфер файлов: WinSCP
- Python на сервере: `python3`, установка пакетов через `pip3 install --user`
- WinSCP Terminal не поддерживает интерактивные команды (sudo с паролем, crontab -e)
- Для crontab: `(crontab -l 2>/dev/null; echo "...") | crontab -`
