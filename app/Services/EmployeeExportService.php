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
     * Соответствие выбираемого в UI статуса и event_type из employee_events,
     * по которому определяется этот статус (см. Employee::scopeActive()).
     */
    private const STATUS_EVENT_TYPES = [
        'active'           => ['hired', 'return_from_leave'],
        'maternity_leave'  => ['maternity_leave'],
        'dismissed'        => ['dismissed'],
    ];

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
            'status' => fn($e) => match ($e->latestEvent?->event_type) {
                'dismissed'        => 'Уволен',
                'maternity_leave'  => 'В декрете',
                'hired', 'return_from_leave' => 'Активен',
                default            => '',
            },
            'status_event_date' => fn($e) =>
                in_array($e->latestEvent?->event_type, ['dismissed', 'maternity_leave'])
                    ? \Carbon\Carbon::parse($e->latestEvent->event_date)->format('d.m.Y')
                    : '',
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
            'role' => 'Позиция',
            'status' => 'Статус',
            'status_event_date' => 'Дата увольнения/декрета',
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

        $statuses = array_intersect(
            $request->input('statuses', ['active']),
            array_keys(self::STATUS_EVENT_TYPES)
        ) ?: ['active'];

        $eventTypes = array_unique(array_merge(
            ...array_map(fn($status) => self::STATUS_EVENT_TYPES[$status], $statuses)
        ));

        $employees = Employee::withLatestEvent()
            ->whereHas('latestEvent', fn($q) => $q->whereIn('event_type', $eventTypes))
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
