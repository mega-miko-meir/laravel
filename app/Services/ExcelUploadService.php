<?php

namespace App\Services;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelUploadService
{
    /**
     * Load rows from an uploaded Excel file, optionally selecting a sheet by name.
     *
     * @throws \Exception
     */
    public function loadRows(Request $request, ?string $sheetInputName = null): array
    {
        $spreadsheet = IOFactory::load($request->file('file')->getPathname());

        if ($sheetInputName && $name = $request->input($sheetInputName)) {
            $sheet = $spreadsheet->getSheetByName($name);
            if (!$sheet) {
                throw new \Exception('Лист с таким именем не найден!');
            }
        } else {
            $sheet = $spreadsheet->getActiveSheet();
        }

        return $sheet->toArray();
    }

    /**
     * Skip the header row and any blank rows.
     */
    public function dataRows(array $rows): array
    {
        return array_values(
            array_filter(
                array_slice($rows, 1),
                fn($row) => !empty(array_filter($row))
            )
        );
    }
}
