<?php

namespace App\Http\Controllers;

use App\Notifications\EmployeeExportEmailNotification;
use App\Services\EmployeeEventStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Числа для карточек дашборда (верхние 6 + Стаж/Текучесть + «За период»).
     * Общий метод для первого (SSR) рендера и для AJAX-обновления при смене
     * фильтра по должности или периода — чтобы не дублировать расчёты.
     *
     * $roles — пустой массив = без фильтра (как сейчас). $periodFrom/$periodTo —
     * если оба заданы, верхние «в этом месяце»/«в этом году» карточки
     * заменяются значениями за выбранный период (см. решение по UX ниже).
     */
    private function computeCardMetrics(EmployeeEventStatsService $stats, array $roles, ?string $periodFrom, ?string $periodTo): array
    {
        $now       = now();
        $latestEventSub = 'ev.id = (SELECT ee.id FROM employee_events ee WHERE ee.employee_id = e.id ORDER BY ee.event_date DESC, ee.id DESC LIMIT 1)';

        // Считаем hired/dismissed парой в одном запросе (GROUP BY event_type)
        // вместо запроса на каждый тип — основная БД у нас на удалённом
        // сервере, и AJAX-обновление карточек дёргает эти запросы на каждый
        // клик по фильтру, так что каждый лишний round-trip заметно тормозит.
        $monthCounts = $stats->countsByMonth(['hired', 'dismissed'], $now->month, $now->year, $roles);
        $yearCounts  = $stats->countsByYear(['hired', 'dismissed'], $now->year, $roles);

        $hiredThisMonth = $monthCounts['hired'];
        $firedThisMonth = $monthCounts['dismissed'];
        $hiredThisYear  = $yearCounts['hired'];
        $firedThisYear  = $yearCounts['dismissed'];
        // Текучесть всегда считаем по календарному году (не подменяется
        // периодом ниже) — сохраняем значение до возможной подмены.
        $firedThisYearForTurnover = $yearCounts['dismissed'];

        $hasPeriod = (bool) ($periodFrom && $periodTo);
        $periodStats = null;
        if ($hasPeriod) {
            $periodStats = $stats->countsByDateRange(['hired', 'dismissed', 'maternity_leave'], $periodFrom, $periodTo, $roles);

            // Решение пользователя: пока выбран период, верхние «в этом
            // месяце»/«в этом году» карточки показывают этот период, а не
            // свои обычные фиксированные окна.
            $hiredThisMonth = $periodStats['hired'];
            $firedThisMonth = $periodStats['dismissed'];
            $hiredThisYear  = $periodStats['hired'];
            $firedThisYear  = $periodStats['dismissed'];
        }

        $latestCounts = $stats->countsWithLatestEvent(['hired', 'return_from_leave', 'maternity_leave'], $roles);
        $totalActive  = $latestCounts['hired'] + $latestCounts['return_from_leave'];
        $onLeave      = $latestCounts['maternity_leave'];
        $turnoverPct  = $totalActive > 0 ? round($firedThisYearForTurnover / $totalActive * 100, 1) : 0;

        $avgDaysQuery = DB::table('employees as e')
            ->join('employee_events as ev', function ($j) use ($latestEventSub) {
                $j->on('ev.employee_id', '=', 'e.id')->whereRaw($latestEventSub);
            })
            ->whereIn('ev.event_type', ['hired', 'return_from_leave'])
            ->whereNotNull('e.hiring_date');
        if (!empty($roles)) {
            $avgDaysQuery->whereRaw('(
                SELECT t.role
                FROM employee_territory et
                JOIN territories t ON t.id = et.territory_id
                WHERE et.employee_id = e.id
                ORDER BY et.assigned_at DESC, et.id DESC
                LIMIT 1
            ) IN (' . implode(',', array_fill(0, count($roles), '?')) . ')', $roles);
        }
        $avgDays = $avgDaysQuery->selectRaw('AVG(DATEDIFF(NOW(), e.hiring_date)) as avg_days')->value('avg_days');

        return [
            'hired_total'        => $totalActive,
            'on_maternity_leave' => $onLeave,
            'hired_this_month'   => $hiredThisMonth,
            'fired_this_month'   => $firedThisMonth,
            'hired_this_year'    => $hiredThisYear,
            'fired_this_year'    => $firedThisYear,
            'avg_tenure_years'   => $avgDays ? intval($avgDays / 365) : 0,
            'avg_tenure_months'  => $avgDays ? intval(($avgDays % 365) / 30) : 0,
            'turnover_pct'       => $turnoverPct,
            'has_period'         => $hasPeriod,
            'period_stats'       => $periodStats,
        ];
    }

    /**
     * AJAX-эндпоинт: пересчитывает все карточки дашборда без перезагрузки
     * страницы — при смене фильтра по должности и/или периода «За период».
     */
    public function cardsData(Request $request, EmployeeEventStatsService $stats)
    {
        $roles      = array_filter((array) $request->input('roles', []));
        $periodFrom = $request->input('date_from');
        $periodTo   = $request->input('date_to');

        return response()->json($this->computeCardMetrics($stats, $roles, $periodFrom, $periodTo));
    }

    public function showDashboard(Request $request, EmployeeEventStatsService $stats)
    {
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

        // ── Карточки (верхние 6 + Стаж/Текучесть + «За период») ─────────
        // Общий расчёт с cardsData() — чтобы AJAX-обновление при смене
        // фильтра по должности/периода не расходилось с первым рендером.
        $periodFrom = $request->input('date_from');
        $periodTo   = $request->input('date_to');
        $cards      = $this->computeCardMetrics($stats, [], $periodFrom, $periodTo);

        return view('dashboard', [
            'periodFrom'  => $periodFrom,
            'periodTo'    => $periodTo,
            'hasPeriod'   => $cards['has_period'],
            'periodStats' => $cards['period_stats'],

            'hired_total'        => $cards['hired_total'],
            'on_maternity_leave' => $cards['on_maternity_leave'],
            'hired_this_month'   => $cards['hired_this_month'],
            'fired_this_month'   => $cards['fired_this_month'],
            'hired_this_year'    => $cards['hired_this_year'],
            'fired_this_year'    => $cards['fired_this_year'],

            'chartLabels'      => $chartLabels,
            'chartKeys'        => $chartKeys,
            'cumulativeByRole' => $cumulativeByRole,

            'allRoles'         => $allRoles,
            'barRoleData'      => $barRoleData,
            'donutRoleData'    => $donutRoleData,
            'cityRoleData'     => $cityRoleData,
            'hasCityData'      => $hasCityData,

            'avgTenureYears'   => $cards['avg_tenure_years'],
            'avgTenureMonths'  => $cards['avg_tenure_months'],
            'turnoverPct'      => $cards['turnover_pct'],
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
            'employees'  => $employeesCallback(),
            'title'      => $title,
            'exportUrl'  => route('employees.filtered.export', $type),
            'emailUrl'   => route('employees.filtered.email', $type),
        ]);
    }

    public function filteredListExport(string $type, EmployeeEventStatsService $stats)
    {
        $config = [
            'hired_total'        => [fn() => $stats->getWithLatestEvent(['hired', 'return_from_leave']), 'Активные сотрудники'],
            'fired_this_month'   => [fn() => $stats->getByMonth('dismissed', now()->month, now()->year),        'Уволенные в этом месяце'],
            'hired_this_month'   => [fn() => $stats->getByMonth('hired', now()->month, now()->year),            'Нанятые в этом месяце'],
            'on_maternity_leave' => [fn() => $stats->getWithLatestEvent('maternity_leave'),                     'В декрете'],
            'fired_last_month'   => [fn() => $stats->getByMonth('dismissed', now()->subMonth()->month, now()->subMonth()->year), 'Уволенные в прошлом месяце'],
            'hired_last_month'   => [fn() => $stats->getByMonth('hired', now()->subMonth()->month, now()->subMonth()->year),     'Нанятые в прошлом месяце'],
            'fired_this_year'    => [fn() => $stats->getByYear('dismissed', now()->year),                       'Уволенные в этом году'],
            'hired_this_year'    => [fn() => $stats->getByYear('hired', now()->year),                           'Нанятые в этом году'],
        ];

        if (!isset($config[$type])) {
            abort(404);
        }

        [$employeesCallback, $title] = $config[$type];

        return $this->exportEventsToExcel($stats, $employeesCallback(), $title);
    }

    public function filteredListEmail(string $type, EmployeeEventStatsService $stats)
    {
        $config = [
            'hired_total'        => [fn() => $stats->getWithLatestEvent(['hired', 'return_from_leave']), 'Активные сотрудники'],
            'fired_this_month'   => [fn() => $stats->getByMonth('dismissed', now()->month, now()->year),        'Уволенные в этом месяце'],
            'hired_this_month'   => [fn() => $stats->getByMonth('hired', now()->month, now()->year),            'Нанятые в этом месяце'],
            'on_maternity_leave' => [fn() => $stats->getWithLatestEvent('maternity_leave'),                     'В декрете'],
            'fired_last_month'   => [fn() => $stats->getByMonth('dismissed', now()->subMonth()->month, now()->subMonth()->year), 'Уволенные в прошлом месяце'],
            'hired_last_month'   => [fn() => $stats->getByMonth('hired', now()->subMonth()->month, now()->subMonth()->year),     'Нанятые в прошлом месяце'],
            'fired_this_year'    => [fn() => $stats->getByYear('dismissed', now()->year),                       'Уволенные в этом году'],
            'hired_this_year'    => [fn() => $stats->getByYear('hired', now()->year),                           'Нанятые в этом году'],
        ];

        if (!isset($config[$type])) {
            abort(404);
        }

        [$employeesCallback, $title] = $config[$type];

        return $this->emailEventsExcel($stats, $employeesCallback(), $title);
    }

    /**
     * Ключ типа события из URL -> [заголовок, event_type(ы) для запроса].
     * 'all' объединяет все три события в один список/файл (по запросу — чтобы
     * не скачивать 3 отдельных файла для рассылки коллегам).
     */
    private function periodTypeConfig(string $type): ?array
    {
        return [
            'hired'           => ['Принято', 'hired'],
            'dismissed'       => ['Уволено', 'dismissed'],
            'maternity_leave' => ['В декрете', 'maternity_leave'],
            'all'             => ['Все события (принятые, уволенные, в декрете)', ['hired', 'dismissed', 'maternity_leave']],
        ][$type] ?? null;
    }

    public function periodList(string $type, Request $request, EmployeeEventStatsService $stats)
    {
        $config = $this->periodTypeConfig($type);

        if (!$config) {
            abort(404);
        }
        [$label, $eventTypes] = $config;

        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        if (!$from || !$to) {
            abort(400, 'Не указан период');
        }

        $title = $label . ': '
            . \Carbon\Carbon::parse($from)->format('d.m.Y') . ' — '
            . \Carbon\Carbon::parse($to)->format('d.m.Y');

        return view('employees-filtered-list', [
            'employees' => $stats->getByDateRange($eventTypes, $from, $to),
            'title'     => $title,
            'exportUrl' => route('employees.periodList.export', ['type' => $type, 'date_from' => $from, 'date_to' => $to]),
            'emailUrl'  => route('employees.periodList.email', ['type' => $type, 'date_from' => $from, 'date_to' => $to]),
        ]);
    }

    public function periodListExport(string $type, Request $request, EmployeeEventStatsService $stats)
    {
        $config = $this->periodTypeConfig($type);

        if (!$config) {
            abort(404);
        }
        [$label, $eventTypes] = $config;

        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        if (!$from || !$to) {
            abort(400, 'Не указан период');
        }

        $title = $label . ' ' . $from . ' — ' . $to;

        return $this->exportEventsToExcel($stats, $stats->getByDateRange($eventTypes, $from, $to), $title);
    }

    public function periodListEmail(string $type, Request $request, EmployeeEventStatsService $stats)
    {
        $config = $this->periodTypeConfig($type);

        if (!$config) {
            abort(404);
        }
        [$label, $eventTypes] = $config;

        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        if (!$from || !$to) {
            abort(400, 'Не указан период');
        }

        $title = $label . ' ' . $from . ' — ' . $to;

        return $this->emailEventsExcel($stats, $stats->getByDateRange($eventTypes, $from, $to), $title);
    }

    /**
     * Экспорт списка сотрудников по событию (ФИО / Тип события / Дата) в Excel.
     * Используется и для пресетов дашборда, и для произвольного периода.
     * Сборка файла — в EmployeeEventStatsService::buildEventsExcelFile(),
     * она же переиспользуется в плановой email-рассылке (SendWeeklyDismissedReport).
     */
    private function exportEventsToExcel(EmployeeEventStatsService $stats, Collection $employees, string $title): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filePath = $stats->buildEventsExcelFile($employees, $title);

        return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
    }

    /**
     * Тот же Excel, что и exportEventsToExcel(), но отправляется письмом на почту
     * текущего пользователя, а не скачивается. Сбой отправки не должен ронять
     * запрос — файл уже собран, просто сообщаем об ошибке через флэш-сообщение.
     */
    private function emailEventsExcel(EmployeeEventStatsService $stats, Collection $employees, string $title)
    {
        $filePath = $stats->buildEventsExcelFile($employees, $title);

        try {
            auth()->user()->notify(new EmployeeExportEmailNotification($title, $employees->count(), $filePath));
            $result = back()->with('success', 'Файл отправлен на почту');
        } catch (\Exception $e) {
            Log::error('Export email send failed', ['title' => $title, 'error' => $e->getMessage()]);
            $result = back()->with('error', 'Не удалось отправить письмо. Попробуйте позже.');
        } finally {
            @unlink($filePath);
        }

        return $result;
    }
}
