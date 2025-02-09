<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Brick;
use App\Models\Tablet;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\Models\EmployeeTablet;
use App\Models\EmployeeTerritory;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\View\Components\employee as ComponentsEmployee;

class EmployeeController extends Controller
{

    public function exportToExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Заголовки
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Имя');
        $sheet->setCellValue('C1', 'Фамилия');
        $sheet->setCellValue('D1', 'Имэйл');

        // Получаем данные из БД
        $employees = DB::table('employees')->get();
        // return $employees;
        $row = 2;

        foreach ($employees as $employee) {
            $sheet->setCellValue("A$row", $employee->id);
            $sheet->setCellValue("B$row", $employee->first_name);
            $sheet->setCellValue("C$row", $employee->last_name);
            $sheet->setCellValue("D$row", $employee->email);
            $row++;
        }
        // Сохраняем во временный файл
        $filePath = storage_path('employees.xlsx');
        // $writer = new Xlsx($spreadsheet);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        // Возвращаем файл пользователю
        return response()->download($filePath)->deleteFileAfterSend(true);
    }



    public function uploadEmployees(Request $request)
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

    // public function unassignTerritory(Employee $employee, Territory $territory){

    //     $territory->old_employee_id = $employee->full_name;
    //     $territory->employee()->dissociate();
    //     $territory->save();

    //     // Filling in employee_territory table
    //     $employee->employee_territory()->updateExistingPivot($territory->id, ['unassigned_at' => now()]);

    //     return redirect()->back()->with('success', 'Tablet successfully unassigned from the employee.');
    // }

    public function unassignTerritory(Employee $employee, Territory $territory)
    {
        $assignmentToRemove = DB::table('employee_territory')
            ->where('employee_id', $employee->id)
            ->where('territory_id', $territory->id)
            ->where('confirmed', 0)
            ->orderByDesc('id') // Сортируем по убыванию ID
            ->first();

        // dd($assignmentToRemove2);
        $territory->employee()->dissociate();
        $territory->save();

        // if ($assignment) {
            // Отвязываем территорию от сотрудника

            // Если запись существует и confirmed = false, удаляем только эту строку
            if ($assignmentToRemove) {
                DB::table('employee_territory')
                    ->where('id', $assignmentToRemove->id) // Указываем ID найденной записи
                    ->delete();



                return redirect()->back()->with('success', 'Territory unassigned and removed due to unconfirmed status.');
            } else {
                // Обновляем колонку unassigned_at только для этой строки
                $employee->employee_territory()->updateExistingPivot($territory->id, ['unassigned_at' => now()]);
                // Обновляем old_employee_id на территории
                $territory->old_employee_id = $employee->full_name;
                $territory->save();
                return redirect()->back()->with('success', 'Territory successfully unassigned from the employee.');
            }
        // } else {
        //     // Если привязки не существует
        //     return redirect()->back()->with('error', 'Assignment not found.');
        // }
    }


    public function assignTerritory(Request $request, Employee $employee){
        $territory = Territory::findOrFail($request->input('territory_id'));
        $territory->employee()->associate($employee);
        $territory->save();

        // Filling in employee_territory table
        $employee->employee_territory()->attach($territory->id, ['assigned_at' => now()]);

        return redirect()->back()->with('success', 'Territory successfully assigned to the employee.');
    }


    public function confirmTerritory(Employee $employee, Territory $territory){
        $assignment = $employee->employee_territory()->where('territory_id', $territory->id)->first();
        if (!$assignment) {
            return redirect()->back()->with('error', 'Territory assignment not found.');
        }

        // Обновляем запись
        $employee->employee_territory()->updateExistingPivot($territory->id, ['confirmed' => true]);

        return redirect()->back()->with('success', 'Territory confirmed.');
    }


    public function searchEmployee(Request $request){

        $query = $request->input('search');
        $employees = Employee::where('first_name', 'like', "%$query%")
            ->orWhere('full_name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhereHas('territories', function ($q) use ($query) {
                $q->where('team', 'like', "%$query%")
                ->orWhere('city', 'like', "%$query%");
            })
            ->get();
        return view('home', ['employees' => $employees, 'query' => $query]);
    }

    public function deleteEmployee(Employee $employee)
{
    try {
        $employee->delete();
        return redirect('/')->with('success', 'Employee deleted successfully!');
    } catch (\Illuminate\Database\QueryException $e) {
        // Проверяем, вызвана ли ошибка нарушением внешнего ключа
        if ($e->getCode() == '23000') {
            return redirect('/')->with('error', 'Cannot delete employee because there are related records.');
        }
        // Для других ошибок
        return redirect('/')->with('error', 'An error occurred while deleting the employee.');
    }
}

    public function actuallyEditEmployee(Request $request, Employee $employee){
            $incomingFields = $request->validate([
            'full_name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'team' => 'nullable',
            'city' => 'nullable',
            'position' => 'nullable',
            'hiring_date' => 'nullable',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
        ]);

        // Обновление данных модели
        $employee->update($incomingFields);

        // return redirect('/')->with('success', 'Employee updated successfully!');
        return back()->with('success', 'Данные успешно загружены!');


    }


    public function showEditEmployee(Employee $employee){
        return view('edit-employee', ['employee' => $employee]);
    }

    public function createEmployeeForm(){
        return view('create-employee');
    }

    public function createEmployee(Request $request){
        $incomingFields = $request->validate(
            [
                'full_name' => 'required',
                'first_name' => 'nullable',
                'last_name' => 'nullable',
                'birth_date' => 'nullable',
                'email' => 'required|email|unique:employees,email',
                'hiring_date' => 'nullable',
                'position' => 'nullable',
                'status' => 'nullable',

            ]
        );

        Employee::create($incomingFields);
        return redirect('/')->with('success', 'Employee added successfully!');
    }

    public function showEmployee($id){
        $employee = Employee::with(['tablets', 'territories', 'employee_territory', 'employee_tablet'])->findOrFail($id);
        // $oldEmployee = Employee::with('tablets')->findOrFail($oldEmployeeId);
        $bricks = Brick::all();
        // $territories = $employee->territories();
        $selectedBricks = $employee->territories->first()->bricks ?? collect();
        $availableTablets = Tablet::whereNull('employee_id')->with('oldEmployee')->get();
        $availableTerritories = Territory::whereNull('employee_id')->with('oldEmployee')->get();
        $territoriesHistory = EmployeeTerritory::where('employee_id', $employee->id)
        ->whereNotNull('unassigned_at')
        ->with(['territory']) // Подгружаем данные из модели Territory, если есть связь
        ->orderByDesc('assigned_at') // Сортировка по дате изменения
        ->get();
        // dd($territoriesHistory);
        $tabletHistories = EmployeeTablet::where('employee_id', $employee->id)
        ->with(['tablet'])
        ->whereNotNull('returned_at') // Только подтвержденные записи
        ->orderByDesc('assigned_at')
        ->get();

        $territories = $employee->territories->map(function ($territory) use ($employee) {
            $territory->assignmentToRemove = DB::table('employee_territory')
                ->where('employee_id', $employee->id)
                ->where('territory_id', $territory->id)
                ->where('confirmed', 0)
                ->orderByDesc('id') // Берем последнюю запись
                ->first();

            return $territory;
        });

        $tablets = $employee->tablets->map(function ($tablet) use ($employee) {
            $tablet->pdfAssignment = DB::table('employee_tablet')
                ->where('employee_id', $employee->id)
                ->where('tablet_id', $tablet->id)
                ->select('id', 'pdf_path')
                ->orderByDesc('id') // Берем только поле pdf_path
                ->first();

            return $tablet;
        });

        // $tablets = $employee->employee_tablet->each(function ($tablet) {
        //     $tablet->pdfAssignment = $tablet->pivot->pdf_path ?? null;
        // });

        // dd($tablets);


        return view('employee', compact('employee', 'availableTablets', 'availableTerritories', 'bricks', 'selectedBricks', 'territoriesHistory', 'tabletHistories'));


        // return view('employee.show', compact('employee', 'availableTablets'));
    }
}
