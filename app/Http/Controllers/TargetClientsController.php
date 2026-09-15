<?php

namespace App\Http\Controllers;

use App\Services\TargetClientsService;
use App\Support\Etl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TargetClientsController extends Controller
{
    private const CACHE_VERSION = 'v1';

    public function index(Request $request, TargetClientsService $service)
    {
        $month      = $request->input('month', now()->subMonth()->format('Y-m'));
        $department = $request->input('department') ?: null;

        ['departments' => $departments, 'doctorsSummary' => $doctorsSummary,
         'pharmaciesSummary' => $pharmaciesSummary, 'error' => $error] = $this->loadMonth($service, $month);

        return view('target-clients', compact(
            'month', 'department', 'departments', 'doctorsSummary', 'pharmaciesSummary', 'error'
        ));
    }

    /**
     * JSON-эндпоинт для смены месяца без перезагрузки страницы (Alpine.js fetch).
     * В отличие от отдела, месяц нельзя посчитать заранее на клиенте — это новый
     * запрос к Nobel CRM, но сама страница при этом не перезагружается.
     */
    public function data(Request $request, TargetClientsService $service)
    {
        $month = $request->input('month', now()->subMonth()->format('Y-m'));

        return response()->json($this->loadMonth($service, $month));
    }

    private function loadMonth(TargetClientsService $service, string $month): array
    {
        // Раньше считалось живьём при КАЖДОМ открытии/смене месяца — данные из
        // Nobel CRM обновляются раз в сутки ночным ETL, поэтому кэшируем до
        // следующего запуска, как и на остальных страницах «Аналитики».
        return Cache::remember(
            "target_clients_month_" . self::CACHE_VERSION . "_{$month}",
            Etl::secondsUntilNextRun(),
            function () use ($service, $month) {
                [$monthStart, $monthEnd] = $this->monthBounds($month);

                $doctorsSummary = null;
                $pharmaciesSummary = null;
                $departments = [];
                $error = null;

                try {
                    // Всегда считаем ЦЕЛИКОМ, без фильтра по отделу — фильтрация на странице
                    // происходит на клиенте (Alpine.js), без похода на сервер при каждом
                    // переключении отдела. Список отделов для <select> — union отделов,
                    // фактически встретившихся хотя бы в одном из двух деревьев.
                    $doctorsSummary    = $service->summary(TargetClientsService::SEGMENT_DOCTORS, $monthStart, $monthEnd);
                    $pharmaciesSummary = $service->summary(TargetClientsService::SEGMENT_PHARMACIES, $monthStart, $monthEnd);

                    $departments = $doctorsSummary->tree->pluck('department')
                        ->merge($pharmaciesSummary->tree->pluck('department'))
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('TargetClients report failed', ['error' => $e->getMessage()]);
                    $error = 'Не удалось получить данные из Nobel CRM. Попробуйте позже.';
                }

                return compact('departments', 'doctorsSummary', 'pharmaciesSummary', 'error');
            }
        );
    }

    public function exportDoctors(Request $request, TargetClientsService $service): BinaryFileResponse
    {
        return $this->export($request, $service, TargetClientsService::SEGMENT_DOCTORS, 'Таргетные врачи');
    }

    public function exportPharmacies(Request $request, TargetClientsService $service): BinaryFileResponse
    {
        return $this->export($request, $service, TargetClientsService::SEGMENT_PHARMACIES, 'Таргетные аптеки');
    }

    private function export(Request $request, TargetClientsService $service, string $segment, string $title): BinaryFileResponse
    {
        // Месяц может дать 15-20 тыс. уникальных строк (врачи/аптеки на большую
        // компанию) — PhpSpreadsheet держит накладные расходы на ячейку, и
        // дефолтных 128M не хватает; поднимаем только для этого действия.
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $month      = $request->input('month', now()->subMonth()->format('Y-m'));
        $department = $request->input('department') ?: null;
        [$monthStart, $monthEnd] = $this->monthBounds($month);

        $rows    = $service->exportRows($segment, $monthStart, $monthEnd, $department);
        $columns = $service->exportColumns($segment);
        $labels  = $service->exportLabels($segment);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($labels, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($rows as $r) {
            $sheet->fromArray(array_map(fn($col) => $r->$col ?? '', $columns), null, 'A' . $row);
            $row++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = \Illuminate\Support\Str::slug($title) . '_' . $month . '.xlsx';
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
