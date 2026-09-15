<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Nobel\Call;
use App\Support\Etl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public const FREQUENCY    = 2;
    public const DAILY_TARGET = 18;

    public function index(Request $request)
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));

        return view('leaderboard', [
            'month'       => $month,
            'initialData' => $this->loadMonth($month),
        ]);
    }

    /**
     * JSON-эндпоинт смены месяца без перезагрузки страницы — тот же паттерн,
     * что на «Визитах»/«Таргетных клиентах»/«Двойных визитах». Сортировка
     * таблицы больше не серверный параметр — $rows уже плоский массив,
     * сортируется целиком на клиенте после одной загрузки месяца.
     */
    public function data(Request $request)
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));

        return response()->json($this->loadMonth($month), 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function loadMonth(string $month): array
    {
        [$dateFrom, $dateTo] = $this->monthBounds($month);

        // Раньше каждое открытие страницы било живым запросом в Nobel CRM (плюс
        // ещё 2 запроса к stg_*-таблицам) — как и везде в этом разделе, кэшируем
        // до следующего ночного ETL.
        return Cache::remember("leaderboard_{$month}", Etl::secondsUntilNextRun(), function () use ($dateFrom, $dateTo) {
            [$crmStats, $stgDoctors, $stgPharmacies] = $this->fetchStats($dateFrom, $dateTo);
            $workingDays = $this->workingDays($dateFrom, $dateTo);
            $callTarget  = $workingDays * self::DAILY_TARGET;
            $rows        = $this->buildRows($crmStats, $stgDoctors, $stgPharmacies, $callTarget);

            return [
                'rows'        => $rows,
                'workingDays' => $workingDays,
                'callTarget'  => $callTarget,
            ];
        });
    }

    public function export(Request $request)
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));
        $data  = $this->loadMonth($month);

        $rows        = collect($data['rows'])->sortByDesc('total_visits')->values();
        $workingDays = $data['workingDays'];
        $callTarget  = $data['callTarget'];

        $fileName = 'leaderboard_' . $month . '.csv';

        return response()->streamDownload(function () use ($rows, $workingDays, $callTarget) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                '#', 'Сотрудник', 'Должность',
                'Всего визитов', 'Таргет (' . $workingDays . '×' . self::DAILY_TARGET . ')', 'Реализация %',
                'База врачей', 'Таргет частоты (врачи)', 'Факт визитов (врачи)', '% частоты (врачи)',
                'База аптек', 'Таргет частоты (аптеки)', 'Факт визитов (аптеки)', '% частоты (аптеки)',
                'Ср. длит. мин',
            ], ';');
            foreach ($rows as $i => $r) {
                fputcsv($out, [
                    $i + 1,
                    $r['name'],
                    $r['position'],
                    $r['total_visits'],
                    $callTarget,
                    $r['call_pct'],
                    $r['base_doctors'],
                    $r['freq_target_doc'],
                    $r['doctor_visits'],
                    $r['freq_pct_doc'],
                    $r['base_pharmacies'],
                    $r['freq_target_phar'],
                    $r['pharmacy_visits'],
                    $r['freq_pct_phar'],
                    $r['avg_duration'],
                ], ';');
            }
            fclose($out);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function fetchStats(string $dateFrom, string $dateTo): array
    {
        $crmStats      = collect();
        $stgDoctors    = collect();
        $stgPharmacies = collect();

        try {
            $crmStats = Call::whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку'])
                ->where('appointment_status', 'Выполнено')
                ->where('appointment_Date', '>=', $dateFrom)
                ->where('appointment_Date', '<=', $dateTo)
                ->selectRaw('
                    employee_id,
                    MAX(employee) as employee_name,
                    COUNT(*) as total_visits,
                    SUM(appointment_type = "Визит к врачу") as doctor_visits,
                    SUM(appointment_type = "Визит в аптеку") as pharmacy_visits,
                    ROUND(AVG(CASE WHEN appointment_duration > 0 THEN appointment_duration END)) as avg_duration
                ')
                ->groupBy('employee_id')->get()->keyBy('employee_id');

            // Базы клиентов из stg-таблиц (без фильтра по датам — это назначенная база)
            $stgDoctors = DB::connection('nobel')
                ->table('stg_nobel_report_2')
                ->selectRaw('TRIM(employee) as employee, COUNT(DISTINCT customer_id) as base_count')
                ->groupBy('employee')
                ->get()
                ->keyBy(fn($r) => trim($r->employee ?? ''));

            $stgPharmacies = DB::connection('nobel')
                ->table('stg_nobel_report_1')
                ->where('organization_type', 'Аптечные учреждения')
                ->selectRaw('TRIM(employee) as employee, COUNT(DISTINCT organization_id) as base_count')
                ->groupBy('employee')
                ->get()
                ->keyBy(fn($r) => trim($r->employee ?? ''));

        } catch (\Exception) {}

        return [$crmStats, $stgDoctors, $stgPharmacies];
    }

    private function buildRows(
        \Illuminate\Support\Collection $crmStats,
        \Illuminate\Support\Collection $stgDoctors,
        \Illuminate\Support\Collection $stgPharmacies,
        int $callTarget
    ): array {
        return Employee::whereHas('crmIds')
            ->with('crmIds')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'position'])
            ->map(function ($emp) use ($crmStats, $stgDoctors, $stgPharmacies, $callTarget) {
                // Сотрудник может иметь несколько CRM-аккаунтов (повторный найм) —
                // суммируем метрики по всем его id вместо единственного lookup.
                $crmRows        = $emp->crmIds->map(fn($c) => $crmStats->get($c->crm_employee_id))->filter();
                $totalVisits    = (int) $crmRows->sum('total_visits');
                $doctorVisits   = (int) $crmRows->sum('doctor_visits');
                $pharmacyVisits = (int) $crmRows->sum('pharmacy_visits');
                $avgDuration    = $totalVisits > 0
                    ? (int) round($crmRows->sum(fn($r) => (int)$r->avg_duration * (int)$r->total_visits) / $totalVisits)
                    : 0;

                // Разные CRM-аккаунты одного человека (напр. после смены написания
                // имени при повторном найме) могут звучать в stg-таблицах баз по-разному —
                // суммируем базу врачей/аптек по всем встречавшимся именам.
                $empNames       = $crmRows->pluck('employee_name')->map(fn($n) => trim($n ?? ''))->filter()->unique();
                $baseDoctors    = (int) $empNames->sum(fn($n) => $stgDoctors->get($n)?->base_count ?? 0);
                $basePharmacies = (int) $empNames->sum(fn($n) => $stgPharmacies->get($n)?->base_count ?? 0);
                $freqTargetDoc  = $baseDoctors    * self::FREQUENCY;
                $freqTargetPhar = $basePharmacies * self::FREQUENCY;

                return [
                    'id'              => $emp->id,
                    'name'            => $emp->full_name,
                    'position'        => $emp->position ?? '',
                    'total_visits'    => $totalVisits,
                    'call_pct'        => $callTarget > 0 ? round($totalVisits    / $callTarget  * 100) : 0,
                    'doctor_visits'   => $doctorVisits,
                    'pharmacy_visits' => $pharmacyVisits,
                    'avg_duration'    => $avgDuration,
                    'base_doctors'    => $baseDoctors,
                    'base_pharmacies' => $basePharmacies,
                    'freq_target_doc' => $freqTargetDoc,
                    'freq_target_phar'=> $freqTargetPhar,
                    'freq_pct_doc'    => $freqTargetDoc  > 0 ? round($doctorVisits   / $freqTargetDoc  * 100) : 0,
                    'freq_pct_phar'   => $freqTargetPhar > 0 ? round($pharmacyVisits / $freqTargetPhar * 100) : 0,
                ];
            })
            ->filter(fn($r) => $r['total_visits'] > 0)
            ->values()
            ->all();
    }

    private function workingDays(string $dateFrom, string $dateTo): int
    {
        $start = Carbon::parse($dateFrom);
        $end   = Carbon::parse($dateTo);
        $days  = 0;
        for ($d = $start->copy()->startOfDay(); $d->lte($end); $d->addDay()) {
            if ($d->isWeekday()) {
                $days++;
            }
        }
        return $days;
    }

    /** @return array{0: string, 1: string} [monthStart, monthEnd] в формате Y-m-d */
    private function monthBounds(string $month): array
    {
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        return [$date->toDateString(), $date->copy()->endOfMonth()->toDateString()];
    }
}
