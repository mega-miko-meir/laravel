<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeEventStatsService
{
    private function baseCountQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('employee_events as ee1')
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )');
    }

    private function applyTypes(\Illuminate\Database\Query\Builder $query, string|array $types): \Illuminate\Database\Query\Builder
    {
        return is_array($types)
            ? $query->whereIn('ee1.event_type', $types)
            : $query->where('ee1.event_type', $types);
    }

    private function applyTypesToList(\Illuminate\Database\Query\Builder $query, string|array $types): \Illuminate\Database\Query\Builder
    {
        return is_array($types)
            ? $query->whereIn('ev.event_type', $types)
            : $query->where('ev.event_type', $types);
    }

    public function countWithLatestEvent(string|array $types): int
    {
        return $this->applyTypes($this->baseCountQuery(), $types)->count();
    }

    public function countByMonth(string|array $types, int $month, int $year): int
    {
        $query = $this->baseCountQuery()
            ->whereMonth('ee1.event_date', $month)
            ->whereYear('ee1.event_date', $year);

        return $this->applyTypes($query, $types)->count();
    }

    public function countByYear(string|array $types, int $year): int
    {
        $query = $this->baseCountQuery()
            ->whereYear('ee1.event_date', $year);

        return $this->applyTypes($query, $types)->count();
    }

    private function baseListQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('employees as e')
            ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
            ->select('e.*', 'ev.event_type', 'ev.event_date')
            ->orderBy('ev.event_date', 'DESC');
    }

    public function getWithLatestEvent(string|array $types): Collection
    {
        $query = $this->baseListQuery()
            ->whereRaw('ev.id = (
                SELECT ee.id FROM employee_events ee
                WHERE ee.employee_id = ev.employee_id
                ORDER BY ee.event_date DESC
                LIMIT 1
            )');

        return $this->applyTypesToList($query, $types)->get();
    }

    public function getByMonth(string|array $types, int $month, int $year): Collection
    {
        $query = $this->baseListQuery()
            ->whereMonth('ev.event_date', $month)
            ->whereYear('ev.event_date', $year);

        return $this->applyTypesToList($query, $types)->get();
    }

    public function getByYear(string|array $types, int $year): Collection
    {
        $query = $this->baseListQuery()
            ->whereYear('ev.event_date', $year);

        return $this->applyTypesToList($query, $types)->get();
    }
}
