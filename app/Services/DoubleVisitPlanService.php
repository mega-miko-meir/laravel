<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Факт/план/% выполнения двойных визитов РМ с их медпредами за период,
 * с детализацией до конкретного медпреда.
 *
 * Данные факта — Nobel CRM (qs_double_calls): каждая строка — визит к ОДНОМУ врачу
 * в рамках дня двойного визита, поэтому "1 двойной визит" = уникальная пара
 * (медпред, дата), а не строка. employee в этой таблице — сам РМ (кто ведёт визит),
 * double_visit_employee — медпред, с которым делается двойной визит.
 *
 * План рассчитывается из основной БД (territories/employee_territory/employee_events):
 * помесячно, на медпреда — 2 визита, ЕСЛИ у РМ в этом месяце ≤7 активных медпредов,
 * иначе (8 и более) норма снижается до 1 на каждого (нагрузка РМ ограничена).
 * Принятые в течение месяца — отдельная ступенчатая логика (см. employeeMonthTier):
 * до 10 числа — как у всех (по норме месяца), с 11 по 24 — фикс. 1 визит, с 25 и
 * позже — 0 (и в подсчёт численности для выбора нормы такой сотрудник не входит).
 * Состав подчинённых территорий (Rep, parent_territory_id) берётся ТЕКУЩИЙ (в схеме
 * нет истории смены родителя у территории), а вот кто реально занимал территорию и
 * был ли активен — учитывается помесячно, по датам. План РМ = сумма планов по медпредам.
 *
 * ВАЖНО: вся история назначений/событий/сырые строки факта подтягиваются НЕБОЛЬШИМ
 * фиксированным числом запросов и дальше обрабатываются в памяти — основная БД тоже
 * удалённая (192.168.33.39), и наивный вариант "запрос на каждого РМ/медпреда на
 * каждый месяц" не укладывается в 30-секундный лимит выполнения PHP.
 */
class DoubleVisitPlanService
{
    public function getReport(string $from, string $to): Collection
    {
        $rms = $this->getRms();
        $factRows = $this->getFactRows($from, $to);
        $months = $this->monthsBetween($from, $to);

        $rmTerritoryIds = $rms->pluck('territory_id');

        $children = $rmTerritoryIds->isEmpty() ? collect() : DB::table('territories')
            ->whereIn('parent_territory_id', $rmTerritoryIds)
            ->where('role', 'Rep')
            ->get(['id', 'parent_territory_id']);
        $childrenByRm = $children->groupBy('parent_territory_id');
        $allChildIds = $children->pluck('id');

        $assignments = $allChildIds->isEmpty() ? collect() : DB::table('employee_territory')
            ->whereIn('territory_id', $allChildIds)
            ->get(['territory_id', 'employee_id', 'assigned_at', 'unassigned_at']);
        $assignmentsByTerritory = $assignments->groupBy('territory_id');

        $employeeIds = $assignments->pluck('employee_id')->unique();
        $employees = $employeeIds->isEmpty() ? collect() : DB::table('employees')
            ->whereIn('id', $employeeIds)
            ->get(['id', 'full_name'])
            ->keyBy('id');

        $events = $employeeIds->isEmpty() ? collect() : DB::table('employee_events')
            ->whereIn('employee_id', $employeeIds)
            ->orderBy('event_date')
            ->orderBy('id')
            ->get(['employee_id', 'event_type', 'event_date']);
        $eventsByEmployee = $events->groupBy('employee_id');

        return $rms->map(function ($rm) use ($factRows, $months, $childrenByRm, $assignmentsByTerritory, $eventsByEmployee, $employees) {
            $childIds = ($childrenByRm[$rm->territory_id] ?? collect())->pluck('id');

            // Строки факта, где employee (РМ в CRM) сопоставляется с этим РМ
            $rmFactRows = $factRows->filter(fn($row) => $this->namesMatch($rm->full_name, $row->employee));

            // Все сотрудники, когда-либо числившиеся за территориями этого РМ — кандидаты в "медпреды РМ"
            $candidateEmployeeIds = collect();
            foreach ($childIds as $tid) {
                foreach ($assignmentsByTerritory[$tid] ?? [] as $a) {
                    $candidateEmployeeIds->push($a->employee_id);
                }
            }
            $candidateEmployeeIds = $candidateEmployeeIds->unique();

            // Тир каждого кандидата в каждом месяце: 'excluded' (план 0 — не активен
            // или нанят 25-го числа и позже), 'partial' (план фикс. 1 — нанят с 11 по
            // 24), 'full' (план = норма месяца — уже активен весь месяц, или нанят
            // до 10-го). Норма месяца сама зависит от того, сколько кандидатов НЕ
            // 'excluded' в этом месяце — считается ПОСЛЕ отсечения "excluded".
            $tiersByEmployeeAndMonth = [];
            $monthNorms = [];
            foreach ($months as $m) {
                $nonExcludedCount = 0;
                foreach ($candidateEmployeeIds as $empId) {
                    $tier = $this->employeeMonthTier($empId, $childIds, $m['start'], $m['end'], $assignmentsByTerritory, $eventsByEmployee);
                    $tiersByEmployeeAndMonth[$empId][$m['label']] = $tier;
                    if ($tier !== 'excluded') {
                        $nonExcludedCount++;
                    }
                }
                $monthNorms[$m['label']] = $nonExcludedCount <= 7 ? 2 : 1;
            }

            $reps = $candidateEmployeeIds->map(function ($empId) use ($months, $employees, $rmFactRows, $tiersByEmployeeAndMonth, $monthNorms, $eventsByEmployee) {
                $repName = $employees[$empId]->full_name ?? "#{$empId}";

                $plan = 0;
                foreach ($months as $m) {
                    $tier = $tiersByEmployeeAndMonth[$empId][$m['label']];
                    $plan += match ($tier) {
                        'excluded' => 0,
                        'partial'  => 1,
                        'full'     => $monthNorms[$m['label']],
                    };
                }

                $fact = $rmFactRows
                    ->filter(fn($row) => $this->namesMatch($repName, $row->double_visit_employee))
                    ->pluck('double_visit_Date')
                    ->unique()
                    ->count();

                $status = $this->employeeCurrentStatus($eventsByEmployee[$empId] ?? collect());

                return (object) [
                    'employee_id' => $empId,
                    'rep_name'    => $repName,
                    'fact'        => $fact,
                    'plan'        => $plan,
                    'kpi_percent' => $plan > 0 ? round($fact / $plan * 100, 1) : ($fact > 0 ? null : 0.0),
                    'status'      => $status,
                ];
            })
            ->filter(fn($r) => $r->plan > 0 || $r->fact > 0) // без медпредов, у которых и план, и факт — 0 (никогда не были активны в периоде)
            ->sortByDesc('plan')
            ->values();

            // Перевыполнение одного медпреда не должно маскировать недовыполнение
            // другого: в общий факт РМ идёт не более 100% от личного плана каждого
            // медпреда (min(факт, план)), визиты сверх плана в сумму не попадают.
            $totalPlan = $reps->sum('plan');
            $totalFact = $reps->sum(fn($r) => min($r->fact, $r->plan));

            return (object) [
                'rm_id'       => $rm->employee_id,
                'rm_name'     => $rm->full_name,
                'territory'   => $rm->territory_name,
                'fact'        => $totalFact,
                'plan'        => $totalPlan,
                'kpi_percent' => $totalPlan > 0 ? round($totalFact / $totalPlan * 100, 1) : ($totalFact > 0 ? null : 0.0),
                'reps'        => $reps,
            ];
        })->sortByDesc(fn($r) => $r->kpi_percent ?? -1)->values();
    }

    /** Все территории с ролью RM и их текущим сотрудником. */
    private function getRms(): Collection
    {
        return DB::table('territories as t')
            ->join('employees as e', 'e.id', '=', 't.employee_id')
            ->where('t.role', 'RM')
            ->select('t.id as territory_id', 't.territory_name', 'e.id as employee_id', 'e.full_name')
            ->get();
    }

    /**
     * Сырые строки факта за период (без агрегации — агрегируем позже, уже после
     * сопоставления и с РМ, и с конкретным медпредом).
     */
    private function getFactRows(string $from, string $to): Collection
    {
        // Статус и дата — именно double_visit_*, а не appointment_* (это статус/дата
        // обычного визита к врачу, который может отличаться от статуса самого
        // двойного визита: визит к врачу мог состояться, а двойной визит — нет, и
        // наоборот. Проверено на реальных данных — расхождение в ~20% строк.
        return DB::connection('nobel')->table('qs_double_calls')
            ->where('appointment_type', 'Двойной визит')
            ->where('double_visit_status', 'Выполнено')
            ->where('employee_position', 'Региональный менеджер')
            ->whereBetween('double_visit_Date', [$from, $to])
            ->select('employee', 'double_visit_employee', 'double_visit_Date')
            ->get();
    }

    /**
     * Сопоставляет ФИО из основной БД ("Изенова Айгуль Бекбергеновна") с записью
     * в Nobel CRM, где имя часто без отчества ("Изенова Айгуль ") — по фамилии И
     * имени, без учёта порядка/отчества (тот же принцип, что и для сопоставления KMP).
     */
    private function namesMatch(string $mainDbName, ?string $nobelName): bool
    {
        if (!$nobelName) {
            return false;
        }

        $parts = preg_split('/\s+/u', mb_strtolower(trim($mainDbName)));
        $surname = $parts[0] ?? '';
        $firstName = $parts[1] ?? '';
        $nobelLower = mb_strtolower(trim($nobelName));

        return str_contains($nobelLower, $surname) && ($firstName === '' || str_contains($nobelLower, $firstName));
    }

    /**
     * Тир сотрудника $employeeId за месяц [$monthStart, $monthEnd] — определяет,
     * как считать его план ПОСЛЕ того, как норма месяца (2 или 1) уже известна:
     *
     *   'excluded' — план 0. Не числится ни за одной из территорий $childTerritoryIds
     *                в этом месяце, ИЛИ его последнее событие на конец месяца — не
     *                hired/return_from_leave, ИЛИ принят на работу 25-го числа и позже.
     *   'partial'  — план фиксированно 1 (не зависит от нормы месяца). Принят на
     *                работу (event_type='hired') именно в этом месяце, с 11 по 24 число.
     *   'full'     — план = норме месяца (2 или 1 — см. ниже). Уже был активен до
     *                начала месяца, либо вышел из декрета, либо принят до 10 числа
     *                включительно.
     *
     * Норма месяца считается ОТДЕЛЬНО, в getReport(): 2 визита на сотрудника, если
     * у РМ в этом месяце ≤7 НЕисключённых (не 'excluded') сотрудников, иначе 1 —
     * подсчёт числа сотрудников для выбора нормы делается уже ПОСЛЕ отсечения
     * 'excluded' (принятых 25-го и позже), как и требует бизнес-правило.
     */
    private function employeeMonthTier(
        int $employeeId,
        Collection $childTerritoryIds,
        string $monthStart,
        string $monthEnd,
        Collection $assignmentsByTerritory,
        Collection $eventsByEmployee
    ): string {
        $monthStartTs = strtotime($monthStart);
        $monthEndTs   = strtotime($monthEnd);

        $assignedThisMonth = false;
        foreach ($childTerritoryIds as $territoryId) {
            foreach ($assignmentsByTerritory[$territoryId] ?? [] as $a) {
                if ($a->employee_id != $employeeId) {
                    continue;
                }
                $assignedTs   = strtotime($a->assigned_at);
                $unassignedTs = $a->unassigned_at ? strtotime($a->unassigned_at) : null;

                if ($assignedTs <= $monthEndTs && ($unassignedTs === null || $unassignedTs >= $monthStartTs)) {
                    $assignedThisMonth = true;
                    break 2;
                }
            }
        }

        if (!$assignedThisMonth) {
            return 'excluded';
        }

        $latest = null;
        $hiredThisMonth = null;
        foreach ($eventsByEmployee[$employeeId] ?? [] as $event) {
            if ($event->event_date <= $monthEnd) {
                $latest = $event; // события отсортированы по возрастанию даты — последнее подходящее и есть актуальное
            }
            if ($event->event_type === 'hired' && $event->event_date >= $monthStart && $event->event_date <= $monthEnd) {
                if ($hiredThisMonth === null || $event->event_date < $hiredThisMonth->event_date) {
                    $hiredThisMonth = $event;
                }
            }
        }

        if (!$latest || !in_array($latest->event_type, ['hired', 'return_from_leave'], true)) {
            return 'excluded';
        }

        if ($hiredThisMonth) {
            $day = (int) date('j', strtotime($hiredThisMonth->event_date));
            if ($day >= 25) {
                return 'excluded';
            }
            if ($day >= 11) {
                return 'partial';
            }
            return 'full'; // день <= 10
        }

        return 'full';
    }

    /**
     * Текущий кадровый статус сотрудника (НЕ привязан к границам отчётного периода —
     * это реальный статус на сегодня, для подсказки при наведении на медпреда):
     * дата последнего приёма/возврата из декрета, и если сейчас не работает —
     * дата и тип последнего события (увольнение/декрет).
     */
    private function employeeCurrentStatus(Collection $events): object
    {
        $lastHiredOrReturn = null;
        $latest = null;
        foreach ($events as $event) {
            $latest = $event; // события отсортированы по возрастанию даты — последний элемент и есть самый свежий
            if (in_array($event->event_type, ['hired', 'return_from_leave'], true)) {
                $lastHiredOrReturn = $event;
            }
        }

        $isActive = $latest && in_array($latest->event_type, ['hired', 'return_from_leave'], true);

        return (object) [
            'is_active'       => $isActive,
            'last_hired_date' => $lastHiredOrReturn->event_date ?? null,
            'inactive_since'  => $isActive ? null : ($latest->event_date ?? null),
            'inactive_type'   => $isActive ? null : ($latest->event_type ?? null),
        ];
    }

    /** Разбивает период на календарные месяцы (для динамического плана по месяцам). */
    private function monthsBetween(string $from, string $to): array
    {
        $months = [];
        $cursor = Carbon::parse($from)->startOfMonth();
        $end = Carbon::parse($to)->endOfMonth();

        while ($cursor->lte($end)) {
            $months[] = [
                'label' => $cursor->translatedFormat('F Y'),
                'start' => $cursor->toDateString(),
                'end'   => $cursor->copy()->endOfMonth()->toDateString(),
            ];
            $cursor->addMonth();
        }

        return $months;
    }
}
