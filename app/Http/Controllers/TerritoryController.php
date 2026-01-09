<?php

namespace App\Http\Controllers;

use App\Models\Brick;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\Models\EmployeeEvent;
use App\Models\EmployeeTerritory;
use Illuminate\Support\Facades\DB;
use App\View\Components\territory as ComponentsTerritory;

class TerritoryController extends Controller
{
    public function unassignTerritory(Employee $employee, Territory $territory, Request $request)
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
                $employee->employee_territory()->updateExistingPivot($territory->id, ['unassigned_at' => $request->input('unassigned_at')]);
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
        // $request->validate([
        //     'assigned_at' => 'date'
        // ]);
        $territory = Territory::findOrFail($request->input('territory_id'));
        $territory->employee()->associate($employee);
        $territory->save();

        // Filling in employee_territory table
        $employee->employee_territory()->attach($territory->id, ['assigned_at' => $request->input('assigned_at')]);

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


    public function searchTerritory(Request $request){
        $query = $request->input('search');
        $sort = $request->input('sort', 'territory_name');
        $order = $request->input('order', 'asc');

        $territories = Territory::where('territory_name', 'like', "%$query%")
            ->orWhere('city', 'like', "%$query%")
            ->orWhere('department', 'like', "%$query%")
            ->orWhere('manager_id', 'like', "%$query%")
            ->orWhereHas('employees', function ($q) use ($query) {
                $q->where('full_name', 'like', "%$query%");
            })
            ->orderBy($sort, $order)
            ->get();


        return view('territories', ['territories' => $territories, 'query' => $query, 'sort' => $sort, 'order' => $order]);
    }

    public function showTerritory(Territory $territory)
    {
        $employeeTerritory = EmployeeTerritory::where('territory_id', $territory->id)
        ->whereNull('unassigned_at')
        ->latest('assigned_at') // Берем последнюю запись по дате назначения
        ->first();

        $employee = $employeeTerritory ? $employeeTerritory->employee : null;

        // $employee = $territory->employee;
        $bricks = Brick::all();
        // $selectedBricks = $employee->territories->first()->bricks ?? collect();
        $selectedBricks = optional($employee?->territories->first())->bricks ?? collect();

        $previousUsers = $territory->employees()
        ->withPivot('assigned_at', 'unassigned_at', 'id')
        ->orderByDesc('employee_territory.assigned_at')
        ->get();

        // dd($employee);
        // $lastTerritory = $employee->employee_territory()
        // ->withPivot('assigned_at', 'unassigned_at')
        // ->orderByDesc('assigned_at')
        // ->first();

        $lastTerritory = EmployeeTerritory::where('employee_id', $employee->id ?? null)
        ->whereNull('unassigned_at') // Фильтруем только активные записи
        ->orderByDesc('assigned_at') // Берём последнюю по дате назначения
        ->first();


        $availableEmployees = Employee::whereHas('events', function ($query) {
            $query->whereIn('event_type', ['new', 'hired'])
                  ->whereRaw('event_date = (SELECT MAX(event_date) FROM employee_events WHERE employee_events.employee_id = employees.id)');
        })
        ->where(function ($query) {
            $query->whereDoesntHave('employee_territory') // Нет записей в employee_territory
                  ->orWhereHas('employee_territory', function ($subQuery) {
                      $subQuery->whereNotNull('unassigned_at')
                               ->whereRaw('assigned_at = (SELECT MAX(assigned_at) FROM employee_territory WHERE employee_territory.employee_id = employees.id)');
                  });
        })
        ->orderBy('full_name', 'asc')
        ->get();







        // $availableEmployees = Employee::whereIn('status', ['active', 'new']) // Статус "active" или "new"
        // ->where(function ($query) {
        //     $query->whereDoesntHave('employee_territory') // Сотрудники, у которых нет записей в employee_territory
        //           ->orWhereHas('employee_territory', function ($subQuery) {
        //               $subQuery->whereNotNull('unassigned_at') // Последняя запись с unassigned_at != null
        //                   ->whereRaw('assigned_at = (SELECT MAX(assigned_at) FROM employee_territory WHERE employee_territory.employee_id = employees.id)');
        //           });
        // })
        // ->orderBy('full_name', 'asc')
        // ->get();


        // $availableEmployees = Employee::whereNull('firing_date') // Сотрудники без даты увольнения
        // ->where(function ($query) {
        //     $query->whereDoesntHave('employee_territory') // Исключаем тех, у кого вообще нет привязки
        //             ->orWhereHas('employee_territory', function ($subQuery) {
        //                 $subQuery->whereNotNull('unassigned_at'); // Берем только тех, у кого есть отвязанная территория
        //             });
        // })
        // ->get();


        return view('show-territory', compact('territory', 'previousUsers', 'employee', 'bricks', 'selectedBricks', 'availableEmployees', 'lastTerritory'));
    }

    public function createTerritoryForm(){
        $parentTerritories = Territory::with('employee')
            ->where('role', 'RM')
            ->get();


        return view('create-edit-territory', compact('parentTerritories'));
    }


    public function editTerritoryForm(Territory $territory){
        $parentTerritories = Territory::with('employee')
        ->where('role', 'RM')
        ->get();
        return view('create-edit-territory', ['territory' => $territory, 'parentTerritories' => $parentTerritories]);
    }




    public function createTerritory(Request $request){
        $incomingFields = $request->validate([
            'territory' => 'required',
            'territory_name' => 'required',
            'department' => 'required',
            'team' => 'required',
            'role' => 'required',
            'city' => 'required',
            'manager_id' => 'nullable|integer',
            'old_employee_id' => 'nullable|string',
            'parent_territory_id' => 'nullable|string'
        ]);

        $existingTerritory = Territory::where('territory_name', $incomingFields['territory_name'])->first();

        if ($existingTerritory) {
            return redirect()->back()->with('error', 'Такая территория уже существует!');
        }

        Territory::create($incomingFields);

        // return view('territory.create', [
        //     'territory' => null, // Для формы создания новой территории
        //     'territories' => Territory::all(), // Передаем все территории
        // ]);

        return redirect()->back()->with('success', 'Territory added successfully!');
    }

    public function editTerritory(Request $request, Territory $territory)
    {
        $incomingFields = $request->validate([
            'territory' => 'required|unique:territories,territory_name,' . $territory->id,
            'territory_name' => 'required|unique:territories,territory_name,' . $territory->id,
            'department' => 'required',
            'team' => 'required',
            'role' => 'required',
            'city' => 'required',
            'manager_id' => 'nullable|integer',
            'old_employee_id' => 'nullable|string',
            'parent_territory_id' => 'nullable|string'
        ]);

        $territory->update($incomingFields);

        return back()->with('success', 'Данные успешно обновлены!');
        // return view('territories.create', [
        //     'territory' => $territory, // Передаем текущую территорию
        //     'territories' => Territory::where('id', '!=', $territory->id)->get(), // Исключаем текущую из списка
        // ]);
    }




}
