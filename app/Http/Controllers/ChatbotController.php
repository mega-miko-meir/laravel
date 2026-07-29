<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private const SESSION_KEY = 'chatbot_history';
    private const MAX_HISTORY = 20;
    private const SQL_MARKER  = 'DATA_QUERY:';

    private string $systemPrompt = <<<'PROMPT'
Ты аналитический AI-ассистент компании Nobel (фармацевтика, Казахстан).
Отвечай на русском языке, лаконично и по делу.

У тебя есть доступ к базам данных проекта. Когда пользователь задаёт вопрос о данных
(сотрудники, визиты, продажи, статистика, покрытие) — сначала сформулируй SQL-запрос.
Ответ должен начинаться строго с маркера:
DATA_QUERY: <только SQL-запрос, без пояснений, без markdown>

Если вопрос не требует данных из БД (общий вопрос, приветствие) — отвечай напрямую без маркера.

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

=== СХЕМА БД (основная) ===

Таблица: employees — сотрудники системы
  id, full_name, position, crm_employee_id, phone, email

=== ПРАВИЛА SQL ===
- Только SELECT запросы
- Имена сотрудников хранятся с пробелами — используй TRIM(employee) LIKE '%Иванов%'
- Для дат: DATE(appointment_Date) или YEAR(), MONTH()
- LIMIT 20 если результат может быть большим
- Строковые значения в одинарных кавычках

=== ПРИМЕРЫ ===
Вопрос: Сколько визитов у Какиевой в июне 2026?
DATA_QUERY: SELECT COUNT(*) as total FROM qs_calls WHERE appointment_status='Выполнено' AND appointment_type IN ('Визит к врачу','Визит в аптеку') AND TRIM(employee) LIKE '%Какиева%' AND YEAR(appointment_Date)=2026 AND MONTH(appointment_Date)=6

Вопрос: Топ-5 МП по визитам за последний месяц?
DATA_QUERY: SELECT TRIM(employee) as employee, COUNT(*) as total FROM qs_calls WHERE appointment_status='Выполнено' AND appointment_type IN ('Визит к врачу','Визит в аптеку') AND appointment_Date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) GROUP BY TRIM(employee) ORDER BY total DESC LIMIT 5

Вопрос: Сколько врачей в базе у Алпысбай?
DATA_QUERY: SELECT COUNT(DISTINCT customer_id) as base_doctors FROM stg_nobel_report_2 WHERE TRIM(employee) LIKE '%Алпысбай%'
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

        // Шаг 1: Claude генерирует SQL или отвечает напрямую
        $step1 = $this->callClaude($messages);

        if (str_starts_with(trim($step1), self::SQL_MARKER)) {
            $sql      = trim(substr(trim($step1), strlen(self::SQL_MARKER)));
            $botReply = $this->executeAndAnswer($userMessage, $sql, $messages);
        } else {
            $botReply = $step1;
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

        // Шаг 2: Claude формулирует ответ на основе реальных данных
        $messages[] = [
            'role'    => 'user',
            'content' => "Вопрос: {$question}\n\nДанные из БД:\n{$data}\n\nДай чёткий понятный ответ на русском языке. Не упоминай SQL и технические детали.",
        ];

        return $this->callClaude($messages);
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
        $nobelTables = ['qs_calls', 'qs_onekey', 'stg_nobel', 'kmp'];
        foreach ($nobelTables as $table) {
            if (str_contains($sqlLower, $table)) {
                return 'nobel';
            }
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

    private function callClaude(array $messages): string
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'         => config('services.anthropic.key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => config('services.anthropic.model'),
                    'max_tokens' => 1024,
                    'system'     => $this->systemPrompt,
                    'messages'   => $messages,
                ]);

            if ($response->failed()) {
                Log::error('Claude API error', ['status' => $response->status(), 'body' => $response->body()]);
                return 'Не удалось получить ответ от AI. Попробуйте позже.';
            }

            return $response->json('content.0.text') ?? 'Нет ответа.';

        } catch (\Exception $e) {
            Log::error('Claude exception', ['message' => $e->getMessage()]);
            return 'Ошибка соединения с AI. Попробуйте позже.';
        }
    }
}
