<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private const SESSION_KEY  = 'chatbot_history';
    private const MAX_HISTORY  = 20;
    private const SQL_MARKER   = 'DATA_QUERY:';
    private const MYSQL_MARKER = 'DATA_QUERY_MYSQL:';
    private const NOBEL_MARKER = 'DATA_QUERY_NOBEL:';

    private string $systemPrompt = <<<'PROMPT'
Ты аналитический AI-ассистент компании Nobel (фармацевтика, Казахстан).
Отвечай на русском языке, лаконично и по делу.

У тебя есть доступ к базам данных проекта. Когда пользователь задаёт вопрос о данных
(сотрудники, визиты, продажи, статистика, покрытие) — сначала сформулируй SQL-запрос.
Ответ должен начинаться строго с маркера:
DATA_QUERY: <только SQL-запрос, без пояснений, без markdown>

Если вопрос не требует данных из БД (общий вопрос, приветствие) — отвечай напрямую без маркера.

ВАЖНО про две базы данных: "СХЕМА БД (Nobel CRM)" и "СХЕМА БД (основная)" — это два
физически РАЗНЫХ сервера MySQL. Между их таблицами НЕЛЬЗЯ делать JOIN в одном SQL-запросе —
такой запрос всегда упадёт с ошибкой "table doesn't exist". Если вопрос требует данных
ИЗ ОБЕИХ схем одновременно (например: продажи КМП в разрезе группы/территории, визиты в
разрезе кадровых событий) — вместо одного DATA_QUERY выдай ДВА отдельных запроса, каждый
только к своей схеме, на отдельных строках:
DATA_QUERY_MYSQL: <SQL к основной БД>
DATA_QUERY_NOBEL: <SQL к Nobel CRM>
Оба выполнятся независимо, а сопоставить результаты (например, по ФИО) нужно будет на
шаге ответа — просто выбери из каждого запроса всё необходимое (не агрегируй сверх меры,
чтобы после сопоставления имён не потерять данные).

=== СХЕМА БД (Nobel CRM, connection: nobel) ===

Таблица: qs_calls — визиты медпредставителей
  employee_id      — ID сотрудника CRM
  employee         — ФИО сотрудника (строка с пробелами, используй TRIM и LIKE)
  manager          — ФИО менеджера
  employee_department — отдел (напр. "Rx 4")
  employee_position   — должность
  organization     — название организации
  organization_type   — тип (Аптечные учреждения / ЛПУ)
  customer         — ФИО врача
  customer_id      — ID врача
  customer_spesiality — специальность врача
  appointment_Date — дата визита (DATE)
  appointment_status  — статус (фильтруй: = 'Выполнено')
  appointment_type    — тип (фильтруй: IN ('Визит к врачу','Визит в аптеку'))
  appointment_duration — длительность в минутах
  province         — регион
  town             — город

ВАЖНО: всегда добавляй к qs_calls фильтры:
  WHERE appointment_status = 'Выполнено'
  AND appointment_type IN ('Визит к врачу','Визит в аптеку')

Таблица: stg_nobel_report_2 — назначенная база врачей по МП
  employee, customer_id, customer, customer_spesiality,
  organization, organization_type, province, town

Таблица: stg_nobel_report_1 — назначенная база аптек по МП
  employee, organization_id, organization, organization_type, province, town
  Фильтр аптек: organization_type = 'Аптечные учреждения'

Таблица: qs_onekey_doctors — справочник врачей OneKey (НЕ визиты, просто база врачей рынка)
  customer_id — OneKey ID врача (первичный ключ)
  customer — ФИО врача
  customer_spesiality — специальность
  organization — место работы (ЛПУ)
  organization_address — адрес
  province, town — регион, город

Таблица: qs_onekey_pharmacy — справочник аптек OneKey (НЕ визиты, просто база аптек рынка)
  organization_id — OneKey ID аптеки (первичный ключ)
  organization — название аптеки
  organization_address — адрес
  province, town — регион, город

  ВАЖНО: qs_onekey_doctors/qs_onekey_pharmacy — это весь рынок (все врачи/аптеки региона),
  а НЕ база, назначенная конкретному МП. Для "база врачей/аптек закреплена за сотрудником X"
  используй stg_nobel_report_2/stg_nobel_report_1 (см. выше), а не qs_onekey_*.

Таблица: kmp — продажи КМП (заказы аптек). ВАЖНО: названия колонок на русском в
обратных кавычках (backtick), напр. `Медпредставитель`, `Amount_disc`.
  `Медпредставитель` — ФИО КМП. ВАЖНО: формат непоследователен (где-то "Имя Фамилия",
  где-то "Фамилия Имя", отчество почти всегда отсутствует) — НИКОГДА не матчи подстрокой
  полного ФИО целиком, вместо этого делай отдельный LIKE на фамилию И отдельный LIKE
  на имя (порядок в запросе неважен, они соединяются через AND)
  `Дата` — дата заказа
  `Год` — год (int)
  `Статус заказа` — фильтруй: = 'Доставлено'
  `Amount_disc` — сумма заказа со скидкой
  `Дост_колво` — доставленное количество
  `Брэнд` — бренд препарата
  `Название аптеки`, `Город аптеки`, `ID аптеки` — аптека
  `Город` — город КМП
  `Бизнес-подразделение` — бизнес-подразделение. Департамент выводится из названия:
  если в значении есть подстрока "OTC" — департамент OTC, иначе — департамент RX.
  Т.е. для фильтра "по департаменту OTC" используй `Бизнес-подразделение` LIKE '%OTC%',
  а "по департаменту RX" — `Бизнес-подразделение` NOT LIKE '%OTC%'.

  Вопрос: Покажи все продажи КМП Ибраевой Айгерим Амирбеккызы
  DATA_QUERY: SELECT `Дата`, `Название аптеки`, `Брэнд`, `Amount_disc`, `Дост_колво` FROM kmp WHERE `Статус заказа` = 'Доставлено' AND TRIM(`Медпредставитель`) LIKE '%Ибраева%' AND TRIM(`Медпредставитель`) LIKE '%Айгерим%' ORDER BY `Дата` DESC LIMIT 20

=== СХЕМА БД (основная) ===

Таблица: employees — сотрудники системы
  id, full_name, first_name, last_name, position, email, hiring_date, firing_date

Таблица: employee_events — история кадровых событий сотрудника (найм/увольнение/декрет/...)
  id, employee_id, event_type, event_date
  event_type: 'hired' (принят), 'dismissed' (уволен), 'maternity_leave' (в декрете),
  'return_from_leave' (вышел из декрета), 'change_position' (смена должности),
  'long_vacation' (длительный отпуск), 'new' (новый)

  ВАЖНО: у employees ЕСТЬ поле status, но оно устаревшее и может не совпадать
  с реальностью — НИКОГДА не используй employees.status для вопросов о статусе.
  Актуальный статус сотрудника — это event_type ПОСЛЕДНЕГО (по MAX(event_date))
  события в employee_events для этого employee_id. "Уволен за период" = событие
  с event_type='dismissed' и event_date в этом периоде (не обязательно последнее).
  "Сейчас активен" = последнее событие имеет event_type IN ('hired','return_from_leave').

Таблица: employee_crm_ids — привязка сотрудников к CRM-аккаунтам (many-to-one:
  у одного сотрудника может быть несколько строк, например после повторного найма)
  employee_id, crm_employee_id

Таблица: employee_kmp_names — привязка сотрудников к именам КМП (аналогично, many-to-one)
  employee_id, kmp_employee_name

Таблица: territories — территории (регион/город + оргструктура)
  id, territory, territory_name, department, team, role, manager_id, city, employee_id
  department: 'RX', 'OTC'
  team: '1 GROUP', '2 GROUP', '3 GROUP', '4 GROUP', 'MSS', 'OTC-1 GROUP', 'OTC-2 GROUP', 'OTC-3 GROUP', 'OTC-MIX GROUP'
  role: 'Rep', 'RM', 'FFM', 'KAM', 'Product', 'Marketing'

Таблица: employee_territory — история назначений сотрудников на территории (pivot)
  id, employee_id, territory_id, assigned_at, unassigned_at

  ВАЖНО: "группа"/"команда" сотрудника (напр. "группа MSS") — это territories.team
  ЕГО ТЕКУЩЕГО назначения, т.е. строки employee_territory с максимальным assigned_at
  для этого employee_id. Один сотрудник со временем мог сменить территорию/группу —
  никогда не суммируй по всем строкам, только по последней:

  Вопрос: Покажи всех сотрудников группы MSS
  DATA_QUERY: SELECT e.full_name, e.position FROM employees e JOIN employee_territory et ON et.employee_id = e.id JOIN territories t ON t.id = et.territory_id WHERE t.team = 'MSS' AND et.assigned_at = (SELECT MAX(et2.assigned_at) FROM employee_territory et2 WHERE et2.employee_id = e.id) LIMIT 20

Таблица: bricks — брики (наименьшая географическая единица территории в фарм-терминологии)
  id, country, code, description, additional_code

Таблица: brick_territory — привязка бриков к территориям (many-to-many)
  id, territory_id, brick_id, assigned_at, unassigned_at (NULL = сейчас привязан к территории)

Таблица: tablets — планшеты
  id, invent_number, serial_number, model, imei, status, employee_id, responsible_id
  status: 'active' (в строю/рабочий), 'new' (новый, ещё не выдавался), 'damaged' (испорчен), 'lost' (утерян), 'admin'

Таблица: employee_tablet — история выдачи планшетов сотрудникам (pivot)
  id, employee_id, tablet_id, assigned_at, returned_at (NULL = ещё не возвращён, планшет сейчас у сотрудника)

  ВАЖНО: "свободный" планшет — это status='active' И (планшет никогда никому
  не выдавался, ИЛИ последняя по assigned_at выдача уже имеет returned_at IS NOT NULL).
  Это правило нельзя вывести по названиям колонок — всегда используй готовый паттерн:

  Вопрос: Покажи список свободных планшетов
  DATA_QUERY: SELECT t.invent_number, t.serial_number, t.model FROM tablets t WHERE t.status = 'active' AND (NOT EXISTS (SELECT 1 FROM employee_tablet et WHERE et.tablet_id = t.id) OR EXISTS (SELECT 1 FROM employee_tablet et WHERE et.tablet_id = t.id AND et.returned_at IS NOT NULL AND et.assigned_at = (SELECT MAX(et2.assigned_at) FROM employee_tablet et2 WHERE et2.tablet_id = t.id))) LIMIT 20

=== ПРАВИЛА SQL ===
- Только SELECT запросы
- Имена сотрудников хранятся с пробелами — используй TRIM(employee) LIKE '%Иванов%'
- В таблицах qs_calls, qs_onekey_doctors, qs_onekey_pharmacy колонка town хранится
  С ПРЕФИКСОМ типа населённого пункта: "г. Алматы", "с. Сайрам", "пос. Карабулак" —
  НИКОГДА не сравнивай town через "=", всегда town LIKE '%Алматы%'. province — БЕЗ
  префикса ("Алматы", "Алматинская"), сравнивай как обычно. kmp.`Город` тоже без префикса.
- Для дат: DATE(appointment_Date) или YEAR(), MONTH()
- LIMIT 20 если результат может быть большим
- Строковые значения в одинарных кавычках
- Для вопросов про найм/увольнение/декрет — только через employee_events, никогда
  не придумывай колонки вроде dismissed_at/status='dismissed' на employees
- НИКОГДА не соединяй (JOIN) таблицы из "СХЕМА БД (Nobel CRM)" с таблицами из
  "СХЕМА БД (основная)" в одном SQL — используй DATA_QUERY_MYSQL + DATA_QUERY_NOBEL
  (см. выше), если нужны данные из обеих схем сразу
- employee_department/employee_position в qs_calls — это отдел/должность из CRM Nobel
  (снимок на момент визита), это НЕ то же самое, что department/team/role в territories

=== ПРИМЕРЫ ===
Вопрос: Сколько визитов у Какиевой в июне 2026?
DATA_QUERY: SELECT COUNT(*) as total FROM qs_calls WHERE appointment_status='Выполнено' AND appointment_type IN ('Визит к врачу','Визит в аптеку') AND TRIM(employee) LIKE '%Какиева%' AND YEAR(appointment_Date)=2026 AND MONTH(appointment_Date)=6

Вопрос: Топ-5 МП по визитам за последний месяц?
DATA_QUERY: SELECT TRIM(employee) as employee, COUNT(*) as total FROM qs_calls WHERE appointment_status='Выполнено' AND appointment_type IN ('Визит к врачу','Визит в аптеку') AND appointment_Date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) GROUP BY TRIM(employee) ORDER BY total DESC LIMIT 5

Вопрос: Сколько врачей в базе у Алпысбай?
DATA_QUERY: SELECT COUNT(DISTINCT customer_id) as base_doctors FROM stg_nobel_report_2 WHERE TRIM(employee) LIKE '%Алпысбай%'

Вопрос: Сколько сотрудников уволено за прошлый месяц?
DATA_QUERY: SELECT COUNT(*) as total FROM employee_events WHERE event_type='dismissed' AND YEAR(event_date)=YEAR(CURRENT_DATE - INTERVAL 1 MONTH) AND MONTH(event_date)=MONTH(CURRENT_DATE - INTERVAL 1 MONTH)

Вопрос: Сколько сотрудников сейчас активны (не уволены, не в декрете)?
DATA_QUERY: SELECT COUNT(*) as total FROM employees e WHERE (SELECT ev.event_type FROM employee_events ev WHERE ev.employee_id = e.id ORDER BY ev.event_date DESC LIMIT 1) IN ('hired','return_from_leave')

Вопрос: Кто уволился в этом году?
DATA_QUERY: SELECT e.full_name, ev.event_date FROM employee_events ev JOIN employees e ON e.id = ev.employee_id WHERE ev.event_type='dismissed' AND YEAR(ev.event_date)=YEAR(CURDATE()) ORDER BY ev.event_date DESC LIMIT 20

Вопрос: Топ 5 сотрудников по продажам КМП за 2026 год по каждой группе (сумма по Amount_disc)
DATA_QUERY_MYSQL: SELECT e.id, e.full_name, km.kmp_employee_name, t.team FROM employees e JOIN employee_kmp_names km ON km.employee_id = e.id JOIN employee_territory et ON et.employee_id = e.id JOIN territories t ON t.id = et.territory_id WHERE et.assigned_at = (SELECT MAX(et2.assigned_at) FROM employee_territory et2 WHERE et2.employee_id = e.id)
DATA_QUERY_NOBEL: SELECT TRIM(`Медпредставитель`) AS kmp_name, SUM(`Amount_disc`) AS total_sales FROM kmp WHERE `Год` = 2026 AND `Статус заказа` = 'Доставлено' GROUP BY TRIM(`Медпредставитель`)

ВАЖНО: `Бизнес-подразделение` в kmp — это НЕ то же самое, что territories.team (группа).
Если вопрос про группу/команду (напр. "1 GROUP", "MSS") — фильтруй team ТОЛЬКО в
DATA_QUERY_MYSQL (WHERE t.team = ...), а DATA_QUERY_NOBEL всегда делай без фильтра по
группе/подразделению, просто агрегируй ВСЕХ по `Медпредставитель` — сопоставление
с нужной группой произойдёт само по ФИО на шаге объединения данных.
PROMPT;

    public function index(Request $request)
    {
        $history = $request->session()->get(self::SESSION_KEY, []);
        return view('chatbot', compact('history'));
    }

    public function handle(Request $request)
    {
        $userMessage = trim($request->input('message', ''));
        if ($userMessage === '') {
            return response()->json(['error' => 'Сообщение не может быть пустым.'], 422);
        }

        $history  = $request->session()->get(self::SESSION_KEY, []);
        $messages = $this->buildMessages($history, $userMessage);

        // Шаг 1: Gemini генерирует SQL или отвечает напрямую
        $step1   = $this->callGemini($messages);
        $trimmed = trim($step1);

        if (str_contains($trimmed, self::MYSQL_MARKER) || str_contains($trimmed, self::NOBEL_MARKER)) {
            $botReply = $this->executeMultiAndAnswer($userMessage, $trimmed, $messages);
        } elseif (str_starts_with($trimmed, self::SQL_MARKER)) {
            $sql      = trim(substr($trimmed, strlen(self::SQL_MARKER)));
            $botReply = $this->executeAndAnswer($userMessage, $sql, $messages);
        } else {
            $botReply = $step1;
        }

        // Защита от утечки: если модель на любом шаге (включая шаг 2 — "сформулируй
        // ответ по данным") снова вместо связного ответа выдала DATA_QUERY-маркер
        // (бывает при длинной/запутанной истории диалога), никогда не показывать
        // сырой SQL пользователю — это внутренняя техническая деталь.
        if (str_contains($botReply, self::SQL_MARKER) || str_contains($botReply, self::MYSQL_MARKER) || str_contains($botReply, self::NOBEL_MARKER)) {
            Log::error('AI chatbot leaked raw query marker into final reply', ['question' => $userMessage, 'reply' => $botReply]);
            $botReply = 'Не удалось сформулировать ответ. Попробуйте переформулировать вопрос.';
        }

        // Сохраняем только user + финальный ответ (SQL-шаг скрыт)
        $history[] = ['role' => 'user',      'content' => $userMessage];
        $history[] = ['role' => 'assistant', 'content' => $botReply];

        if (count($history) > self::MAX_HISTORY) {
            $history = array_slice($history, -self::MAX_HISTORY);
        }
        $request->session()->put(self::SESSION_KEY, $history);

        return response()->json(['reply' => $botReply]);
    }

    public function clearHistory(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);
        return response()->json(['status' => 'ok']);
    }

    private function executeAndAnswer(string $question, string $sql, array $messages): string
    {
        if (!$this->isSafeQuery($sql)) {
            return 'Запрос не может быть выполнен по соображениям безопасности.';
        }

        try {
            $connection = $this->pickConnection($sql);
            $rows       = DB::connection($connection)->select($sql);
            $data       = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('AI chatbot SQL error', ['sql' => $sql, 'error' => $e->getMessage()]);
            return 'Не удалось получить данные из базы. Попробуйте переформулировать вопрос.';
        }

        // Шаг 2: Gemini формулирует ответ на основе реальных данных
        $messages[] = [
            'role'    => 'user',
            'content' => "Вопрос: {$question}\n\nДанные из БД:\n{$data}\n\nДанные уже получены — это финальный шаг. Дай чёткий понятный ответ на русском языке "
                . 'обычным текстом. НЕ используй маркеры DATA_QUERY/DATA_QUERY_MYSQL/DATA_QUERY_NOBEL и не пиши SQL — они здесь неуместны.',
        ];

        return $this->callGemini($messages);
    }

    private function executeMultiAndAnswer(string $question, string $step1, array $messages): string
    {
        $queries = [];
        foreach (explode("\n", $step1) as $line) {
            $line = trim($line);
            if (str_starts_with($line, self::MYSQL_MARKER)) {
                $queries['mysql'] = trim(substr($line, strlen(self::MYSQL_MARKER)));
            } elseif (str_starts_with($line, self::NOBEL_MARKER)) {
                $queries['nobel'] = trim(substr($line, strlen(self::NOBEL_MARKER)));
            }
        }

        $datasets = [];
        foreach ($queries as $connection => $sql) {
            if (!$this->isSafeQuery($sql)) {
                return 'Запрос не может быть выполнен по соображениям безопасности.';
            }
            try {
                $datasets[$connection] = DB::connection($connection)->select($sql);
            } catch (\Exception $e) {
                Log::error('AI chatbot SQL error', ['sql' => $sql, 'connection' => $connection, 'error' => $e->getMessage()]);
                return 'Не удалось получить данные из базы. Попробуйте переформулировать вопрос.';
            }
        }

        // Если получилось — соединяем и сортируем детерминированно в PHP (надёжнее,
        // чем просить LLM вручную ранжировать десятки строк по сумме — на практике
        // модель ошибается в ранжировании при большом числе строк).
        $merged = $this->mergeByKmpName($datasets);

        if ($merged !== null) {
            $data        = json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $instruction = 'Данные ниже уже объединены по сотрудникам и отсортированы по убыванию суммы. '
                . 'Просто сгруппируй/отфильтруй как просит вопрос и возьми нужное количество строк по порядку — '
                . 'НЕ пересчитывай и НЕ переупорядочивай суммы самостоятельно.';
        } else {
            $data        = json_encode($datasets, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $instruction = 'Сопоставь записи между источниками (например, по ФИО — учитывай, что формат имени может отличаться).';
        }

        $messages[] = [
            'role'    => 'user',
            'content' => "Вопрос: {$question}\n\nДанные из БД:\n{$data}\n\n{$instruction} Данные уже получены — это финальный шаг. Дай чёткий понятный ответ на русском языке "
                . 'обычным текстом. НЕ используй маркеры DATA_QUERY/DATA_QUERY_MYSQL/DATA_QUERY_NOBEL и не пиши SQL — они здесь неуместны.',
        ];

        return $this->callGemini($messages);
    }

    /**
     * Детерминированный join mysql+nobel датасетов по имени сотрудника — срабатывает
     * только когда узнаёт паттерн колонок (kmp_employee_name на стороне mysql,
     * kmp_name + числовая сумма на стороне nobel). Иначе null — LLM работает с сырыми
     * данными сама.
     */
    private function mergeByKmpName(array $datasets): ?array
    {
        if (empty($datasets['mysql']) || empty($datasets['nobel'])) {
            return null;
        }

        $mysqlRows = $datasets['mysql'];
        $nobelRows = $datasets['nobel'];
        $firstMysql = (array) $mysqlRows[0];
        $firstNobel = (array) $nobelRows[0];

        if (!array_key_exists('kmp_employee_name', $firstMysql) || !array_key_exists('kmp_name', $firstNobel)) {
            return null;
        }

        $valueKey = null;
        foreach ($firstNobel as $key => $value) {
            if ($key !== 'kmp_name' && is_numeric($value)) {
                $valueKey = $key;
                break;
            }
        }
        if ($valueKey === null) {
            return null;
        }

        $normalize = fn(string $name) => array_values(array_filter(preg_split('/\s+/u', mb_strtolower(trim($name)))));

        $merged = [];
        foreach ($mysqlRows as $mRow) {
            $mArr   = (array) $mRow;
            $tokens = $normalize($mArr['kmp_employee_name'] ?? '');
            if (empty($tokens)) {
                continue;
            }

            $match = null;
            foreach ($nobelRows as $nRow) {
                $nArr   = (array) $nRow;
                $nName  = mb_strtolower(trim($nArr['kmp_name'] ?? ''));
                $allHit = true;
                foreach ($tokens as $t) {
                    if (!str_contains($nName, $t)) {
                        $allHit = false;
                        break;
                    }
                }
                if ($allHit) {
                    $match = $nArr;
                    break;
                }
            }

            $row = $mArr;
            unset($row['kmp_employee_name']);
            $row[$valueKey] = $match[$valueKey] ?? 0;
            $merged[] = $row;
        }

        usort($merged, fn($a, $b) => $b[$valueKey] <=> $a[$valueKey]);

        return $merged;
    }

    private function isSafeQuery(string $sql): bool
    {
        $upper = strtoupper(trim($sql));
        return str_starts_with($upper, 'SELECT')
            && !preg_match('/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|GRANT|EXEC)\b/', $upper);
    }

    private function pickConnection(string $sql): string
    {
        $sqlLower    = strtolower($sql);
        $nobelTables = ['qs_calls', 'qs_onekey', 'stg_nobel'];
        foreach ($nobelTables as $table) {
            if (str_contains($sqlLower, $table)) {
                return 'nobel';
            }
        }
        // 'kmp' матчится отдельно word-boundary — иначе ложно ловит employee_kmp_names
        if (preg_match('/(?<![a-z_])kmp(?![a-z_])/', $sqlLower)) {
            return 'nobel';
        }
        return 'mysql';
    }

    private function buildMessages(array $history, string $userMessage): array
    {
        $messages = array_map(fn($m) => [
            'role'    => $m['role'],
            'content' => $m['content'],
        ], $history);
        $messages[] = ['role' => 'user', 'content' => $userMessage];
        return $messages;
    }

    private function callGemini(array $messages): string
    {
        try {
            // Gemini API: свой формат — роли "user"/"model" (не "assistant"),
            // текст сообщения обёрнут в parts[], системный промпт — отдельным
            // полем systemInstruction (как у Anthropic, но по-другому названо).
            $contents = array_map(fn($m) => [
                'role'  => $m['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $m['content']]],
            ], $messages);

            $model = config('services.gemini.model');
            $key   = config('services.gemini.key');

            $response = Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}", [
                    'contents'          => $contents,
                    'systemInstruction' => ['parts' => [['text' => $this->systemPrompt]]],
                ]);

            if ($response->failed()) {
                Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
                return 'Не удалось получить ответ от AI. Попробуйте позже.';
            }

            return $response->json('candidates.0.content.parts.0.text') ?? 'Нет ответа.';

        } catch (\Exception $e) {
            Log::error('Gemini exception', ['message' => $e->getMessage()]);
            return 'Ошибка соединения с AI. Попробуйте позже.';
        }
    }
}
