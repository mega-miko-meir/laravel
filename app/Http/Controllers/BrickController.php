<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadExcelFileRequest;
use App\Models\Brick;
use App\Models\Employee;
use App\Models\Territory;
use App\Services\BrickExcelService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BrickController extends Controller
{
    public function __construct(private BrickExcelService $excel) {}

    public function handleBricks(Request $request, Territory $territory, ?Brick $brick = null)
    {
        if ($request->isMethod('post')) {
            $brickIds = $request->input('bricks', []);

            if (!empty($brickIds)) {
                foreach (Brick::whereIn('code', $brickIds)->get() as $item) {
                    if (!$territory->bricks->contains($item)) {
                        $territory->bricks()->attach($item->id);
                    }
                }
            }

            return redirect()->back()->with('success', 'Bricks successfully assigned to territory!');
        }

        if ($request->isMethod('delete')) {
            if ($brick && $territory->bricks->contains($brick->id)) {
                $territory->bricks()->detach($brick->id);
                return redirect()->back()->with('success', 'Brick successfully detached from territory!');
            }
            return redirect()->back()->with('error', 'Brick is not attached to this territory.');
        }
    }

    public function formTemplate(Employee $employee)
    {
        $territory = $employee->employee_territory()->latest('assigned_at')->first();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('User Creation');

        $sheet->setCellValue('A1', 'User');            $sheet->setCellValue('A2', $employee->first_name . ' ' . $employee->last_name);
        $sheet->setCellValue('B1', 'Username');        $sheet->setCellValue('B2', $employee->email);
        $sheet->setCellValue('C1', 'Email');           $sheet->setCellValue('C2', $employee->email);
        $sheet->setCellValue('D1', 'FirstName');       $sheet->setCellValue('D2', $employee->first_name);
        $sheet->setCellValue('E1', 'LastName');        $sheet->setCellValue('E2', $employee->last_name);
        $sheet->setCellValue('F1', 'Territory Name');  $sheet->setCellValue('F2', $territory->territory_name);
        $sheet->setCellValue('G1', 'Parent Territory Name'); $sheet->setCellValue('G2', $territory->parent->territory_name);
        $sheet->setCellValue('H1', 'Division');        $sheet->setCellValue('H2', $territory->team);
        $sheet->setCellValue('I1', 'EmployeeNumber');  $sheet->setCellValue('I2', '');
        $sheet->setCellValue('J1', 'Country');         $sheet->setCellValue('J2', 'KAZAKHSTAN');
        $sheet->setCellValue('K1', 'CompanyName');     $sheet->setCellValue('K2', 'Nobel Pharma KZ');
        $sheet->setCellValue('L1', 'MobilePhone');     $sheet->setCellValue('L2', '');
        $sheet->setCellValue('M1', 'Manager Employee Number'); $sheet->setCellValue('M2', '');
        $sheet->setCellValue('N1', 'Manager Name');    $sheet->setCellValue('N2', $territory->parent->employee->first_name . ' ' . $territory->parent->employee->last_name);

        $this->excel->autoSizeSheet($sheet);
        $this->excel->addBricksSheet($spreadsheet, $territory->bricks ?? collect(), $territory->territory_name);

        return $this->excel->saveAndDownload(
            $spreadsheet,
            'OCE-P New User ' . $employee->first_name . ' ' . $employee->last_name . '.xlsx'
        );
    }

    public function assignBricks(Employee $employee, Territory $territory, Request $request)
    {
        $brickIds = $request->input('bricks', []);
        $selectedBricks = collect();

        if ($request->has('bricks')) {
            $selectedBricks = Brick::whereIn('code', $brickIds)->get();
            foreach ($selectedBricks as $brick) {
                if (!$territory->bricks->contains($brick->id)) {
                    $territory->bricks()->attach($brick->id);
                }
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'User');       $sheet->setCellValue('A2', $employee->full_name);
        $sheet->setCellValue('B1', 'Username');   $sheet->setCellValue('B2', $employee->email);
        $sheet->setCellValue('C1', 'Email');      $sheet->setCellValue('C2', $employee->email);
        $sheet->setCellValue('D1', 'Territory');  $sheet->setCellValue('D2', $territory->territory_name);
        $sheet->setCellValue('E1', 'Division');   $sheet->setCellValue('E2', $territory->team);
        $sheet->setCellValue('F1', 'Manager');    $sheet->setCellValue('F2', $territory->manager_id);

        $this->excel->autoSizeSheet($sheet);
        $this->excel->addBricksSheet($spreadsheet, $selectedBricks, $territory->territory_name);

        $this->excel->save($spreadsheet, 'excel/OCE-P New User ' . $employee->full_name . '.xlsx');

        return redirect()->back()->with('success', 'Completed template was created successfully!');
    }

    public function uploadBricks(UploadExcelFileRequest $request)
    {
        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();

            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

                Brick::firstOrCreate(
                    ['code' => $row[1]],
                    [
                        'country'         => $row[0] ?? null,
                        'description'     => $row[2] ?? null,
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
