<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Brick;
use App\Models\Tablet;
use App\Models\Employee;
use App\Models\EmployeeTablet;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelDataUploadController extends Controller
{
    private function formatDate($date)
    {
        return strtotime($date) ? date('Y-m-d', strtotime($date)) : null;
    }


    public function uploadTabletsAssignment(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'sheet_name' => 'nullable|string',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());

            $sheetName = $request->input('tablets_assignments');
            if ($sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if (!$sheet) {
                    return back()->withErrors(['error' => 'Лист с таким именем не найден!']);
                }
            } else {
                $sheet = $spreadsheet->getActiveSheet();
            }

            $rows = $sheet->toArray();

            foreach ($rows as $index => $row) {
                if ($index === 0 || empty(array_filter($row))) continue; // Пропускаем заголовки и пустые строки

                $tabletId = isset($row[1]) ? $row[1] : null;
                $employeeId = isset($row[2]) && is_numeric($row[2]) ? (int) $row[2] : null;

                // Если tablet_id не число, пробуем найти его в базе по названию
                if (!is_numeric($tabletId)) {
                    $tablet = Tablet::where('model', $tabletId)->first();
                    $tabletId = $tablet ? $tablet->id : null;
                }

                if (is_null($tabletId)) {
                    return back()->withErrors(['error' => "Ошибка: tablet_id отсутствует или не найден в базе в строке " . ($index + 1)]);
                }

                EmployeeTablet::firstOrCreate(
                    ['tablet_id' => $tabletId, 'employee_id' => $employeeId],
                    [
                        'assigned_at' => $this->formatDate($row[3]),
                        'returned_at' => $this->formatDate($row[4]),
                        'confirmed' => $row[5] ?? null
                    ]
                );
            }



            return back()->with('success', 'Данные успешно загружены!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }




    public function uploadTablets(Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'sheet_name' => 'nullable|string', // Добавляем поле для выбора листа
        ]);

        $file = $request->file('file');

        try {
            // Читаем Excel файл
            $spreadsheet = IOFactory::load($file->getPathname());

            // Определяем, какой лист выбрать
            $sheetName = $request->input('tablets'); // Получаем имя листа из формы
            if ($sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName); // Выбираем лист по имени
                if (!$sheet) {
                    return back()->withErrors(['error' => 'Лист с таким именем не найден!']);
                }
            } else {
                $sheet = $spreadsheet->getActiveSheet(); // Если имя не указано, берём активный лист
            }

            $rows = $sheet->toArray();

            foreach ($rows as $index => $row) {
                if ($index === 0 || empty(array_filter($row))) continue; // Пропускаем заголовки и пустые строки

                Tablet::firstOrCreate(
                    ['serial_number' => $row[3]], // Уникальный ключ
                    [
                        'id' => $row[0],
                        'model' => $row[1] ?? null,
                        'invent_number' => $row[2] ?? null,
                        'imei' => $row[4] ?? null,
                        'beeline_number' => $row[5] ?? null,
                        'beeline_number_status' => $row[6] ?? null,
                        'status' => $row[7] ?? null,
                        'old_employee_id' => is_numeric($row[8]) ? $row[8] : null,
                        'employee_id' => is_numeric($row[9]) ? $row[9] : null,
                    ]
                );
            }


            return back()->with('success', 'Данные успешно загружены!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }

    public function uploadEmployees(Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'sheet_name' => 'nullable|string', // Добавляем поле для выбора листа
        ]);

        $file = $request->file('file');

        try {
            // Читаем Excel файл
            $spreadsheet = IOFactory::load($file->getPathname());

            // Определяем, какой лист выбрать
            $sheetName = $request->input('employees'); // Получаем имя листа из формы
            if ($sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName); // Выбираем лист по имени
                if (!$sheet) {
                    return back()->withErrors(['error' => 'Лист с таким именем не найден!']);
                }
            } else {
                $sheet = $spreadsheet->getActiveSheet(); // Если имя не указано, берём активный лист
            }

            $rows = $sheet->toArray();

            foreach ($rows as $index => $row) {
                if ($index === 0 || empty(array_filter($row))) continue; // Пропускаем заголовки и пустые строки

                // if (empty($row[4])) {
                //     return back()->withErrors(['error' => "Ошибка в строке $index: фамилия не может быть пустой"]);
                // }

                Employee::updateOrCreate(
                    ['id' => $row[0]], // ID из файла
                    [
                        'full_name' => $row[1] ?? null,
                        'first_name' => $row[2] ?? null,
                        'last_name' => $row[3] ?? null,
                        'birth_date' => !empty($row[4]) ? Carbon::createFromFormat('d/m/Y', $row[4])->format('Y-m-d') : null,
                        'email' => $row[5] ?? null,
                        'hiring_date' => !empty($row[6]) ? Carbon::createFromFormat('d/m/Y', $row[6])->format('Y-m-d') : null,
                        'firing_date' => !empty($row[7]) ? Carbon::createFromFormat('d/m/Y', $row[7])->format('Y-m-d') : null,
                        'position' => $row[8] ?? null,
                        'status' => $row[9] ?? null,
                    ]
                );
            }


            return back()->with('success', 'Данные успешно загружены!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }

}
