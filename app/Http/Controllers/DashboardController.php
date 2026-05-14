<?php

namespace App\Http\Controllers;

use App\Services\EmployeeEventStatsService;

class DashboardController extends Controller
{
    public function showDashboard(EmployeeEventStatsService $stats)
    {
        $now       = now();
        $lastMonth = now()->subMonth();

        return view('dashboard', [
            'hired_total'        => $stats->countWithLatestEvent(['hired', 'return_from_leave']),
            'fired_this_month'   => $stats->countByMonth('dismissed', $now->month, $now->year),
            'hired_this_month'   => $stats->countByMonth('hired', $now->month, $now->year),
            'on_maternity_leave' => $stats->countWithLatestEvent('maternity_leave'),
            'fired_last_month'   => $stats->countByMonth('dismissed', $lastMonth->month, $lastMonth->year),
            'hired_last_month'   => $stats->countByMonth('hired', $lastMonth->month, $lastMonth->year),
            'fired_this_year'    => $stats->countByYear('dismissed', $now->year),
            'hired_this_year'    => $stats->countByYear('hired', $now->year),
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
