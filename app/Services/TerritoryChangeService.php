<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TerritoryChangeService
{
    /**
     * Находит сотрудников, у которых в указанном периоде сменилась территория
     * (появилась новая запись в employee_territory), и при этом изменилась
     * группа (team) и/или менеджер (сотрудник, назначенный на родительскую
     * территорию) по сравнению с предыдущей территорией этого сотрудника.
     * Первое назначение сотрудника (нет предыдущей территории) в выборку
     * не попадает — сравнивать не с чем, это не "смена".
     */
    public function getChanges(string $from, string $to): Collection
    {
        return DB::table('employee_territory as et_new')
            ->join('employees as e', 'e.id', '=', 'et_new.employee_id')
            ->join('territories as t_new', 't_new.id', '=', 'et_new.territory_id')
            ->join('employee_territory as et_old', function ($j) {
                $j->on('et_old.employee_id', '=', 'et_new.employee_id')
                  ->whereRaw('et_old.assigned_at = (
                      SELECT MAX(et3.assigned_at) FROM employee_territory et3
                      WHERE et3.employee_id = et_new.employee_id AND et3.assigned_at < et_new.assigned_at
                  )');
            })
            ->join('territories as t_old', 't_old.id', '=', 'et_old.territory_id')
            ->leftJoin('territories as pt_old', 'pt_old.id', '=', 't_old.parent_territory_id')
            ->leftJoin('employees as mgr_old', 'mgr_old.id', '=', 'pt_old.employee_id')
            ->leftJoin('territories as pt_new', 'pt_new.id', '=', 't_new.parent_territory_id')
            ->leftJoin('employees as mgr_new', 'mgr_new.id', '=', 'pt_new.employee_id')
            ->whereBetween('et_new.assigned_at', [$from, $to])
            ->where(function ($q) {
                $q->whereColumn('t_old.team', '!=', 't_new.team')
                  ->orWhereRaw('COALESCE(mgr_old.id,0) != COALESCE(mgr_new.id,0)');
            })
            ->select([
                'e.id as employee_id',
                'e.full_name',
                'et_new.assigned_at as changed_at',
                't_old.team as old_team', 't_new.team as new_team',
                't_old.territory_name as old_territory', 't_new.territory_name as new_territory',
                'mgr_old.full_name as old_manager', 'mgr_new.full_name as new_manager',
            ])
            ->orderByDesc('et_new.assigned_at')
            ->get();
    }
}
