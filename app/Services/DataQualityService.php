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

    public function activeWithoutCrm(): Collection
    {
        return $this->activeEmployeesMissing('employee_crm_ids');
    }

    public function activeWithoutKmp(): Collection
    {
        return $this->activeEmployeesMissing('employee_kmp_names');
    }

    /**
     * Активные сотрудники, у которых нет ни одной привязанной внешней учётки
     * (many-to-one: проверяем отсутствие любых строк в pivot-таблице, а не одно поле).
     */
    private function activeEmployeesMissing(string $pivotTable): Collection
    {
        return $this->joinLatestEvent(DB::table('employees as e'))
            ->whereIn('ev.event_type', ['hired', 'return_from_leave'])
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
