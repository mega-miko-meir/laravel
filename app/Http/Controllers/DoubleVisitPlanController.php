<?php

namespace App\Http\Controllers;

use App\Services\DoubleVisitPlanService;
use App\Support\Etl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoubleVisitPlanController extends Controller
{
    public function __construct(private DoubleVisitPlanService $service)
    {
    }

    public function index(Request $request)
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));

        try {
            $data = $this->loadMonth($month);
        } catch (\Exception $e) {
            return back()->withErrors(['nobel_db' => 'Не удалось получить данные из Nobel CRM. Попробуйте позже.']);
        }

        return view('double-visit-plan', [
            'month'       => $month,
            'initialData' => $data,
        ]);
    }

    /**
     * JSON-эндпоинт смены месяца без перезагрузки страницы (тот же паттерн, что
     * на «Визитах»/«Таргетных клиентах») — отдаёт закэшированный (или свежий)
     * отчёт за выбранный месяц.
     */
    public function data(Request $request)
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));

        try {
            return response()->json($this->loadMonth($month), 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось получить данные из Nobel CRM. Попробуйте позже.']);
        }
    }

    private function loadMonth(string $month): array
    {
        [$from, $to] = $this->monthBounds($month);

        // Раньше отчёт считался живьём при КАЖДОМ открытии страницы (запрос к
        // qs_double_calls + вся ло­кальная логика плана) — данные из Nobel CRM
        // обновляются раз в сутки ночным ETL, поэтому кэшируем до следующего
        // запуска (Etl::secondsUntilNextRun()), как и на «Визитах».
        return Cache::remember("double_visit_plan_{$month}", Etl::secondsUntilNextRun(), function () use ($from, $to) {
            return [
                'error'  => null,
                'report' => $this->service->getReport($from, $to),
            ];
        });
    }

    public function export(Request $request): BinaryFileResponse
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));
        [$from, $to] = $this->monthBounds($month);

        // Переиспользуем тот же кэш, что и index()/data() — раньше export()
        // гонял getReport() живьём заново, даже если тот же месяц только что
        // считался для отображения на странице.
        $report = collect($this->loadMonth($month)['report'] ?? []);

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

    /** @return array{0: string, 1: string} [monthStart, monthEnd] в формате Y-m-d */
    private function monthBounds(string $month): array
    {
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        return [$date->toDateString(), $date->copy()->endOfMonth()->toDateString()];
    }
}
