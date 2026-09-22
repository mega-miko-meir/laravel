<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

class AvailableResourcesService
{
    /**
     * Полевые должности — те, для кого планшет/территория нужны в первую
     * очередь (в отличие от Product/Marketing/CRM-manager). См. Employee::position.
     */
    public const FIELD_ROLES = ['Rep', 'RM', 'FFM'];

    private function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Именно scopeActive(), а не "последнее событие — hired" — иначе
        // сотрудник, вернувшийся из декрета (return_from_leave), молча
        // выпадал бы из "без планшета"/"без территории", хотя реально
        // активен и ничего не назначено.
        return Employee::active()->orderBy('full_name');
    }

    /**
     * Полевые (Rep/RM/FFM) — в начале списка, остальные — следом; сортировка
     * стабильна (PHP 8+), поэтому алфавитный порядок внутри каждой группы
     * (из orderBy('full_name') в baseQuery()) сохраняется.
     */
    private function fieldRolesFirst(Collection $employees): Collection
    {
        return $employees
            ->sortByDesc(fn ($e) => in_array($e->position, self::FIELD_ROLES))
            ->values();
    }

    public function getAvailableForTablet(): Collection
    {
        return $this->fieldRolesFirst(
            $this->baseQuery()
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
                ->get()
        );
    }

    public function getAvailableForTerritory(): Collection
    {
        return $this->fieldRolesFirst(
            $this->baseQuery()
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
                ->get()
        );
    }
}
