<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Collection;

class TeamService
{
    public function getTeamStructure(): Collection
    {
        // Step 1: load FFMs with their territory assignments (no deep nesting yet)
        $ffms = Employee::where('position', 'FFM')
            ->with([
                'latestEvent',
                'employee_territory' => fn($q) => $q->orderBy('assigned_at', 'desc'),
            ])
            ->get();

        // Step 2: resolve each FFM's current territory
        $ffms->each(function (Employee $ffm) {
            $ffm->lastTerritory = $ffm->employee_territory->first();
        });

        // Step 3: collect all FFM territory IDs and eager-load the full tree in one go
        $territoryIds = $ffms
            ->pluck('lastTerritory')
            ->filter()
            ->pluck('id');

        if ($territoryIds->isNotEmpty()) {
            \App\Models\Territory::whereIn('id', $territoryIds)
                ->with([
                    'children' => fn($q) => $q->orderBy('city'),
                    'children.employeeTerritories.employee.latestEvent',
                    'children.children' => fn($q) => $q->orderBy('city'),
                    'children.children.employeeTerritories.employee.latestEvent',
                ])
                ->get()
                ->keyBy('id')
                ->each(function ($loadedTerritory) use ($ffms) {
                    // Attach the fully-loaded territory back to the correct FFM
                    $ffms->each(function (Employee $ffm) use ($loadedTerritory) {
                        if ($ffm->lastTerritory?->id === $loadedTerritory->id) {
                            $ffm->lastTerritory = $loadedTerritory;
                        }
                    });
                });
        }

        // Step 4: compute stats and prepare display data
        return $ffms->map(function (Employee $ffm) {
            if (!$ffm->lastTerritory) {
                $ffm->ffmStats    = $this->emptyStats();
                $ffm->preparedRms = collect();
                return $ffm;
            }

            $ffm->ffmStats    = $this->calculateStats($ffm->lastTerritory);
            $ffm->preparedRms = $this->prepareRms($ffm->lastTerritory);

            return $ffm;
        });
    }

    // -------------------------------------------------------------------------
    // Department-level aggregation (called in controller, not view)
    // -------------------------------------------------------------------------

    public function groupByDepartment(Collection $ffms): Collection
    {
        return $ffms
            ->sortByDesc(fn($f) => $f->lastTerritory?->department)
            ->groupBy(fn($f) => $f->lastTerritory->department ?? 'Без департамента')
            ->map(function (Collection $deptFfms, string $deptName) {
                return [
                    'name'       => $deptName,
                    'ffms'       => $deptFfms,
                    'stats'      => $this->calculateDeptStats($deptFfms),
                    'teamView'   => $this->buildTeamView($deptFfms),
                ];
            });
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function prepareRms(object $lastTerritory): Collection
    {
        return $lastTerritory->children->map(function ($rm) {

            $activeAssignment = $rm->employeeTerritories
                ->whereNull('unassigned_at')
                ->sortByDesc('assigned_at')
                ->first();

            $dismissedAssignment = $rm->employeeTerritories
                ->whereNotNull('unassigned_at')
                ->sortByDesc('assigned_at')
                ->first();

            $rm->activeEmployee    = $activeAssignment?->employee;
            $rm->dismissedEmployee = $dismissedAssignment?->employee;

            // Pre-compute free slots badge
            $allPlaces      = $rm->children->count();
            $occupiedPlaces = 0;

            $rm->preparedReps = $rm->children->map(function ($rep) use (&$occupiedPlaces) {

                $active = $rep->employeeTerritories
                    ->whereNull('unassigned_at')
                    ->sortByDesc('assigned_at')
                    ->first();

                $last = $rep->employeeTerritories
                    ->sortByDesc('assigned_at')
                    ->first();

                if ($active) {
                    $occupiedPlaces++;
                }

                $employee = $active?->employee;
                $fallback = $last?->employee;

                $isNew = $employee?->latestEvent?->event_date &&
                    \Carbon\Carbon::parse($employee->latestEvent->event_date)
                        ->greaterThanOrEqualTo(now()->subDays(30));

                return (object) [
                    'territory'  => $rep,
                    'employee'   => $employee,
                    'fallback'   => $fallback,
                    'isNew'      => $isNew,
                    'team'       => $rep->team ?? 'Без группы',
                    'city'       => $rep->city,
                    'territoryId'=> $rep->id,
                ];
            })->sortBy('team')->groupBy('team');

            $rm->freePlaces = $allPlaces - $occupiedPlaces;

            return $rm;
        });
    }

    private function calculateStats(object $territory): array
    {
        $rmTotal = $rmUsed = 0;
        $repTotal = $repUsed = 0;
        $teams = [];

        foreach ($territory->children as $rm) {
            $rmTotal++;

            $rmActive = $rm->employeeTerritories
                ->whereNull('unassigned_at')
                ->first();

            if ($rmActive) {
                $rmUsed++;
            }

            foreach ($rm->children as $rep) {
                $repTotal++;

                $repActive = $rep->employeeTerritories
                    ->whereNull('unassigned_at')
                    ->first();

                if ($repActive) {
                    $repUsed++;
                }

                $team = $rep->team ?? 'Без группы';
                $teams[$team]['total'] = ($teams[$team]['total'] ?? 0) + 1;

                if ($repActive) {
                    $teams[$team]['used'] = ($teams[$team]['used'] ?? 0) + 1;
                }
            }
        }

        ksort($teams, SORT_NATURAL | SORT_FLAG_CASE);

        return compact('rmTotal', 'rmUsed', 'repTotal', 'repUsed', 'teams');
    }

    private function calculateDeptStats(Collection $deptFfms): array
    {
        $rmTotal = $rmUsed = 0;
        $repTotal = $repUsed = 0;
        $teams = [];

        foreach ($deptFfms as $ffm) {
            $stats = $ffm->ffmStats;

            $rmTotal  += $stats['rmTotal'];
            $rmUsed   += $stats['rmUsed'];
            $repTotal += $stats['repTotal'];
            $repUsed  += $stats['repUsed'];

            foreach ($stats['teams'] as $teamName => $teamStat) {
                $teams[$teamName]['total'] = ($teams[$teamName]['total'] ?? 0) + $teamStat['total'];
                $teams[$teamName]['used']  = ($teams[$teamName]['used']  ?? 0) + ($teamStat['used'] ?? 0);
            }
        }

        ksort($teams, SORT_NATURAL | SORT_FLAG_CASE);

        return compact('rmTotal', 'rmUsed', 'repTotal', 'repUsed', 'teams');
    }

    private function buildTeamView(Collection $deptFfms): Collection
    {
        $repRows = collect();

        foreach ($deptFfms as $ffm) {
            if (!$ffm->lastTerritory) {
                continue;
            }

            foreach ($ffm->lastTerritory->children as $rm) {
                foreach ($rm->children as $rep) {
                    $active = $rep->employeeTerritories
                        ->whereNull('unassigned_at')
                        ->sortByDesc('assigned_at')
                        ->first();

                    $last = $rep->employeeTerritories
                        ->sortByDesc('assigned_at')
                        ->first();

                    $employee = $active?->employee;
                    $fallback = $last?->employee;

                    $isNew = $employee?->latestEvent?->event_date &&
                        \Carbon\Carbon::parse($employee->latestEvent->event_date)
                            ->greaterThanOrEqualTo(now()->subDays(30));

                    $repRows->push((object) [
                        'territory'   => $rep,
                        'employee'    => $employee,
                        'fallback'    => $fallback,
                        'isNew'       => $isNew,
                        'team'        => $rep->team ?? 'Без группы',
                        'city'        => $rep->city,
                        'territoryId' => $rep->id,
                    ]);
                }
            }
        }

        // Group by team, sort each team's reps by city
        return $repRows
            ->sortBy('team')
            ->groupBy('team')
            ->map(function (Collection $reps, string $teamName) {
                $sorted   = $reps->sortBy('city');
                $total    = $sorted->count();
                $usedRep  = $sorted->filter(fn($r) => $r->employee !== null)->count();

                return (object) [
                    'name'    => $teamName,
                    'reps'    => $sorted,
                    'total'   => $total,
                    'usedRep' => $usedRep,
                ];
            });
    }

    private function emptyStats(): array
    {
        return [
            'rmTotal'  => 0,
            'rmUsed'   => 0,
            'repTotal' => 0,
            'repUsed'  => 0,
            'teams'    => [],
        ];
    }
}
