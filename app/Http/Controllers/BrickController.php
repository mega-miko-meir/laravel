<?php

namespace App\Http\Controllers;

use App\Models\Brick;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BrickController extends Controller{

    public function handleBricks(Request $request, Territory $territory, Brick $brick = null){
        if ($request->isMethod('post')) {
            $brickIds = $request->input('bricks', []);

            // Проверяем, если брики были выбраны
            if (!empty($brickIds)) {
                $selectedBricks = Brick::whereIn('code', $brickIds)->get();

                // Для каждого выбранного брика
                foreach ($selectedBricks as $brick) {
                    // Проверяем, существует ли уже связь между территорией и бриком
                    if (!$territory->bricks->contains($brick)) {
                        // Добавляем брик, если комбинация ещё не существует
                        $territory->bricks()->attach($brick->id);
                    }
                }
            }

            return redirect()->back()->with('success', 'Bricks successfully assigned to territory!');
        }

        if ($request->isMethod('delete')) {
            // Логика удаления брика
            if ($brick && $territory->bricks->contains($brick->id)) {
                $territory->bricks()->detach($brick->id);
                return redirect()->back()->with('success', 'Brick successfully detached from territory!');
            }
            return redirect()->back()->with('error', 'Brick is not attached to this territory.');
        }

        // return redirect()->back()->with('error', 'Invalid request method.');
    }


    public function formTemplate(Employee $employee){
        // Creating an excel file template and filling in the data
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // $sheet->getDefaultColumnDimension()->setWidth(25);

        $sheet->setCellValue('A1', 'User');
        $sheet->setCellValue('A2', $employee->full_name);
        $sheet->setCellValue('B1', 'Username');
        $sheet->setCellValue('B2', $employee->email);
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('C2', $employee->email);
        $sheet->setCellValue('D1', 'Territory');
        $sheet->setCellValue('D2', $employee->territories->first()->territory_name);
        $sheet->setCellValue('E1', 'Division');
        $sheet->setCellValue('E2', $employee->territories->first()->team);
        $sheet->setCellValue('F1', 'Manager');
        $sheet->setCellValue('F2', $employee->territories->first()->manager_id);



        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Bricks');
        $sheet2->setCellValue('A1', 'Brick Code');
        $sheet2->setCellValue('B1', 'Brick Name');
        $sheet2->setCellValue('C1', 'Territory Name');
        $sheet2->setCellValue('D1', 'Note');

        $selectedBricks = $employee->territories ? $employee->territories->first()->bricks : collect();

        foreach ($selectedBricks as $index => $brick) {
            $sheet2->setCellValue('A'.($index+2), $brick->additional_code);
            $sheet2->setCellValue('B'.($index+2), $brick->description);
            $sheet2->setCellValue('C'.($index+2), $employee->territories->first()->territory_name);
            $sheet2->setCellValue('D'.($index+2), 'Add this brick to territory');
        }

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        foreach ($sheet2->getColumnIterator() as $column) {
            $sheet2->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $fileName = 'excel/OCE-P New User '.$employee->full_name . '.xlsx';
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($fileName);

        return redirect()->back()->with('success', 'Completed template was created successfully!');

    }




    public function assignBricks(Employee $employee, Territory $territory, Request $request){

        $brickIds = $request->input('bricks', []);
        // Проверяем, если брики были выбраны
        if ($request->has('bricks')) {
            $selectedBricks = Brick::whereIn('code', $brickIds)->get();
            // Для каждого брика проверим, не существует ли уже комбинация с данной территорией
            foreach ($selectedBricks as $brick) {
                // Проверяем, существует ли уже связь между территорией и бриком
                if (!$territory->bricks->contains($brick->id)) {
                    // Добавляем брик, если комбинация ещё не существует
                    $territory->bricks()->attach($brick->id);
                }
            }
        }



        // Creating an excel file template and filling in the data
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // $sheet->getDefaultColumnDimension()->setWidth(25);

        $sheet->setCellValue('A1', 'User');
        $sheet->setCellValue('A2', $employee->full_name);
        $sheet->setCellValue('B1', 'Username');
        $sheet->setCellValue('B2', $employee->email);
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('C2', $employee->email);
        $sheet->setCellValue('D1', 'Territory');
        $sheet->setCellValue('D2', $territory->territory_name);
        $sheet->setCellValue('E1', 'Division');
        $sheet->setCellValue('E2', $territory->team);
        $sheet->setCellValue('F1', 'Manager');
        $sheet->setCellValue('F2', $territory->manager_id);



        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Bricks');
        $sheet2->setCellValue('A1', 'Brick Code');
        $sheet2->setCellValue('B1', 'Brick Name');
        $sheet2->setCellValue('C1', 'Territory Name');
        $sheet2->setCellValue('D1', 'Note');

        foreach ($selectedBricks as $index => $brick) {
            $sheet2->setCellValue('A'.($index+2), $brick->additional_code);
            $sheet2->setCellValue('B'.($index+2), $brick->description);
            $sheet2->setCellValue('C'.($index+2), $territory->territory_name);
            $sheet2->setCellValue('D'.($index+2), 'Add this brick to territory');
        }

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        foreach ($sheet2->getColumnIterator() as $column) {
            $sheet2->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $fileName = 'excel/OCE-P New User '.$employee->full_name . '.xlsx';
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($fileName);

        return redirect()->back()->with('success', 'Completed template was created successfully!');

    }


    // public function showBricks(){
    //     $bricks = Brick::all();
    //     return view('bricks', compact('bricks'));
    // }

    public function uploadBricks(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

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

                // Brick::create([
                //     'country' => $row[0] ?? null,
                //     'code' => $row[1] ?? null,
                //     'description' => $row[2] ?? null,
                //     'additional_code' => $row[3] ?? null,
                // ]);

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
