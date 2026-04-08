<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeEvent;
use Illuminate\Http\Request;

class EmployeeCrudController extends Controller
{
    /**
     * Show the form for creating a new employee.
     *
     * @return \Illuminate\View\View
     */
    public function createEmployeeForm()
    {
        return view('create-edit-employee');
    }

    /**
     * Store a newly created employee.
     *
     * @param EmployeeStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createEmployee(EmployeeStoreRequest $request)
    {
        $incomingFields = $request->validated();

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
            'event_date' => $employee->hiring_date ?? now(),
        ]);

        return redirect()->route('employees.show', ['id' => $employee->id])
            ->with('success', 'Employee added successfully!');
    }

    /**
     * Display the specified employee.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showEmployee($id)
    {
        $employee = Employee::with(['tablets', 'territories', 'employee_territory', 'employee_tablet', 'credentials', 'events'])->findOrFail($id);
        $bricks = \App\Models\Brick::all();
        $selectedBricks = $employee->employee_territory()->latest('assigned_at')->first()->bricks ?? collect();
        $availableTablets = \App\Models\Tablet::free()->with('oldEmployee')->get();
        $availableTerritories = \App\Models\Territory::whereNull('employee_id')->with('oldEmployee')->get();

        $lastTerritory = $employee->employee_territory()->latest('assigned_at')->first();
        $lastTablet = $employee->employee_tablet()->withPivot('assigned_at', 'returned_at')->orderByDesc('assigned_at')->first();

        $territoriesHistory = $employee->employee_territory()->withPivot('assigned_at', 'unassigned_at', 'id')->orderByDesc('assigned_at')->get();
        $tabletHistories = \App\Models\EmployeeTablet::where('employee_id', $employee->id)->with(['tablet'])->orderByDesc('assigned_at')->get();

        $territories = $employee->employee_territory()->latest('assigned_at')->get()->map(function ($territory) use ($employee) {
            $territory->assignmentToRemove = \Illuminate\Support\Facades\DB::table('employee_territory')
                ->where('employee_id', $employee->id)
                ->where('territory_id', $territory->id)
                ->where('confirmed', 0)
                ->orderByDesc('id')
                ->first();
            return $territory;
        });

        $tablets = $employee->tablets->map(function ($tablet) use ($employee) {
            $tablet->pdfAssignment = \Illuminate\Support\Facades\DB::table('employee_tablet')
                ->where('employee_id', $employee->id)
                ->where('tablet_id', $tablet->id)
                ->select('id', 'pdf_path')
                ->orderByDesc('id')
                ->first();
            return $tablet;
        });

        $latestEvent = $employee->events()->latest('event_date')->first();
        $currentStatus = $latestEvent ? $latestEvent->event_type : null;

        return view('employee', compact('employee', 'availableTablets', 'availableTerritories', 'bricks', 'selectedBricks', 'territoriesHistory', 'tabletHistories', 'lastTerritory', 'lastTablet', 'currentStatus'));
    }

    /**
     * Show the form for editing the specified employee.
     *
     * @param Employee $employee
     * @return \Illuminate\View\View
     */
    public function showEditEmployee(Employee $employee)
    {
        return view('create-edit-employee', ['employee' => $employee]);
    }

    /**
     * Update the specified employee.
     *
     * @param EmployeeUpdateRequest $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actuallyEditEmployee(EmployeeUpdateRequest $request, Employee $employee)
    {
        $incomingFields = $request->validated();
        $employee->update($incomingFields);

        return redirect()->route('employees.show', ['id' => $employee->id])
            ->with('success', 'Employee edited successfully!');
    }

    /**
     * Remove the specified employee.
     *
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteEmployee(Employee $employee)
    {
        try {
            $employee->delete();
            return redirect('/')->with('success', 'Employee deleted successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect('/')->with('error', 'Cannot delete employee because there are related records.');
            }
            return redirect('/')->with('error', 'An error occurred while deleting the employee.');
        }
    }
}