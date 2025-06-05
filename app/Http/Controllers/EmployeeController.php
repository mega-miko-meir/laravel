<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Brick;
use App\Models\Tablet;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\Models\EmployeeEvent;
use App\Models\EmployeeTablet;
use App\Models\EmployeeTerritory;
use App\Models\EmployeeCredential;
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
        $sheet->setCellValue('E1', 'Группа');
        $sheet->setCellValue('F1', 'Департамент');

        // Получаем данные из БД
        $employees = Employee::with('territories')->get();
        // return $employees;
        $row = 2;

        foreach ($employees as $employee) {
            $sheet->setCellValue("A$row", $employee->id);
            $sheet->setCellValue("B$row", $employee->first_name);
            $sheet->setCellValue("C$row", $employee->last_name);
            $sheet->setCellValue("D$row", $employee->email);
            $sheet->setCellValue("E$row", $employee->territories->first()->team ?? '');
            $sheet->setCellValue("F$row", $employee->territories->first()->department ?? '');
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


    // This method should be rewritten for employees to upload
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



    public function index(Request $request)
    {
        $activeOnly = $request->query('active_only', 1);
        $sort = $request->input('sort', 'latest_event_date'); // Сортируем по последнему событию
        $order = $request->input('order', 'desc');

        // Подзапрос для получения последнего события каждого сотрудника
        $employees = Employee::leftJoinSub(
                DB::table('employee_events')
                    ->select('employee_id', DB::raw('MAX(event_date) as latest_event_date'))
                    ->groupBy('employee_id'),
                'latest_events',
                'employees.id',
                '=',
                'latest_events.employee_id'
            )
            ->select('employees.*', 'latest_events.latest_event_date') // Используем latest_event_date
            ->when($activeOnly == 1, function ($query) {
                return $query->whereIn('employees.status', ['active', 'new']);
            })
            ->orderBy($sort === 'latest_event_date' ? 'latest_events.latest_event_date' : 'employees.'.$sort, $order)
            ->orderBy('employees.full_name')
            ->get();

        // Если это AJAX-запрос, рендерим только компонент `x-employee-card`
        if ($request->ajax()) {
            return view('components.employee-card', [
                'employees' => $employees,
                'sort' => $sort,
                'order' => $order
            ])->render();
        }

        // Если обычный запрос, возвращаем полную страницу
        return view('home', compact('employees', 'sort', 'order', 'activeOnly'));
    }




    public function searchEmployee(Request $request)
    {
        $query = $request->input('search');

        $sort = $request->input('sort', 'latest_event_date'); // По умолчанию сортируем по дате последнего события
        $order = $request->input('order', 'desc'); // По умолчанию сортировка по убыванию
        $activeOnly = $request->input('active_only', 1);

        // Подзапрос для получения последнего события каждого сотрудника
        $employees = Employee::leftJoinSub(
                DB::table('employee_events')
                    ->select('employee_id', DB::raw('MAX(event_date) as latest_event_date'))
                    ->groupBy('employee_id'),
                'latest_events',
                'employees.id',
                '=',
                'latest_events.employee_id'
            )
            ->select('employees.*', 'latest_events.latest_event_date') // Выбираем сотрудников + дату последнего события
            ->where(function ($q) use ($query) {
                $q->where('employees.first_name', 'like', "%$query%")
                ->orWhere('employees.full_name', 'like', "%$query%")
                ->orWhere('employees.last_name', 'like', "%$query%")
                ->orWhere('employees.email', 'like', "%$query%")
                ->orWhere('employees.status', 'like', "%$query%")
                ->orWhereHas('territories', function ($q) use ($query) {
                    $q->where('team', 'like', "%$query%")
                        ->orWhere('city', 'like', "%$query%");
                });
            })
            ->when($activeOnly == 1, function ($q) {
                $q->whereIn('employees.status', ['active', 'hired', 'new', 'dismissed', 'maternity_leave', 'changed_position']);
            })
            ->orderBy($sort, $order)
            ->get();

        return view('home', [
            'employees' => $employees,
            'query' => $query,
            'sort' => $sort,
            'order' => $order,
            'activeOnly' => $activeOnly,
        ]);
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
        return back()->with('success', 'Данные успешно обновлены!');
    }


    public function showEditEmployee(Employee $employee){
        return view('create-edit-employee', ['employee' => $employee]);
    }

    public function createEmployeeForm(){
        return view('create-edit-employee');
    }

    public function createEmployee(Request $request)
    {
        $incomingFields = $request->validate([
            'full_name' => 'required',
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'birth_date' => 'nullable',
            'email' => 'required|email|unique:employees,email',
            'hiring_date' => 'nullable|date',
            'position' => 'nullable',
            'status' => 'nullable',
        ]);

        // Устанавливаем статус по умолчанию "new"
        $incomingFields['status'] = 'new';

        // Если дата найма не передана, устанавливаем текущую дату
        $incomingFields['hiring_date'] = $incomingFields['hiring_date'] ?? now();

        // Создаём сотрудника
        $employee = Employee::create($incomingFields);

        // Создаём событие "new"
        EmployeeEvent::create([
            'employee_id' => $employee->id,
            'event_type' => 'hired',
            'event_date' => $employee->hiring_date ?? now(), // Используем дату найма
        ]);

        return redirect('/')->with('success', 'Employee added successfully!');
    }

    public function showEmployee($id){
        $employee = Employee::with(['tablets', 'territories', 'employee_territory', 'employee_tablet', 'credentials', 'events'])->findOrFail($id);
        // $oldEmployee = Employee::with('tablets')->findOrFail($oldEmployeeId);
        $bricks = Brick::all();
        // $territories = $employee->territories();
        $selectedBricks = $employee->territories->first()->bricks ?? collect();
        // $availableTablets = Tablet::whereNull('employee_id')->with('oldEmployee')->get();
        $availableTablets = Tablet::whereHas('employees', function ($query) {
            $query->whereNotNull('returned_at')
                  ->whereRaw('assigned_at = (SELECT MAX(assigned_at) FROM employee_tablet WHERE employee_tablet.tablet_id = tablets.id)');
        })
        ->with('oldEmployee')
        ->get();


        $availableTerritories = Territory::whereNull('employee_id')->with('oldEmployee')->get();
        // $territoriesHistory = EmployeeTerritory::where('employee_id', $employee->id)
        // // ->whereNotNull('unassigned_at')
        // ->with(['territory']) // Подгружаем данные из модели Territory, если есть связь
        // ->orderByDesc('assigned_at') // Сортировка по дате изменения
        // ->get();
        // dd($territoriesHistory);

        $lastTerritory = $employee->employee_territory()
        ->withPivot('assigned_at', 'unassigned_at')
        ->orderByDesc('assigned_at')
        ->first();

        $lastTablet = $employee->employee_tablet()
        ->withPivot('assigned_at', 'returned_at')
        ->orderByDesc('assigned_at')
        ->first();



        $territoriesHistory = $employee->employee_territory()
        ->withPivot('assigned_at', 'unassigned_at', 'id')
        ->orderByDesc('assigned_at')
        ->get();

        $tabletHistories = EmployeeTablet::where('employee_id', $employee->id)
        ->with(['tablet'])
        // ->whereNotNull('returned_at') // Только подтвержденные записи
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

        $latestEvent = $employee->events()->latest('event_date')->first();
        $currentStatus = $latestEvent ? $latestEvent->event_type : null;

        return view('employee', compact('employee', 'availableTablets', 'availableTerritories', 'bricks', 'selectedBricks', 'territoriesHistory', 'tabletHistories', 'lastTerritory', 'lastTablet', 'currentStatus'));


        // return view('employee.show', compact('employee', 'availableTablets'));
    }
}
