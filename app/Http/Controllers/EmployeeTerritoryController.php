<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeTerritoryUpdateDateRequest;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeTerritoryController extends Controller
{
    public function updateDate(EmployeeTerritoryUpdateDateRequest $request, $id)
    {
        $validated = $request->validated();

        DB::table('employee_territory')
            ->where('id', $id)
            ->update([$validated['field_name'] => $validated['date_value']]);

        return back()->with('success', 'Дата обновлена');
    }

    public function assignEmployee(Request $request, Territory $territory)
    {
        // Найти сотрудника по переданному ID
        $employee = Employee::findOrFail($request->input('employee_id'));

        // Привязать сотрудника к территории
        $territory->employee()->associate($employee);
        $territory->save();

        // Запись в таблицу `employee_territory`
        $employee->employee_territory()->attach($territory->id, ['assigned_at' => $request->input('assigned_at')]);

        return redirect()->back()->with('success', 'Employee successfully assigned to the territory.');
    }
}
