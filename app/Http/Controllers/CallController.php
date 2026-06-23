<?php

namespace App\Http\Controllers;

use App\Models\Nobel\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CallController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1 запрос вместо 6: все KPI-метрики за один SELECT
            $kpi = $this->filtered($request)
                ->selectRaw('COUNT(*) as total, COUNT(DISTINCT employee) as employees_count, ROUND(AVG(CASE WHEN appointment_duration > 0 THEN appointment_duration END)) as avg_duration, SUM(appointment_type = "Визит к врачу") as doctor_visits, SUM(appointment_type = "Визит в аптеку") as pharmacy_visits')
                ->first();

            $totalVisits       = (int) ($kpi->total ?? 0);
            $employeesCount    = (int) ($kpi->employees_count ?? 0);
            $avgDuration       = (int) ($kpi->avg_duration ?? 0);
            $doctorVisits      = (int) ($kpi->doctor_visits ?? 0);
            $pharmacyVisits    = (int) ($kpi->pharmacy_visits ?? 0);
            $visitsPerEmployee = $employeesCount > 0 ? round($totalVisits / $employeesCount, 1) : 0;

            // Monthly trend (last 12 months)
            $monthlyTrend = $this->filtered($request)
                ->selectRaw("DATE_FORMAT(appointment_Date, '%Y-%m') as month, COUNT(*) as total, COUNT(*) as completed")
                ->whereNotNull('appointment_Date')
                ->groupBy('month')
                ->orderBy('month')
                ->limit(12)
                ->get();

            // Top 10 regions
            $topRegions = $this->filtered($request)
                ->selectRaw('province, COUNT(*) as total')
                ->whereNotNull('province')->where('province', '<>', '')
                ->groupBy('province')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            // Top specialties
            $topSpecialties = $this->filtered($request)
                ->selectRaw('customer_spesiality, COUNT(*) as total')
                ->whereNotNull('customer_spesiality')->where('customer_spesiality', '<>', '')
                ->groupBy('customer_spesiality')
                ->orderByDesc('total')
                ->limit(12)
                ->get();

            // Paginated table with sorting
            $allowedSorts = ['appointment_Date', 'employee', 'organization', 'province', 'town', 'appointment_duration'];
            $sortCol = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'appointment_Date';
            $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

            $calls = $this->filtered($request)->orderBy($sortCol, $sortDir)->paginate(25);

            // Опции фильтров — кэшируются на 1 час
            $base = fn($col) => Call::whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку'])
                ->where('appointment_status', 'Выполнено')
                ->distinct()->whereNotNull($col)->where($col, '<>', '')->orderBy($col)->pluck($col);

            $provinces   = Cache::remember('calls_filter_provinces',   3600, fn() => $base('province'));
            $towns       = Cache::remember('calls_filter_towns',       3600, fn() => $base('town'));
            $specialties = Cache::remember('calls_filter_specialties', 3600, fn() => $base('customer_spesiality'));
            $departments = Cache::remember('calls_filter_departments', 3600, fn() => $base('employee_department'));

            $empList = \App\Models\Employee::whereNotNull('crm_employee_id')
                ->orderBy('full_name')
                ->get(['full_name', 'crm_employee_id'])
                ->map(fn($e) => ['label' => $e->full_name, 'value' => $e->crm_employee_id])
                ->values();

        } catch (\Exception $e) {
            return back()->withErrors(['nobel_db' => 'Nobel CRM недоступна: ' . $e->getMessage()]);
        }

        return view('calls', compact(
            'calls', 'provinces', 'towns', 'specialties', 'departments', 'empList',
            'totalVisits', 'employeesCount', 'avgDuration', 'visitsPerEmployee',
            'monthlyTrend', 'topRegions', 'topSpecialties',
            'doctorVisits', 'pharmacyVisits',
            'sortCol', 'sortDir'
        ));
    }

    public function export(Request $request)
    {
        $columns = [
            'appointment_Date'   => 'Дата',
            'employee'           => 'Сотрудник',
            'manager'            => 'Менеджер',
            'organization'       => 'Организация',
            'organization_type'  => 'Тип',
            'town'               => 'Город',
            'province'           => 'Регион',
            'appointment_status' => 'Статус',
            'appointment_type'   => 'Тип визита',
            'appointment_duration' => 'Длительность (мин)',
        ];

        $fileName = 'calls_' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($request, $columns) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, array_values($columns), ';');

            $this->filtered($request)->orderBy('appointment_Date', 'desc')->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $row) {
                    fputcsv($out, array_map(fn($col) => $row->$col ?? '', array_keys($columns)), ';');
                }
                ob_flush();
                flush();
            });

            fclose($out);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function filtered(Request $request)
    {
        $q = Call::query()
            ->whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку'])
            ->where('appointment_status', 'Выполнено');
        if ($request->filled('date_from'))            $q->where('appointment_Date', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))              $q->where('appointment_Date', '<=', $request->input('date_to'));
        if ($request->filled('province'))             $q->whereIn('province', (array) $request->input('province'));
        if ($request->filled('town'))                 $q->whereIn('town', (array) $request->input('town'));
        if ($request->filled('employee'))             $q->where('employee', 'like', '%' . $request->input('employee') . '%');
        if ($request->filled('crm_employee_id'))        $q->where('employee_id', $request->input('crm_employee_id'));
        if ($request->filled('employee_department'))    $q->whereIn('employee_department', (array) $request->input('employee_department'));
        if ($request->filled('organization_type'))    $q->whereIn('organization_type', (array) $request->input('organization_type'));
        if ($request->filled('appointment_status'))   $q->whereIn('appointment_status', (array) $request->input('appointment_status'));
        if ($request->filled('customer_spesiality'))  $q->whereIn('customer_spesiality', (array) $request->input('customer_spesiality'));
        return $q;
    }
}
