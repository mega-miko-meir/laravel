<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

class AvailableResourcesService
{
    private function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Employee::whereHas('events', function ($q) {
            $q->whereIn('event_type', ['new', 'hired'])
              ->whereRaw('event_date = (
                  SELECT MAX(event_date) FROM employee_events
                  WHERE employee_events.employee_id = employees.id
              )');
        })->orderBy('full_name');
    }

    public function getAvailableForTablet(): Collection
    {
        return $this->baseQuery()
            ->where(function ($q) {
                $q->whereDoesntHave('employee_tablet')
                  ->orWhereHas('employee_tablet', function ($sub) {
                      $sub->whereNotNull('returned_at')
                          ->whereRaw('assigned_at = (
                              SELECT MAX(assigned_at) FROM employee_tablet
                              WHERE employee_tablet.employee_id = employees.id
                          )');
                  });
            })
            ->get();
    }

    public function getAvailableForTerritory(): Collection
    {
        return $this->baseQuery()
            ->where(function ($q) {
                $q->whereDoesntHave('employee_territory')
                  ->orWhereHas('employee_territory', function ($sub) {
                      $sub->whereNotNull('unassigned_at')
                          ->whereRaw('assigned_at = (
                              SELECT MAX(assigned_at) FROM employee_territory
                              WHERE employee_territory.employee_id = employees.id
                          )');
                  });
            })
            ->get();
    }
}
