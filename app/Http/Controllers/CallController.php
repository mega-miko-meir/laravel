<?php

namespace App\Http\Controllers;

use App\Models\Nobel\Call;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function index(Request $request)
    {
        // KPI
        $totalVisits      = $this->filtered($request)->count();
        $completedVisits  = $this->filtered($request)->where('appointment_status', 'Выполнено')->count();
        $employeesCount   = $this->filtered($request)->distinct()->whereNotNull('employee')->count('employee');
        $avgDuration      = (int) ($this->filtered($request)->where('appointment_status', 'Выполнено')->whereNotNull('appointment_duration')->where('appointment_duration', '>', 0)->avg('appointment_duration') ?? 0);
        $completionRate   = $totalVisits > 0 ? round($completedVisits / $totalVisits * 100, 1) : 0;
        $visitsPerEmployee = $employeesCount > 0 ? round($totalVisits / $employeesCount, 1) : 0;

        // Monthly trend (last 12 months)
        $monthlyTrend = $this->filtered($request)
            ->selectRaw("DATE_FORMAT(appointment_Date, '%Y-%m') as month, COUNT(*) as total, SUM(appointment_status = 'Выполнено') as completed")
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

        // Status breakdown (pie/doughnut data)
        $statusBreakdown = $this->filtered($request)
            ->selectRaw('appointment_status, COUNT(*) as total')
            ->whereNotNull('appointment_status')
            ->groupBy('appointment_status')
            ->orderByDesc('total')
            ->get();

        // Paginated table with sorting
        $allowedSorts = ['appointment_Date', 'employee', 'organization', 'province', 'town', 'appointment_status', 'appointment_duration'];
        $sortCol = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'appointment_Date';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $calls = $this->filtered($request)->orderBy($sortCol, $sortDir)->paginate(25);

        // Filter options
        $provinces   = Call::distinct()->whereNotNull('province')->where('province', '<>', '')->orderBy('province')->pluck('province');
        $towns       = Call::distinct()->whereNotNull('town')->where('town', '<>', '')->orderBy('town')->pluck('town');
        $orgTypes    = Call::distinct()->whereNotNull('organization_type')->where('organization_type', '<>', '')->orderBy('organization_type')->pluck('organization_type');
        $statuses    = Call::distinct()->whereNotNull('appointment_status')->where('appointment_status', '<>', '')->orderBy('appointment_status')->pluck('appointment_status');
        $specialties = Call::distinct()->whereNotNull('customer_spesiality')->where('customer_spesiality', '<>', '')->orderBy('customer_spesiality')->pluck('customer_spesiality');
        $departments = Call::distinct()->whereNotNull('employee_department')->where('employee_department', '<>', '')->orderBy('employee_department')->pluck('employee_department');

        $doctorVisits   = $this->filtered($request)->where('appointment_type', 'Визит к врачу')->count();
        $pharmacyVisits = $this->filtered($request)->where('appointment_type', 'Визит в аптеку')->count();

        return view('calls', compact(
            'calls', 'provinces', 'towns', 'orgTypes', 'statuses', 'specialties', 'departments',
            'totalVisits', 'completedVisits', 'employeesCount', 'avgDuration',
            'completionRate', 'visitsPerEmployee',
            'monthlyTrend', 'topRegions', 'topSpecialties', 'statusBreakdown',
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
