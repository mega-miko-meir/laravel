<?php

namespace App\Http\Controllers;

use App\Services\DoubleVisitPlanService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoubleVisitPlanController extends Controller
{
    public function index(Request $request, DoubleVisitPlanService $service)
    {
        $from = $request->input('date_from', now()->subMonths(3)->startOfMonth()->toDateString());
        $to   = $request->input('date_to', now()->toDateString());

        $report = null;
        $error  = null;

        try {
            $report = $service->getReport($from, $to);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DoubleVisitPlan report failed', ['error' => $e->getMessage()]);
            $error = 'Не удалось получить данные из Nobel CRM. Попробуйте позже.';
        }

        return view('double-visit-plan', [
            'report' => $report,
            'error'  => $error,
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    public function export(Request $request, DoubleVisitPlanService $service): BinaryFileResponse
    {
        $from = $request->input('date_from', now()->subMonths(3)->startOfMonth()->toDateString());
        $to   = $request->input('date_to', now()->toDateString());

        $report = $service->getReport($from, $to);

        $spreadsheet = new Spreadsheet();

        // Лист 1 — сводка по РМ
        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Сводка по РМ');
        $summary->fromArray(['РМ', 'Территория', 'Факт', 'План', '% KPI'], null, 'A1');
        $summary->getStyle('A1:E1')->getFont()->setBold(true);
        $summary->freezePane('A2');

        $row = 2;
        foreach ($report as $r) {
            $summary->fromArray([
                $r->rm_name,
                $r->territory,
                $r->fact,
                $r->plan,
                $r->kpi_percent === null ? '' : str_replace('.', ',', $r->kpi_percent) . '%',
            ], null, 'A' . $row);
            $row++;
        }
        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $summary->getColumnDimension($col)->setAutoSize(true);
        }

        // Лист 2 — детализация по медпредам
        $detail = $spreadsheet->createSheet();
        $detail->setTitle('По медпредам');
        $detail->fromArray(['РМ', 'Медпред', 'Факт', 'План', '% KPI'], null, 'A1');
        $detail->getStyle('A1:E1')->getFont()->setBold(true);
        $detail->freezePane('A2');

        $row = 2;
        foreach ($report as $r) {
            foreach ($r->reps as $rep) {
                $detail->fromArray([
                    $r->rm_name,
                    $rep->rep_name,
                    $rep->fact,
                    $rep->plan,
                    $rep->kpi_percent === null ? '' : str_replace('.', ',', $rep->kpi_percent) . '%',
                ], null, 'A' . $row);
                $row++;
            }
        }
        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $detail->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'double_visit_plan_' . $from . '_' . $to . '.xlsx';
        $filePath = storage_path('app/' . $fileName);
        (new Xlsx($spreadsheet))->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
