<?php

namespace App\Http\Controllers;

use App\Models\Tablet;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeTablet;
use Illuminate\Support\Facades\DB;

class TabletController extends Controller
{
    public function updateDate(Request $request, $id)
    {
        $request->validate([
            'date_value' => 'required|date',
            'field_name' => 'required|in:assigned_at,returned_at',
        ]);

        DB::table('employee_tablet')
            ->where('id', $id)
            ->update([$request->field_name => $request->date_value]);

        return back()->with('success', 'Дата обновлена');
    }

    public function searchTablet(Request $request){
        $query = $request->input('search');
        $sort = $request->input('sort', 'hiring_date'); // По умолчанию сортируем
        $order = $request->input('order', 'desc'); // По умолчанию сортировка по возрастанию

        $tablets = Tablet::where('serial_number', 'like', "%$query%")
            ->orWhere('invent_number', 'like', "%$query%")
            ->orWhere('beeline_number', 'like', "%$query%")
            ->orWhereHas('employees', function ($q) use ($query) {
                $q->where('full_name', 'like', "%$query%");
            })
            ->with(['currentAssignment' => function ($q) {
                $q->select('tablet_id', 'pdf_path', 'unassign_pdf')
                ->orderByDesc('id');
            }])
            ->get();

        return view('tablets', ['tablets' => $tablets, 'query' => $query]);
    }



    public function showTablet(Tablet $tablet)
    {
        $previousUsers = $tablet->employees()
        ->withPivot('assigned_at', 'returned_at', 'pdf_path', 'unassign_pdf', 'id')
        ->orderByDesc('employee_tablet.assigned_at')
        ->get();

        $lastTablet = EmployeeTablet::where('employee_id', $employee->id ?? null)
        ->whereNull('returned_at') // Фильтруем только активные записи
        ->orderByDesc('assigned_at') // Берём последнюю по дате назначения
        ->first();

        // dd($previousUsers->first());

        $availableEmployees = Employee::whereHas('events', function ($query) {
            $query->whereIn('event_type', ['new', 'hired'])
                  ->whereRaw('event_date = (SELECT MAX(event_date) FROM employee_events WHERE employee_events.employee_id = employees.id)');
        })
        ->where(function ($query) {
            $query->whereDoesntHave('employee_tablet') // Нет записей в employee_territory
                  ->orWhereHas('employee_tablet', function ($subQuery) {
                      $subQuery->whereNotNull('returned_at')
                               ->whereRaw('assigned_at = (SELECT MAX(assigned_at) FROM employee_territory WHERE employee_territory.employee_id = employees.id)');
                  });
        })
        ->get();


        return view('show-tablet', compact('tablet', 'previousUsers', 'lastTablet', 'availableEmployees'));
    }


}

