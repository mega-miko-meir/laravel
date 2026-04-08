<?php

namespace App\Services;

use App\Http\Requests\UploadExcelFileRequest;
use App\Models\Brick;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeImportService
{
    /**
     * Upload and process Excel file for bricks.
     *
     * @param UploadExcelFileRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadBricks(UploadExcelFileRequest $request)
    {
        $file = $request->file('file');

        try {
            // Чтение Excel файла
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    // Пропуск заголовков
                    continue;
                }

                // Проверка на уникальность по полю 'code' перед добавлением
                Brick::firstOrCreate(
                    ['code' => $row[1]], // 'code' должен быть уникальным
                    [
                        'country' => $row[0] ?? null,
                        'description' => $row[2] ?? null,
                        'additional_code' => $row[3] ?? null,
                    ]
                );
            }

            return back()->with('success', 'Данные успешно загружены!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }
}