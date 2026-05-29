<?php

namespace App\Http\Controllers;

use App\Services\EmployeeEventStatsService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function showDashboard(EmployeeEventStatsService $stats)
    {
        $now       = now();
        $lastMonth = now()->subMonth();
        $since     = now()->subMonths(11)->startOfMonth();

        // Переиспользуемые подзапросы
        $latestTerrSub  = 'et.assigned_at = (SELECT MAX(et2.assigned_at) FROM employee_territory et2 WHERE et2.employee_id = e.id)';
        $latestEventSub = 'ev.id = (SELECT ee.id FROM employee_events ee WHERE ee.employee_id = e.id ORDER BY ee.event_date DESC, ee.id DESC LIMIT 1)';

        // ── Метки и ключи для 12 месяцев ────────────────────────────────
        $ruMonths       = ['','Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
        $chartLabels    = [];
        $chartKeys      = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartLabels[] = $ruMonths[$date->month] . ' ' . $date->year;
            $chartKeys[]   = $date->year . '-' . $date->month;
        }

        // ── Бар-чарт: найм / увольнения по месяцам (разбивка по роли) ──
        $barEventRows = DB::table('employee_events as ev')
            ->join('employees as e', 'e.id', '=', 'ev.employee_id')
            ->leftJoin('employee_territory as et', function ($j) use ($latestTerrSub) {
                $j->on('et.employee_id', '=', 'e.id')->whereRaw($latestTerrSub);
            })
            ->leftJoin('territories as t', 't.id', '=', 'et.territory_id')
            ->where('ev.event_date', '>=', $since)
            ->whereIn('ev.event_type', ['hired', 'dismissed'])
            ->selectRaw("YEAR(ev.event_date) as y, MONTH(ev.event_date) as m,
                         ev.event_type,
                         COALESCE(NULLIF(t.role,''), '—') as role,
                         COUNT(*) as cnt")
            ->groupBy('y', 'm', 'ev.event_type', 'role')
            ->get();

        // { "REP": { "hired": {"2025-6": 2}, "dismissed": {...} }, ... }
        $barRoleData = [];
        foreach ($barEventRows as $row) {
            $key = $row->y . '-' . $row->m;
            $barRoleData[$row->role][$row->event_type][$key] = $row->cnt;
        }

        // ── Donut: активные сотрудники по ролям (без уволенных) ─────────
        $donutRows = DB::table('employees as e')
            ->join('employee_events as ev', function ($j) use ($latestEventSub) {
                $j->on('ev.employee_id', '=', 'e.id')->whereRaw($latestEventSub);
            })
            ->leftJoin('employee_territory as et', function ($j) use ($latestTerrSub) {
                $j->on('et.employee_id', '=', 'e.id')->whereRaw($latestTerrSub);
            })
            ->leftJoin('territories as t', 't.id', '=', 'et.territory_id')
            ->whereIn('ev.event_type', ['hired', 'return_from_leave'])
            ->selectRaw("COALESCE(NULLIF(t.role,''), '—') as role, COUNT(*) as cnt")
            ->groupBy('role')
            ->orderByDesc('cnt')
            ->get();

        $donutRoleData = $donutRows->pluck('cnt', 'role')->toArray();
        $allRoles      = $donutRows->pluck('role')->toArray();

        // ── Накопительный рост по ролям ─────────────────────────────────
        $latestTerrSubEv = 'et.assigned_at = (SELECT MAX(et2.assigned_at) FROM employee_territory et2 WHERE et2.employee_id = ev.employee_id)';
        $cumulativeByRole = array_fill_keys($allRoles, []);
        for ($i = 11; $i >= 0; $i--) {
            $endOfMonth = now()->subMonths($i)->endOfMonth()->format('Y-m-d 23:59:59');
            $rows = DB::table('employee_events as ev')
                ->leftJoin('employee_territory as et', function ($j) use ($latestTerrSubEv) {
                    $j->on('et.employee_id', '=', 'ev.employee_id')->whereRaw($latestTerrSubEv);
                })
                ->leftJoin('territories as t', 't.id', '=', 'et.territory_id')
                ->whereRaw('ev.id = (SELECT ee.id FROM employee_events ee WHERE ee.employee_id = ev.employee_id AND ee.event_date <= ? ORDER BY ee.event_date DESC, ee.id DESC LIMIT 1)', [$endOfMonth])
                ->whereIn('ev.event_type', ['hired', 'return_from_leave'])
                ->selectRaw("COALESCE(NULLIF(t.role,''), '—') as role, COUNT(*) as cnt")
                ->groupBy('role')
                ->pluck('cnt', 'role')
                ->toArray();
            foreach ($allRoles as $role) {
                $cumulativeByRole[$role][] = (int) ($rows[$role] ?? 0);
            }
        }

        // ── Города + роль ────────────────────────────────────────────────
        $cityRoleRows = DB::table('employees as e')
            ->join('employee_territory as et', function ($j) use ($latestTerrSub) {
                $j->on('et.employee_id', '=', 'e.id')->whereRaw($latestTerrSub);
            })
            ->join('territories as t', 't.id', '=', 'et.territory_id')
            ->join('employee_events as ev', function ($j) use ($latestEventSub) {
                $j->on('ev.employee_id', '=', 'e.id')->whereRaw($latestEventSub);
            })
            ->whereIn('ev.event_type', ['hired', 'return_from_leave'])
            ->whereNotNull('t.city')->where('t.city', '!=', '')
            ->selectRaw("t.city, COALESCE(NULLIF(t.role,''), '—') as role, COUNT(*) as cnt")
            ->groupBy('t.city', 'role')
            ->get();

        $cityRoleData = [];
        foreach ($cityRoleRows as $row) {
            $cityRoleData[$row->city][$row->role] = $row->cnt;
        }
        $hasCityData = !empty($cityRoleData);

        // ── Средний стаж ─────────────────────────────────────────────────
        $avgDays = DB::table('employees as e')
            ->join('employee_events as ev', function ($j) use ($latestEventSub) {
                $j->on('ev.employee_id', '=', 'e.id')->whereRaw($latestEventSub);
            })
            ->whereIn('ev.event_type', ['hired', 'return_from_leave'])
            ->whereNotNull('e.hiring_date')
            ->selectRaw('AVG(DATEDIFF(NOW(), e.hiring_date)) as avg_days')
            ->value('avg_days');

        $avgTenureYears  = $avgDays ? intval($avgDays / 365) : 0;
        $avgTenureMonths = $avgDays ? intval(($avgDays % 365) / 30) : 0;

        // ── Текучесть ─────────────────────────────────────────────────────
        $totalActive   = $stats->countWithLatestEvent(['hired', 'return_from_leave']);
        $firedThisYear = $stats->countByYear('dismissed', $now->year);
        $turnoverPct   = $totalActive > 0 ? round($firedThisYear / $totalActive * 100, 1) : 0;

        return view('dashboard', [
            'hired_total'        => $totalActive,
            'fired_this_month'   => $stats->countByMonth('dismissed', $now->month, $now->year),
            'hired_this_month'   => $stats->countByMonth('hired', $now->month, $now->year),
            'on_maternity_leave' => $stats->countWithLatestEvent('maternity_leave'),
            'fired_last_month'   => $stats->countByMonth('dismissed', $lastMonth->month, $lastMonth->year),
            'hired_last_month'   => $stats->countByMonth('hired', $lastMonth->month, $lastMonth->year),
            'fired_this_year'    => $firedThisYear,
            'hired_this_year'    => $stats->countByYear('hired', $now->year),

            'chartLabels'      => $chartLabels,
            'chartKeys'        => $chartKeys,
            'cumulativeByRole' => $cumulativeByRole,

            'allRoles'         => $allRoles,
            'barRoleData'      => $barRoleData,
            'donutRoleData'    => $donutRoleData,
            'cityRoleData'     => $cityRoleData,
            'hasCityData'      => $hasCityData,

            'avgTenureYears'   => $avgTenureYears,
            'avgTenureMonths'  => $avgTenureMonths,
            'turnoverPct'      => $turnoverPct,
        ]);
    }

    public function filteredList(string $type, EmployeeEventStatsService $stats)
    {
        $now       = now();
        $lastMonth = now()->subMonth();

        $config = [
            'hired_total'        => [fn() => $stats->getWithLatestEvent(['hired', 'return_from_leave']), 'Активные сотрудники'],
            'fired_this_month'   => [fn() => $stats->getByMonth('dismissed', $now->month, $now->year),        'Уволенные в этом месяце'],
            'hired_this_month'   => [fn() => $stats->getByMonth('hired', $now->month, $now->year),            'Нанятые в этом месяце'],
            'on_maternity_leave' => [fn() => $stats->getWithLatestEvent('maternity_leave'),                   'В декрете'],
            'fired_last_month'   => [fn() => $stats->getByMonth('dismissed', $lastMonth->month, $lastMonth->year), 'Уволенные в прошлом месяце'],
            'hired_last_month'   => [fn() => $stats->getByMonth('hired', $lastMonth->month, $lastMonth->year),     'Нанятые в прошлом месяце'],
            'fired_this_year'    => [fn() => $stats->getByYear('dismissed', $now->year),                     'Уволенные в этом году'],
            'hired_this_year'    => [fn() => $stats->getByYear('hired', $now->year),                         'Нанятые в этом году'],
        ];

        if (!isset($config[$type])) {
            abort(404);
        }

        [$employeesCallback, $title] = $config[$type];

        return view('employees-filtered-list', [
            'employees' => $employeesCallback(),
            'title'     => $title,
        ]);
    }
}
