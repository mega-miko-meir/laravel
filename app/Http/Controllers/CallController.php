<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Nobel\Call;
use App\Services\TargetClientsService;
use App\Support\Etl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CallController extends Controller
{
    public function __construct(private TargetClientsService $target)
    {
    }

    /**
     * Порядок полей в каждой строке $rows (см. loadMonth()) — массив, а не объект,
     * чтобы не повторять имена ключей 30-40 тысяч раз в JSON (на объёме одного
     * месяца это разница в разы: qs_calls отдаёт до ~41k визитов/месяц).
     */
    private const ROW_FIELDS = [
        'appointment_Date', 'employee', 'employee_id', 'organization',
        'customer_spesiality', 'town', 'province', 'employee_department',
        'appointment_type', 'appointment_duration',
    ];

    /**
     * Версия формы payload'а loadMonth() — часть cache-ключа. Кэш живёт часами
     * (до ночного ETL), и при любом изменении состава полей возвращаемого
     * массива (например, onekeyVisited -> onekeyTarget) старые закэшированные
     * записи сохраняют СТАРУЮ форму — клиент получает смесь новых и отсутствующих
     * (undefined -> 0) полей молча, без ошибки. Бампать при каждом таком изменении.
     */
    private const CACHE_VERSION = 'v4';

    public function index(Request $request)
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));

        try {
            $data = $this->loadMonth($month);
        } catch (\Exception $e) {
            return back()->withErrors(['nobel_db' => 'Nobel CRM недоступна: ' . $e->getMessage()]);
        }

        return view('calls', [
            'month'             => $month,
            'initialData'       => $data,
            'departmentGroupMap' => TargetClientsService::DEPARTMENT_GROUP_MAP,
        ]);
    }

    /**
     * JSON-эндпоинт смены месяца без перезагрузки страницы (Alpine.js fetch).
     * Внутри месяца все фильтры (регион/город/специальность/группа/сотрудник/
     * сортировка/пагинация/поиск) работают ЦЕЛИКОМ на клиенте — вся выборка за
     * месяц уже в браузере, повторного похода на сервер не требуется. Только
     * смена месяца — реальный новый запрос к Nobel CRM.
     */
    public function data(Request $request)
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));

        try {
            // JSON_UNESCAPED_UNICODE: без него кириллица (почти весь текст тут)
            // кодируется как \uXXXX — 6 байт на символ вместо ~2 в UTF-8, что на
            // объёме одного месяца утраивало размер ответа.
            return response()->json($this->loadMonth($month), 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Nobel CRM недоступна: ' . $e->getMessage()]);
        }
    }

    private function loadMonth(string $month): array
    {
        set_time_limit(120);
        // Один месяц может дать 30-40 тыс. визитов — Eloquent-модели на каждую
        // строку (атрибуты, casts, dirty-tracking) съедают память в разы
        // быстрее, чем сырые stdClass из query builder; дефолтных 128M не
        // хватало даже на выборку одного тяжёлого месяца.
        ini_set('memory_limit', '512M');

        [$monthStart, $monthEnd] = $this->monthBounds($month);
        $ttl = Etl::secondsUntilNextRun();

        return Cache::remember("calls_month_" . self::CACHE_VERSION . "_{$month}", $ttl, function () use ($monthStart, $monthEnd) {
            // Вся выборка месяца — ОДИН запрос, без пагинации/сортировки/фильтров
            // на сервере (это теперь всё на клиенте). KPI/топ-регионы/топ-
            // специальности/список фильтров — тоже больше не отдельные запросы,
            // они считаются в браузере из этого же набора строк. Через query
            // builder (не Eloquent) — сырые stdClass, без накладных расходов модели.
            //
            // Категориальные поля (сотрудник/организация/специальность/город/
            // регион/группа/тип/дата) словарно кодируются: вместо повторения
            // строки в каждой из 30-40 тыс. строк — маленький целочисленный
            // индекс в общий словарь (см. $dictionaries в возвращаемом массиве).
            // На реальных данных это снизило JSON с ~22MB до размера, пригодного
            // для загрузки разом в браузер.
            $dict = ['date' => [], 'employee' => [], 'organization' => [], 'specialty' => [],
                     'town' => [], 'province' => [], 'department' => [], 'type' => []];
            $idx = function (string $col, string $val) use (&$dict) {
                return $dict[$col][$val] ??= count($dict[$col]);
            };

            $rows = DB::connection('nobel')->table('qs_calls')
                ->whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку'])
                ->where('appointment_status', 'Выполнено')
                ->whereBetween('appointment_Date', [$monthStart, $monthEnd])
                ->orderBy('appointment_Date', 'desc')
                ->get(self::ROW_FIELDS)
                ->map(function ($c) use ($idx) {
                    $date = $c->appointment_Date ? Carbon::parse($c->appointment_Date)->format('d.m.Y') : '—';
                    return [
                        $idx('date', $date),
                        $idx('employee', $c->employee ?? '—'),
                        $c->employee_id,
                        $idx('organization', $c->organization ?? '—'),
                        $idx('specialty', $c->customer_spesiality ?? '—'),
                        $idx('town', $c->town ?? '—'),
                        $idx('province', $c->province ?? '—'),
                        $idx('department', $c->employee_department ?? '—'),
                        $idx('type', $c->appointment_type ?? '—'),
                        $c->appointment_duration,
                    ];
                });

            // Тренд за последние 12 месяцев, заканчивая выбранным — единственный
            // виджет, который физически не может считаться из данных одного
            // месяца, остаётся отдельным лёгким агрегатом (не зависит от
            // категориальных фильтров страницы, только от выбранного месяца).
            $trendStart = Carbon::parse($monthStart)->subMonths(11)->startOfMonth();
            $trend = Call::query()
                ->whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку'])
                ->where('appointment_status', 'Выполнено')
                ->where('appointment_Date', '>=', $trendStart)
                ->where('appointment_Date', '<=', $monthEnd)
                ->selectRaw("DATE_FORMAT(appointment_Date, '%Y-%m') as month, COUNT(*) as total")
                ->groupBy('month')->orderBy('month')
                ->get();

            // Охват = доля рынка OneKey, попавшая в таргет-лист за выбранный месяц.
            // Числитель — таргетные клиенты (employee_position='Медицинский
            // представитель', без фильтра по статусу визита — таргет это план,
            // а не факт визита, см. TargetClientsService); знаменатель — весь
            // справочник OneKey (ёмкость рынка, не зависит ни от месяца, ни от
            // фильтров). Таргет-лист должен реагировать на те же фильтры
            // (регион/город/специальность/группа/сотрудник), что и остальная
            // страница — поэтому он не сводится на сервере к одному числу, а
            // грузится отдельным маленьким набором (уникальные клиенты, а не
            // визиты — на порядок меньше строк, чем в $rows) и фильтруется в
            // браузере той же логикой, что и таблица. Индексы категориальных
            // полей — из ОБЩИХ словарей выше ($idx), чтобы совпадали с $rows.
            $onekeyTotal = (int) (DB::connection('nobel')
                ->selectOne('SELECT COUNT(DISTINCT customer_id) AS n FROM qs_onekey_doctors')->n ?? 0);
            $pharmOnekeyTotal = (int) (DB::connection('nobel')
                ->selectOne('SELECT COUNT(DISTINCT organization_id) AS n FROM qs_onekey_pharmacy')->n ?? 0);

            // Критерий "кто является целью" (employee_position/appointment_type/
            // organization_type) — из TargetClientsService::baseQuery(), не
            // продублирован здесь: та же таблица "таргет", что и на странице
            // «Таргетные клиенты», только своя проекция колонок под клиентскую
            // фильтрацию/словарное кодирование этой страницы.
            $doctorTargets = $this->target
                ->baseQuery(TargetClientsService::SEGMENT_DOCTORS, $monthStart, $monthEnd)
                ->distinct()
                ->get(['customer_id', 'employee_id', 'province', 'town', 'employee_department', 'customer_spesiality'])
                ->map(fn($c) => [
                    $c->customer_id,
                    $c->employee_id,
                    $idx('province', $c->province ?? '—'),
                    $idx('town', $c->town ?? '—'),
                    $idx('department', $c->employee_department ?? '—'),
                    $idx('specialty', $c->customer_spesiality ?? '—'),
                ]);

            $pharmacyTargets = $this->target
                ->baseQuery(TargetClientsService::SEGMENT_PHARMACIES, $monthStart, $monthEnd)
                ->distinct()
                ->get(['organization_id', 'employee_id', 'province', 'town', 'employee_department'])
                ->map(fn($c) => [
                    $c->organization_id,
                    $c->employee_id,
                    $idx('province', $c->province ?? '—'),
                    $idx('town', $c->town ?? '—'),
                    $idx('department', $c->employee_department ?? '—'),
                ]);

            // Словари, использованные и в $rows, и в таргет-листах — пересобираем
            // ПОСЛЕ обоих проходов, чтобы включить значения, встретившиеся только
            // в таргет-листах (например, департамент сотрудника, у которого в
            // этом месяце не было ни одного ВЫПОЛНЕННОГО визита).
            $dictionaries = array_map(fn($d) => array_keys($d), $dict);

            // Список сотрудников для фильтра + их CRM-аккаунты — у одного
            // сотрудника может быть несколько crm_employee_id (повторный найм),
            // клиентский фильтр должен матчить строки по ЛЮБОМУ из них.
            $empList = Employee::whereHas('crmIds')
                ->orderBy('full_name')
                ->get(['id', 'full_name'])
                ->map(fn($e) => [
                    'label'  => $e->full_name,
                    'value'  => $e->id,
                    'crmIds' => $e->crm_employee_ids,
                ])
                ->values();

            return [
                'error'            => null,
                'rowFields'        => self::ROW_FIELDS,
                'rows'             => $rows,
                'dictionaries'     => $dictionaries,
                'trend'            => $trend,
                'onekeyTotal'      => $onekeyTotal,
                'pharmOnekeyTotal' => $pharmOnekeyTotal,
                'doctorTargets'    => $doctorTargets,
                'pharmacyTargets'  => $pharmacyTargets,
                'empList'          => $empList,
            ];
        });
    }

    public function export(Request $request)
    {
        set_time_limit(0);

        $columns = [
            'appointment_Date'     => 'Дата',
            'employee'             => 'Сотрудник',
            'manager'              => 'Менеджер',
            'customer'             => 'ФИО врача',
            'customer_id'          => 'ID клиента',
            'customer_spesiality'  => 'Специальность',
            'organization'         => 'Организация',
            'organization_type'    => 'Тип',
            'town'                 => 'Город',
            'province'             => 'Регион',
            'appointment_status'   => 'Статус',
            'appointment_type'     => 'Тип визита',
            'appointment_duration' => 'Длительность (мин)',
        ];

        $fileName = 'calls_' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($request, $columns) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, array_values($columns), ';');

            $this->filtered($request)->orderBy('appointment_Date', 'desc')->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $row) {
                    fputcsv($out, array_map(fn($col) => $row->$col ?? '', array_keys($columns)), ';');
                }
                ob_flush();
                flush();
            });

            fclose($out);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /** Экспорт по-прежнему серверный (нужна настоящая отправка файла) — принимает month + те же категориальные фильтры, что выбраны на клиенте. */
    private function filtered(Request $request)
    {
        $q = Call::query()
            ->whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку'])
            ->where('appointment_status', 'Выполнено');

        if ($request->filled('month')) {
            [$monthStart, $monthEnd] = $this->monthBounds($request->input('month'));
            $q->whereBetween('appointment_Date', [$monthStart, $monthEnd]);
        }
        if ($request->filled('province'))            $q->whereIn('province', (array) $request->input('province'));
        if ($request->filled('town'))                $q->whereIn('town', (array) $request->input('town'));
        if ($request->filled('employee_department')) {
            // Клиент присылает НОРМАЛИЗОВАННЫЕ названия групп (см.
            // TargetClientsService::DEPARTMENT_GROUP_MAP, единственный источник
            // этой карты в проекте) — разворачиваем обратно в сырые значения БД.
            $raw = collect((array) $request->input('employee_department'))
                ->flatMap(fn($name) => $this->target->rawDepartmentValues($name))
                ->all();
            $q->whereIn('employee_department', $raw);
        }
        if ($request->filled('customer_spesiality'))  $q->whereIn('customer_spesiality', (array) $request->input('customer_spesiality'));
        if ($request->filled('employee_id')) {
            $crmIds = Employee::find($request->input('employee_id'))?->crm_employee_ids ?? [];
            $q->whereIn('employee_id', $crmIds ?: [-1]);
        }

        return $q;
    }

    /** @return array{0: string, 1: string} [monthStart, monthEnd] в формате Y-m-d */
    private function monthBounds(string $month): array
    {
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        return [$date->toDateString(), $date->copy()->endOfMonth()->toDateString()];
    }
}
