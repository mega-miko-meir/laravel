<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DataQualityService
{
    private function latestEventSub(string $employeesAlias = 'e', string $eventsAlias = 'ev'): string
    {
        return "{$eventsAlias}.id = (
            SELECT ee.id FROM employee_events ee
            WHERE ee.employee_id = {$employeesAlias}.id
            ORDER BY ee.event_date DESC, ee.id DESC
            LIMIT 1
        )";
    }

    private function joinLatestEvent(\Illuminate\Database\Query\Builder $query, string $employeesAlias = 'e', string $eventsAlias = 'ev'): \Illuminate\Database\Query\Builder
    {
        return $query->join("employee_events as {$eventsAlias}", function ($j) use ($employeesAlias, $eventsAlias) {
            $j->on("{$eventsAlias}.employee_id", '=', "{$employeesAlias}.id")
              ->whereRaw($this->latestEventSub($employeesAlias, $eventsAlias));
        });
    }

    /**
     * Одно и то же событие (сотрудник + тип + дата) записано больше одного раза.
     */
    public function duplicateEvents(): Collection
    {
        return DB::table('employee_events as ev')
            ->join('employees as e', 'e.id', '=', 'ev.employee_id')
            ->select('e.id as employee_id', 'e.full_name', 'ev.event_type', 'ev.event_date', DB::raw('COUNT(*) as cnt'))
            ->groupBy('e.id', 'e.full_name', 'ev.event_type', 'ev.event_date')
            ->having('cnt', '>', 1)
            ->orderByDesc('ev.event_date')
            ->get();
    }

    /**
     * У активных сотрудников статичное employees.position расходится с ролью
     * на последней территории (источник истины для роли везде в приложении).
     */
    public function positionMismatches(): Collection
    {
        $roleSub = "(
            SELECT t.role FROM employee_territory et
            JOIN territories t ON t.id = et.territory_id
            WHERE et.employee_id = e.id
            ORDER BY et.assigned_at DESC, et.id DESC
            LIMIT 1
        )";

        $rows = $this->joinLatestEvent(DB::table('employees as e'))
            ->whereIn('ev.event_type', ['hired', 'return_from_leave'])
            ->whereNotNull('e.position')->where('e.position', '<>', '')
            ->selectRaw("e.id, e.full_name, e.position, {$roleSub} as territory_role")
            ->get();

        return $rows->filter(fn($r) => $r->territory_role !== null && $r->territory_role !== $r->position)->values();
    }

    /**
     * Nobel CRM (qs_calls) заводит учётки только на Rep и RM — KAM/Product/
     * Marketing/FFM там в принципе не существуют, так что для них "нет
     * привязки" всегда true и не является реальной проблемой данных.
     */
    public function activeWithoutCrm(): Collection
    {
        return $this->activeEmployeesMissing('employee_crm_ids', ['Rep', 'RM']);
    }

    /**
     * KMP-продажи привязываются только по полю "Медпредставитель" (Rep) —
     * РМ там не фигурирует как отдельная привязываемая роль.
     */
    public function activeWithoutKmp(): Collection
    {
        return $this->activeEmployeesMissing('employee_kmp_names', ['Rep']);
    }

    /**
     * Активные сотрудники нужной должности, у которых нет ни одной привязанной
     * внешней учётки (many-to-one: проверяем отсутствие любых строк в
     * pivot-таблице, а не одно поле).
     */
    private function activeEmployeesMissing(string $pivotTable, array $positions): Collection
    {
        return $this->joinLatestEvent(DB::table('employees as e'))
            ->whereIn('ev.event_type', ['hired', 'return_from_leave'])
            ->whereIn('e.position', $positions)
            ->whereNotExists(function ($q) use ($pivotTable) {
                $q->select(DB::raw(1))
                    ->from($pivotTable . ' as pv')
                    ->whereColumn('pv.employee_id', 'e.id');
            })
            ->select('e.id', 'e.full_name', 'e.position')
            ->orderBy('e.full_name')
            ->get();
    }

    /**
     * Обратное направление к activeWithoutCrm(): у CRM-аккаунта (визиты в Nobel
     * CRM, Rep/RM по должности CRM) нет ни привязки в employee_crm_ids, ни вообще
     * похожего по имени сотрудника в employees — то есть это не "забыли
     * привязать", а человек в принципе не заведён в системе. activeWithoutCrm()
     * такие случаи не видит, поскольку смотрит только на таблицу employees.
     */
    public function crmAccountsWithoutEmployee(): Collection
    {
        $crmEmployees = DB::connection('nobel')->select("
            SELECT employee_id, TRIM(employee) as employee, employee_position
            FROM qs_calls
            WHERE employee_id IS NOT NULL AND employee IS NOT NULL AND employee <> ''
              AND employee_position IN ('Медицинский представитель', 'Региональный менеджер')
            GROUP BY employee_id, employee, employee_position
            ORDER BY employee
        ");

        $linkedCrmIds = DB::table('employee_crm_ids')->pluck('crm_employee_id')->flip();

        // Тот же алгоритм короткого имени (первые два слова), что и в
        // CrmMappingController::autoMatch() — если бы автопривязка нашла
        // совпадение, это не "отсутствующий" сотрудник, а просто непривязанный.
        $employeeShNames = DB::table('employees')->pluck('full_name')
            ->map(fn($name) => $this->shName($name))
            ->flip();

        return collect($crmEmployees)
            ->reject(fn($r) => $linkedCrmIds->has((int) $r->employee_id))
            ->reject(fn($r) => $employeeShNames->has($this->shName($r->employee)))
            ->map(fn($r) => (object) [
                'crm_employee_id' => (int) $r->employee_id,
                'employee'        => $r->employee,
                'position'        => $r->employee_position,
            ])
            ->values();
    }

    private function shName(string $name): string
    {
        return implode(' ', array_slice(explode(' ', trim($name)), 0, 2));
    }

    /**
     * Обратное направление к activeWithoutKmp(): у КМП-продавца (Медпредставитель
     * в kmp) нет ни привязки в employee_kmp_names, ни вообще похожего по имени
     * сотрудника в employees.
     */
    public function kmpAccountsWithoutEmployee(): Collection
    {
        $kmpEmployees = DB::connection('nobel')->select('
            SELECT TRIM(`Медпредставитель`) as name
            FROM kmp
            WHERE `Статус заказа` = "Доставлено"
              AND `Медпредставитель` IS NOT NULL AND `Медпредставитель` <> ""
            GROUP BY TRIM(`Медпредставитель`)
            ORDER BY `Медпредставитель`
        ');

        $linkedNames = DB::table('employee_kmp_names')->pluck('kmp_employee_name')->flip();

        $employeeShNames = DB::table('employees')->pluck('full_name')
            ->map(fn($name) => $this->shName($name))
            ->flip();

        return collect($kmpEmployees)
            ->reject(fn($r) => $linkedNames->has($r->name))
            ->reject(fn($r) => $employeeShNames->has($this->shName($r->name)))
            ->map(fn($r) => (object) ['employee' => $r->name])
            ->values();
    }

    /**
     * Территория помечена как активно назначенная (unassigned_at IS NULL)
     * сотруднику, чей последний статус — "уволен". Забыли снять при увольнении.
     */
    public function territoriesHeldByDismissed(): Collection
    {
        return DB::table('employee_territory as et')
            ->join('territories as t', 't.id', '=', 'et.territory_id')
            ->join('employees as e', 'e.id', '=', 'et.employee_id')
            ->join('employee_events as ev', function ($j) {
                $j->on('ev.employee_id', '=', 'e.id')->whereRaw($this->latestEventSub());
            })
            ->where('ev.event_type', 'dismissed')
            ->whereNull('et.unassigned_at')
            ->select('e.id as employee_id', 'e.full_name', 't.id as territory_id', 't.territory_name', 'et.assigned_at')
            ->orderByDesc('et.assigned_at')
            ->get();
    }

    /**
     * Планшет числится выданным (returned_at IS NULL) уволенному сотруднику.
     */
    public function tabletsHeldByDismissed(): Collection
    {
        return DB::table('employee_tablet as et')
            ->join('tablets as tb', 'tb.id', '=', 'et.tablet_id')
            ->join('employees as e', 'e.id', '=', 'et.employee_id')
            ->join('employee_events as ev', function ($j) {
                $j->on('ev.employee_id', '=', 'e.id')->whereRaw($this->latestEventSub());
            })
            ->where('ev.event_type', 'dismissed')
            ->whereNull('et.returned_at')
            ->select('e.id as employee_id', 'e.full_name', 'tb.id as tablet_id', 'tb.invent_number', 'et.assigned_at')
            ->orderByDesc('et.assigned_at')
            ->get();
    }

    /**
     * Ответственный за планшет (tablets.responsible_id) — уволенный сотрудник.
     */
    public function tabletsResponsibleDismissed(): Collection
    {
        return DB::table('tablets as tb')
            ->join('employees as e', 'e.id', '=', 'tb.responsible_id')
            ->join('employee_events as ev', function ($j) {
                $j->on('ev.employee_id', '=', 'e.id')->whereRaw($this->latestEventSub());
            })
            ->where('ev.event_type', 'dismissed')
            ->select('e.id as employee_id', 'e.full_name', 'tb.id as tablet_id', 'tb.invent_number')
            ->get();
    }
}
