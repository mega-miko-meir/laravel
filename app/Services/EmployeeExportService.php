<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeExportService
{
    /**
     * Get the export mapping for employee fields.
     *
     * @return array
     */
    private function exportMap(): array
    {
        return [
            'full_name' => fn($e) => $e->full_name,
            'first_name_eng' => fn($e) =>
                trim($e->first_name . ' ' . $e->last_name),
            'city' => fn($e) =>
                $e->employee_territory()->latest('assigned_at')->first()->city ?? '',
            'email' => fn($e) => $e->email,
            'team' => fn($e) =>
                $e->employee_territory()->latest('assigned_at')->first()->team ?? '',
            'department' => fn($e) =>
                $e->employee_territory()->latest('assigned_at')->first()->department ?? '',
            'manager' => fn($e) =>
                $e->employee_territory()->latest('assigned_at')->first()->parent->employee->full_name ?? '',
            'hiring_date' => fn($e) =>
                optional($e->events()->where('event_type', 'hired')->latest('event_date')->first())->event_date
                    ? \Carbon\Carbon::parse($e->events()->where('event_type', 'hired')->latest('event_date')->first()->event_date)->format('d.m.Y')
                    : '',
            'role' => fn($e) => $e->employee_territory()->latest('assigned_at')->first()->role ?? '',
        ];
    }

    /**
     * Get labels for export fields.
     *
     * @param string $key
     * @return string
     */
    private function labels(string $key): string
    {
        return [
            'full_name'      => 'ФИО',
            'first_name_eng' => 'ФИО англ',
            'city'           => 'Город',
            'email' => 'Почта',
            'team' => 'Группа',
            'department' => 'Департамент',
            'manager' => 'РМ',
            'hiring_date' => 'Дата приема',
            'role' => 'Позиция'
        ][$key] ?? $key;
    }

    /**
     * Export employees to Excel based on request parameters.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $columns = $request->input('columns', []);
        $withExperience = $request->boolean('with_experience');
        $experienceDate = $request->input('experience_date')
            ? Carbon::parse($request->experience_date)
            : now();

        $employees = Employee::withLatestEvent()
            ->active()
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /*
        |--------------------------------------------------------------------------
        | Заголовки
        |--------------------------------------------------------------------------
        */
        $col = 'A';

        foreach ($columns as $field) {
            $sheet->setCellValue($col . '1', $this->labels($field));
            $col++;
        }

        if ($withExperience) {
            $sheet->setCellValue($col . '1', 'Стаж (лет)');
        }

        /*
        |--------------------------------------------------------------------------
        | Данные
        |--------------------------------------------------------------------------
        */
        $row = 2;

        foreach ($employees as $employee) {
            $col = 'A';

            foreach ($columns as $field) {
                $map = $this->exportMap();
                $value = isset($map[$field]) ? ($map[$field])($employee) : '';
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }

            if ($withExperience) {
                $experience = '';

                $latestEvent = $employee->latestEvent;

                if ($latestEvent && $latestEvent->event_type !== "dismissed") {
                    $hiringEvent = $employee->events()
                        ->where('event_type', 'hired')
                        ->latest('event_date')
                        ->first();
                } else {
                    $hiringEvent = null;
                }

                if ($hiringEvent && $hiringEvent->event_date) {
                    $experience = round(
                        Carbon::parse($hiringEvent->event_date)
                            ->diffInDays($experienceDate) / 365,
                        1
                    );
                }

                $sheet->setCellValue($col . $row, $experience);
            }

            $row++;
        }

        /*
        |--------------------------------------------------------------------------
        | Сохранение и отдача файла
        |--------------------------------------------------------------------------
        */
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path('employees.xlsx');
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
