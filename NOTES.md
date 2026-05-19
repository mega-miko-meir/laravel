# Nobel System — Заметки по проекту

## Что это

Внутренняя HR/CRM-система компании **Nobel** (nobel.kz).  
Управление сотрудниками, планшетами, территориями и базой клиентов OneKey.

---

## Стек

| Слой | Технология |
|---|---|
| Backend | Laravel (PHP), MySQL |
| Frontend | Blade + Alpine.js + Tailwind CSS |
| Экспорт | PhpSpreadsheet (Excel), fputcsv (CSV-стриминг) |
| Чатбот | OpenAI API (`OPENAI_API_KEY` в `.env`) |
| Погода | Weather API (`WEATHER_API_KEY` в `.env`) |

> Локальный запуск через **XAMPP** (`http://localhost/laravel-project/public`)  
> База данных: MySQL, база `laravel-project`

---

## Основные модули

### Сотрудники (`/`)
- Список с поиском, фильтром по активности, сортировкой
- Карточка сотрудника: данные, история территорий, планшеты, события, реквизиты
- Выгрузка в Excel (`POST /export-excel`)
- Ключевые файлы: `home.blade.php`, `employee.blade.php`, `EmployeeController.php`

### Планшеты (`/tablets`)
- Список всех планшетов с поиском, статистика по статусам
- Статусы: `active`, `free`, `new`, `damaged`, `lost`, `admin`
- Привязка/отвязка планшета к сотруднику, PDF-акты
- Выгрузка в Excel (`POST /export/tablets`)
- Ключевые файлы: `tablets.blade.php`, `TabletController.php`

### Территории (`/territories`)
- Территории привязаны к сотрудникам через `employee_territory`
- Брики (`Brick`) — подразделения территорий
- Ключевые файлы: `TerritoryController.php`, `BrickController.php`

### База OneKey (`/clients`)
- Справочник фармацевтических клиентов (специалисты + аптеки)
- Фильтры: тип, регион, город, специальность, ФИО
- Выгрузка в CSV (`POST /clients/export`) — потоковая, без ограничений по объёму
- Ключевые файлы: `clients.blade.php`, `ClientController.php`

### Команда (`/my-team`)
- Иерархия FFM → RM → Rep по департаментам
- Два режима отображения: по группам / по FFM и RM

### Дашборд (`/dashboard`)
- Статистика активности сотрудников

### Администрирование
- Пользователи (`/users`) — только `admin`
- Роли и права (`/roles`, `/permissions`)
- Лог активности (`/activity`) — все HTTP-запросы
- Уведомления (`/admin/notifications`)

---

## Роли и права

```
admin   — полный доступ, управление пользователями
editor  — создание/редактирование сотрудников, планшетов, территорий
(нет)   — только просмотр
```

Middleware: `can:admin`, `can:editor` в роутах.

---

## Ключевые таблицы БД

| Таблица | Описание |
|---|---|
| `employees` | Сотрудники (ФИО, статус, email, даты) |
| `tablets` | Планшеты (серийный номер, статус, модель) |
| `tablet_assignments` | История привязки планшетов к сотрудникам |
| `territories` | Территории |
| `employee_territory` | Pivot: сотрудник ↔ территория |
| `bricks` | Брики (подтерриториальные единицы) |
| `clients` | База OneKey (врачи, аптеки) |
| `users` | Пользователи системы |
| `roles` / `permissions` | RBAC |
| `activity_logs` | Лог всех запросов |
| `employee_events` | События сотрудников (приём, увольнение и т.д.) |
| `employee_credentials` | Реквизиты сотрудников |
| `tasks` | Задачи (CRUD, привязаны к auth-пользователю) |

---

## Структура роутов

```
web.php          — auth, dashboard, chatbot, export, clients
routes/employees.php   — сотрудники
routes/tablets.php     — планшеты
routes/territories.php — территории и брики
routes/admin.php       — пользователи, роли, логи
```

---

## Важные детали

- **Экспорт OneKey** — был переведён с PhpSpreadsheet на CSV-стриминг (`chunk(500)`) из-за `memory exhausted` на больших таблицах. Файл `.csv` с UTF-8 BOM, разделитель `;`.
- **Alpine.js** используется повсеместно для интерактивности без Vue/React.
- **Inline styles** (не Tailwind) — стиль страниц `home.blade.php` и `tablets.blade.php` используется как эталон для новых страниц.
- **Chatbot** — `ChatbotController.php` использует OpenAI API через Laravel HTTP-фасад (`Illuminate\Support\Facades\Http`).
- **Фото сотрудников** — хранятся в `storage/`, добавлены в последней миграции (`2026_05_12`).
