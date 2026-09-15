<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * "Таргетные клиенты" — врачи и аптеки, назначенные медпредам для покрытия
 * (план визитов) за выбранный месяц (qs_calls, Nobel CRM). Два независимых сегмента:
 *
 *   Врачи:  appointment_type = 'Визит к врачу' AND organization_type IN ('ЛПУ','Прочие')
 *   Аптеки: appointment_type = 'Визит в аптеку' AND organization_type = 'Аптечные учреждения'
 *
 * НЕ фильтруется по appointment_status — это таргет-лист (план покрытия), а не
 * факт выполненных визитов, поэтому обычное для qs_calls (см. CLAUDE.md) правило
 * "только appointment_status = 'Выполнено'" здесь намеренно не применяется.
 * Уникальность врача — customer_id, аптеки — organization_id.
 */
class TargetClientsService
{
    public const SEGMENT_DOCTORS    = 'doctors';
    public const SEGMENT_PHARMACIES = 'pharmacies';

    /** Колонки экспорта (в этом порядке) — состав задан бизнес-требованием, не менять произвольно. */
    private const EXPORT_COLUMNS = [
        self::SEGMENT_DOCTORS => [
            'province', 'town', 'employee_department', 'manager', 'employee',
            'organization', 'organization_address',
            'customer_id', 'customer', 'customer_spesiality', 'month', 'year',
        ],
        self::SEGMENT_PHARMACIES => [
            'province', 'town', 'employee_department', 'manager', 'employee',
            'organization', 'organization_address', 'organization_id',
            'customer_id', 'customer', 'customer_spesiality', 'month', 'year',
        ],
    ];

    private const EXPORT_LABELS = [
        'province'             => 'Регион',
        'town'                => 'Город',
        'employee_department' => 'Отдел',
        'manager'              => 'Менеджер',
        'employee'             => 'Сотрудник',
        'organization'         => 'Организация',
        'organization_address' => 'Адрес организации',
        'organization_id'      => 'ID организации',
        'customer_id'          => 'ID врача',
        'customer'             => 'ФИО / название',
        'customer_spesiality'  => 'Специальность',
        'month'                => 'Месяц',
        'year'                 => 'Год',
    ];

    /** Уникальный ключ клиента для KPI/дерева: врачи — customer_id, аптеки — organization_id. */
    private function uniqueKeyColumn(string $segment): string
    {
        return $segment === self::SEGMENT_PHARMACIES ? 'organization_id' : 'customer_id';
    }

    /**
     * employee_department в Nobel CRM разбит на пары-варианты одной и той же группы
     * (М/К-суффикс) и вдобавок пишется то латиницей "OTC", то кириллицей "ОТС" —
     * сводим к одному общему названию группы. Явный список, а не регулярка: значений
     * всего 12, и с смешанной кириллицей/латиницей регулярка ненадёжнее словаря.
     */
    /**
     * public — единственный источник этой карты в проекте. Другие потребители
     * (сейчас: CallController на странице «Визиты», и JS там же через
     * @json(TargetClientsService::DEPARTMENT_GROUP_MAP)) берут её отсюда, а не
     * держат свою копию — иначе рано или поздно расходятся (уже случалось).
     */
    public const DEPARTMENT_GROUP_MAP = [
        'OTC 1 М' => 'OTC 1',
        'ОТС 1 К' => 'OTC 1',
        'ОТС 2 К' => 'OTC 2',
        'ОТС 2 М' => 'OTC 2',
        'ОТС 3 К' => 'OTC 3',
        'ОТС 3 М' => 'OTC 3',
        'ОТС'     => 'OTC',
    ];

    public function normalizeDepartment(?string $raw): string
    {
        $raw = trim((string) $raw);
        return self::DEPARTMENT_GROUP_MAP[$raw] ?? ($raw !== '' ? $raw : '—');
    }

    /** Обратный маппинг: по общему названию группы — все "сырые" значения employee_department, которые под неё попадают. */
    public function rawDepartmentValues(string $normalized): array
    {
        $raw = array_keys(array_filter(self::DEPARTMENT_GROUP_MAP, fn($v) => $v === $normalized));
        return $raw ?: [$normalized];
    }

    /**
     * Базовый запрос таргет-листа сегмента (врачи/аптеки) за период — тот же
     * критерий "кто является целью", что использует и сама страница «Таргетные
     * клиенты», и страница «Визиты» (для карточек охвата и таргет-листов
     * фильтрации). public — чтобы CallController мог переиспользовать критерий,
     * не дублируя его своими WHERE-условиями.
     */
    public function baseQuery(string $segment, string $monthStart, string $monthEnd, ?string $department = null): Builder
    {
        // Без фильтра по appointment_status: таргет-лист — это план покрытия
        // (кто должен быть посещён), а не факт выполненных визитов, поэтому сюда
        // намеренно попадают все статусы (Запланировано/Перенесено/Отменено/Выполнено).
        $q = DB::connection('nobel')->table('qs_calls')
            ->where('employee_position', 'Медицинский представитель')
            ->whereBetween('appointment_Date', [$monthStart, $monthEnd]);

        if ($segment === self::SEGMENT_PHARMACIES) {
            $q->where('appointment_type', 'Визит в аптеку')
              ->where('organization_type', 'Аптечные учреждения');
        } else {
            $q->where('appointment_type', 'Визит к врачу')
              ->whereIn('organization_type', ['ЛПУ', 'Прочие']);
        }

        if ($department) {
            $q->whereIn('employee_department', $this->rawDepartmentValues($department));
        }

        return $q;
    }

    /**
     * KPI + дерево (отдел -> сотрудник) + для врачей дополнительно разрез по
     * специальности, ВСЕГДА за весь месяц целиком, без фильтра по отделу — за ОДИН
     * запрос к Nobel: DISTINCT (отдел, сотрудник, менеджер, [специальность], ключ
     * клиента), т.е. уже дедуплицированные связи "сотрудник видел этого клиента"
     * (без сырых visit-строк с датами/врачами/аптеками — их на порядок больше).
     * Список отделов для фильтра тоже берётся отсюда (union по обоим сегментам в
     * контроллере) — отдельного запроса на список отделов больше нет.
     *
     * Фильтрация по отделу на странице происходит ЦЕЛИКОМ на клиенте (Alpine.js,
     * без перезагрузки страницы и без похода на сервер) — раз тут уже посчитано
     * дерево по ВСЕМ отделам, выбор одного отдела в UI — это просто показать одну
     * ветку уже отданных в браузер данных. Задание бизнес-требования на серверную
     * фильтрацию по отделу сохранено только в Excel-экспорте (exportRows), где
     * выбор отдела реально сокращает объём выгружаемых строк.
     */
    public function summary(string $segment, string $monthStart, string $monthEnd): object
    {
        $cacheKey = "target_clients_summary_{$segment}_{$monthStart}";

        return Cache::remember($cacheKey, 1800, function () use ($segment, $monthStart, $monthEnd) {
            return $this->buildSummary($segment, $monthStart, $monthEnd);
        });
    }

    private function buildSummary(string $segment, string $monthStart, string $monthEnd): object
    {
        $keyCol = $this->uniqueKeyColumn($segment);

        $select = ['employee_department', 'employee', 'manager', $keyCol . ' as client_key'];
        if ($segment === self::SEGMENT_DOCTORS) {
            $select[] = 'customer_spesiality';
        }

        $rows = $this->baseQuery($segment, $monthStart, $monthEnd)
            ->select($select)
            ->distinct()
            ->get()
            ->map(fn($r) => tap($r, fn($row) => $row->employee_department = $this->normalizeDepartment($row->employee_department)));

        $tree = $rows
            ->groupBy('employee_department')
            ->map(function ($deptRows, $dept) {
                $byEmployee = $deptRows
                    ->groupBy(fn($r) => $r->employee ?: '—')
                    ->map(fn($empRows, $employee) => (object) [
                        'employee'     => $employee,
                        'manager'      => $empRows->first()->manager ?? '',
                        'client_count' => $empRows->unique('client_key')->count(),
                    ])
                    ->sortByDesc('client_count')
                    ->values();

                return (object) [
                    'department'   => $dept,
                    'client_count' => $deptRows->unique('client_key')->count(),
                    'employees'    => $byEmployee,
                ];
            })
            ->sortByDesc('client_count')
            ->values();

        $specialtyTree = null;
        $specialtyByDept = null;
        if ($segment === self::SEGMENT_DOCTORS) {
            $bySpecialty = fn($collection) => $collection
                ->groupBy(fn($r) => $r->customer_spesiality ?: '—')
                ->map(fn($specRows, $spec) => (object) [
                    'specialty'    => $spec,
                    'client_count' => $specRows->unique('client_key')->count(),
                ])
                ->sortByDesc('client_count')
                ->values();

            $specialtyTree = $bySpecialty($rows);
            $specialtyByDept = $rows
                ->groupBy('employee_department')
                ->map(fn($deptRows) => $bySpecialty($deptRows));
        }

        return (object) [
            'kpi'             => $rows->unique('client_key')->count(),
            'tree'            => $tree,
            'specialtyTree'   => $specialtyTree,
            'specialtyByDept' => $specialtyByDept,
        ];
    }

    /**
     * Агрегированные уникальные строки для экспорта — SQL DISTINCT по ровно тем
     * колонкам, что заданы бизнес-требованием (см. EXPORT_COLUMNS), без сырых
     * appointment-строк визитов.
     */
    public function exportRows(string $segment, string $monthStart, string $monthEnd, ?string $department = null): Collection
    {
        return $this->baseQuery($segment, $monthStart, $monthEnd, $department)
            ->select(self::EXPORT_COLUMNS[$segment])
            ->distinct()
            ->orderBy('employee_department')
            ->orderBy('employee')
            ->get();
    }

    public function exportColumns(string $segment): array
    {
        return self::EXPORT_COLUMNS[$segment];
    }

    public function exportLabels(string $segment): array
    {
        return array_map(fn($col) => self::EXPORT_LABELS[$col] ?? $col, self::EXPORT_COLUMNS[$segment]);
    }
}
