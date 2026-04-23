<?php

namespace App\Services;

use App\Models\Tablet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TabletExportService
{
    // -------------------------------------------------------------------------
    // Column map: field => callable($tablet)
    // -------------------------------------------------------------------------

    private function exportMap(): array
    {
        return [
            'invent_number' => fn($t) => $t->invent_number,

            'serial_number' => fn($t) => $t->serial_number,

            'model' => fn($t) => $t->model,

            'imei' => fn($t) => $t->imei,

            'beeline_number' => fn($t) => $t->beeline_number,

            'beeline_number_status' => fn($t) => $t->beeline_number_status,

            'status' => fn($t) => $t->status,

            // Текущий сотрудник (у кого сейчас планшет)
            'employee_name' => fn($t) => $t->currentEmployee?->full_name ?? '',
            'position' => fn($t) =>
                $t->currentEmployee?->employee_territory()
                    // ->whereNull('unassigned_at')
                    ->latest('assigned_at')
                    ->first()
                    ?->role ?? '',

            // Город текущего сотрудника
            'employee_city' => fn($t) =>
                $t->currentEmployee
                    ?->employee_territory()
                    ->whereNull('unassigned_at')
                    ->latest('assigned_at')
                    ->first()
                    ?->city ?? '',

            // Менеджер (RM) текущего сотрудника
            'employee_manager' => fn($t) =>
                $t->currentEmployee?->current_manager?->full_name ?? '',

            // Дата привязки текущего сотрудника
            'assigned_at' => fn($t) =>
                $t->latestAssignment?->assigned_at
                    ? Carbon::parse($t->latestAssignment->assigned_at)->format('d.m.Y')
                    : '',

            // Дата возврата (если уже вернул)
            'returned_at' => fn($t) =>
                $t->latestAssignment?->returned_at
                    ? Carbon::parse($t->latestAssignment->returned_at)->format('d.m.Y')
                    : '',

            // Ответственное лицо
            'responsible_name' => fn($t) => $t->responsible?->full_name ?? '',

            // Город ответственного лица
            'responsible_city' => fn($t) =>
                $t->responsible
                    ?->employee_territory()
                    ->whereNull('unassigned_at')
                    ->latest('assigned_at')
                    ->first()
                    ?->city ?? '',
        ];
    }

    private function labels(string $key): string
    {
        return [
            'invent_number'         => 'Инв. номер',
            'serial_number'         => 'Серийный номер',
            'model'                 => 'Модель',
            'imei'                  => 'IMEI',
            'beeline_number'        => 'Билайн номер',
            'beeline_number_status' => 'Статус Билайн',
            'status'                => 'Статус планшета',
            'employee_name'         => 'Сотрудник',
            'employee_city'         => 'Город сотрудника',
            'employee_manager'      => 'Менеджер сотрудника',
            'assigned_at'           => 'Дата привязки',
            'returned_at'           => 'Дата возврата',
            'responsible_name'      => 'Ответственное лицо',
            'responsible_city'      => 'Город ответственного',
        ][$key] ?? $key;
    }

    // -------------------------------------------------------------------------
    // Main export method
    // -------------------------------------------------------------------------

    public function exportToExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $columns = $request->input('columns', []);

        $tablets = Tablet::with([
            'latestAssignment.employee.employee_territory',
            'responsible.employee_territory',
        ])->get();

        // Подгружаем currentEmployee через accessor (использует latestAssignment)
        // Он уже загружен, поэтому доп. запросов не будет

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Планшеты');

        // Стиль заголовков
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => 'center'],
        ];

        // Заголовки
        $col = 1;
        foreach ($columns as $field) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getCell($colLetter . '1')->setValue($this->labels($field));
            $sheet->getStyle($colLetter . '1')->applyFromArray($headerStyle);
            $sheet->getColumnDimension($colLetter)->setWidth(20);
            $col++;
        }

        // Данные
        $row = 2;
        $map = $this->exportMap();

        foreach ($tablets as $tablet) {
            $col = 1;
            foreach ($columns as $field) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $value = isset($map[$field]) ? ($map[$field])($tablet) : '';
                $sheet->getCell($colLetter . $row)->setValue($value);
                $col++;
            }
            $row++;
        }

        // Автофильтр
        if (!empty($columns)) {
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns));
            $sheet->setAutoFilter("A1:{$lastCol}1");
        }

        // Заморозить первую строку
        $sheet->freezePane('A2');

        $writer   = new Xlsx($spreadsheet);
        $filePath = storage_path('tablets_export.xlsx');
        $writer->save($filePath);

        return response()->download($filePath, 'tablets_' . now()->format('Y-m-d') . '.xlsx')
            ->deleteFileAfterSend(true);
    }
}
