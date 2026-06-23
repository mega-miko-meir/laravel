<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Nobel\Call;
use App\Models\Nobel\Kmp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $year     = $request->input('year', (string) date('Y'));
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        [$crmStats, $kmpStats] = $this->fetchStats($year, $dateFrom, $dateTo);

        $rows = $this->buildRows($crmStats, $kmpStats);

        $allowedSorts = ['total_visits', 'doctor_visits', 'pharmacy_visits', 'avg_duration', 'total_amount', 'total_qty'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'total_visits';
        $dir  = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $rows = ($dir === 'desc' ? $rows->sortByDesc($sort) : $rows->sortBy($sort))->values();

        $years = Cache::remember('kmp_filter_years', 3600, fn() =>
            Kmp::distinct()->where('Статус заказа', 'Доставлено')
                ->whereNotNull('Год')->orderBy('Год', 'desc')->pluck('Год')
        );

        return view('leaderboard', compact('rows', 'years', 'year', 'dateFrom', 'dateTo', 'sort', 'dir'));
    }

    public function export(Request $request)
    {
        $year     = $request->input('year', (string) date('Y'));
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        [$crmStats, $kmpStats] = $this->fetchStats($year, $dateFrom, $dateTo);

        $rows = $this->buildRows($crmStats, $kmpStats)->sortByDesc('total_visits')->values();

        $fileName = 'leaderboard_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['#', 'Сотрудник', 'Должность', 'Всего визитов', 'К врачам', 'В аптеки', 'Ср. длит. мин', 'Сумма KZT', 'Кол-во уп.'], ';');
            foreach ($rows as $i => $r) {
                fputcsv($out, [
                    $i + 1,
                    $r['name'],
                    $r['position'],
                    $r['total_visits'],
                    $r['doctor_visits'],
                    $r['pharmacy_visits'],
                    $r['avg_duration'],
                    str_replace('.', ',', $r['total_amount']),
                    str_replace('.', ',', $r['total_qty']),
                ], ';');
            }
            fclose($out);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function fetchStats(string $year, ?string $dateFrom, ?string $dateTo): array
    {
        $crmStats = collect();
        $kmpStats = collect();

        try {
            $q = Call::whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку'])
                ->where('appointment_status', 'Выполнено')
                ->selectRaw('
                    employee_id,
                    COUNT(*) as total_visits,
                    SUM(appointment_type = "Визит к врачу") as doctor_visits,
                    SUM(appointment_type = "Визит в аптеку") as pharmacy_visits,
                    ROUND(AVG(CASE WHEN appointment_duration > 0 THEN appointment_duration END)) as avg_duration
                ');
            if ($dateFrom) $q->where('appointment_Date', '>=', $dateFrom);
            if ($dateTo)   $q->where('appointment_Date', '<=', $dateTo);
            $crmStats = $q->groupBy('employee_id')->get()->keyBy('employee_id');
        } catch (\Exception $e) {}

        try {
            $q = Kmp::where('Статус заказа', 'Доставлено')
                ->selectRaw('`Медпредставитель`, ROUND(SUM(`Amount_disc`)) as total_amount, ROUND(SUM(`Дост_колво`)) as total_qty');
            if ($year)    $q->where('Год', $year);
            if ($dateFrom) $q->where('Дата', '>=', $dateFrom);
            if ($dateTo)   $q->where('Дата', '<=', $dateTo);
            $kmpStats = $q->groupBy('Медпредставитель')->get()->keyBy('Медпредставитель');
        } catch (\Exception $e) {}

        return [$crmStats, $kmpStats];
    }

    private function buildRows($crmStats, $kmpStats): \Illuminate\Support\Collection
    {
        return Employee::where(function ($q) {
            $q->whereNotNull('crm_employee_id')->orWhereNotNull('kmp_employee_name');
        })->orderBy('full_name')
          ->get(['id', 'full_name', 'position', 'crm_employee_id', 'kmp_employee_name'])
          ->map(function ($emp) use ($crmStats, $kmpStats) {
              $crm = $crmStats->get($emp->crm_employee_id);
              $kmp = $kmpStats->get($emp->kmp_employee_name);
              return [
                  'id'              => $emp->id,
                  'name'            => $emp->full_name,
                  'position'        => $emp->position ?? '',
                  'total_visits'    => (int)($crm?->total_visits ?? 0),
                  'doctor_visits'   => (int)($crm?->doctor_visits ?? 0),
                  'pharmacy_visits' => (int)($crm?->pharmacy_visits ?? 0),
                  'avg_duration'    => (int)($crm?->avg_duration ?? 0),
                  'total_amount'    => (int)($kmp?->total_amount ?? 0),
                  'total_qty'       => (int)($kmp?->total_qty ?? 0),
              ];
          })
          ->filter(fn($r) => $r['total_visits'] > 0 || $r['total_amount'] > 0)
          ->values();
    }
}
