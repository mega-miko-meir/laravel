<?php

namespace App\Http\Controllers;

use App\Http\Requests\TabletStoreRequest;
use App\Http\Requests\TabletUpdateDateRequest;
use App\Http\Requests\TabletUpdatePdfRequest;
use App\Http\Requests\TabletUpdateRequest;
use App\Models\Tablet;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeTablet;
use Illuminate\Support\Facades\DB;

class TabletController extends Controller
{
    public function updateDate(TabletUpdateDateRequest $request, $id)
    {
        $validated = $request->validated();

        DB::table('employee_tablet')
            ->where('id', $id)
            ->update([$validated['field_name'] => $validated['date_value']]);

        return back()->with('success', 'Дата обновлена');
    }

    public function updatePdf(TabletUpdatePdfRequest $request, $id)
    {
        $validated = $request->validated();

        $record = DB::table('employee_tablet')
            ->where('id', $id)
            ->first();

        if (!$record) {
            return back()->with('error', 'Запись не найдена');
        }

        // Загружаем новый файл
        $path = $request->file('pdf_value')
            ->store('employee_tablets', 'public');

        // Удаляем старый файл если есть
        if ($record->{$validated['field_name']}) {
            Storage::disk('public')
                ->delete($record->{$validated['field_name']});
        }

        // Обновляем поле
        DB::table('employee_tablet')
            ->where('id', $id)
            ->update([
                $validated['field_name'] => $path
            ]);

        return back()->with('success', 'PDF обновлен');
    }


    public function searchTablet(Request $request){
        $query = $request->input('search');
        $sort = $request->input('sort', 'hiring_date'); // По умолчанию сортируем
        $order = $request->input('order', 'desc'); // По умолчанию сортировка по возрастанию

        $activeOnly = $request->boolean('active_only');

        $tablets = Tablet::query()
        ->when($query, function ($q) use ($query) {
            $q->where(function ($sub) use ($query) {
                $sub->where('serial_number', 'like', "%$query%")
                    ->orWhere('invent_number', 'like', "%$query%")
                    ->orWhere('status', 'like', "%$query%")
                    ->orWhere('model', 'like', "%$query%")
                    ->orWhere('beeline_number', 'like', "%$query%")
                    ->orWhereHas('employees', function ($emp) use ($query) {
                        $emp->where('full_name', 'like', "%$query%");
                    });
            });
        })
        ->with([
            'latestAssignment.employee',
            'currentAssignment'
        ])
        ->get()
        ->sortByDesc(function ($tablet) {
            return optional($tablet->latestAssignment)->assigned_at;
        })
        ->values();

        $freeTablets = Tablet::free()
        // ->with('oldEmployee')
        ->get();


        $availableEmployees = Employee::whereHas('events', function ($query) {
            $query->whereIn('event_type', ['new', 'hired'])
                  ->whereRaw('event_date = (
                      SELECT MAX(event_date)
                      FROM employee_events
                      WHERE employee_events.employee_id = employees.id
                  )');
        })
        ->where(function ($query) {
            $query->whereDoesntHave('employee_tablet')
                  ->orWhereHas('employee_tablet', function ($subQuery) {
                      $subQuery->whereNotNull('returned_at')
                               ->whereRaw('assigned_at = (
                                   SELECT MAX(assigned_at)
                                   FROM employee_tablet
                                   WHERE employee_tablet.employee_id = employees.id
                               )');
                  });
        })
        ->orderBy('full_name', 'asc')
        ->get();

        // Количество сотрудников без планшета
        $count = $availableEmployees->count();




        return view('tablets', ['tablets' => $tablets, 'query' => $query, 'freeTablets' => $freeTablets, 'availableEmployees' => $availableEmployees, 'count' => $count]);
    }



    public function showTablet(Tablet $tablet)
    {
        $previousUsers = $tablet->employees()
        ->withPivot('assigned_at', 'returned_at', 'pdf_path', 'unassign_pdf', 'id', 'employee_id', 'tablet_id')
        ->orderByDesc('employee_tablet.assigned_at')
        ->get();

        $lastTablet = EmployeeTablet::where('employee_id', $employee->id ?? null)
        ->whereNull('returned_at') // Фильтруем только активные записи
        ->orderByDesc('assigned_at') // Берём последнюю по дате назначения
        ->first();

        // dd($previousUsers->first());

        $availableEmployees = Employee::whereHas('events', function ($query) {
            $query->whereIn('event_type', ['new', 'hired'])
                  ->whereRaw('event_date = (SELECT MAX(event_date) FROM employee_events WHERE employee_events.employee_id = employees.id) ');
        })
        ->where(function ($query) {
            $query->whereDoesntHave('employee_tablet') // Нет записей в employee_tablet
                  ->orWhereHas('employee_tablet', function ($subQuery) {
                      $subQuery->whereNotNull('returned_at')
                               ->whereRaw('assigned_at = (SELECT MAX(assigned_at) FROM employee_tablet WHERE employee_tablet.employee_id = employees.id)');
                  });
        })
        ->orderBy('full_name', 'asc')
        ->get();


        return view('show-tablet', compact('tablet', 'previousUsers', 'lastTablet', 'availableEmployees'));
    }


    public function createTabletForm(){
        return view('create-edit-tablet');
    }

    public function editTabletForm(Tablet $tablet){
        return view('create-edit-tablet', ['tablet' => $tablet]);
    }


    public function createTablet(TabletStoreRequest $request){
        $incomingFields = $request->validated();

        $incomingTablet = Tablet::where('serial_number', $incomingFields['serial_number'])->first();

        if($incomingTablet) {
            return redirect()->back()->with('error', 'Такой iPad уже существует');
        }

        $tablet = Tablet::create($incomingFields);

        return redirect()->route('tablets.show', ['tablet' => $tablet])->with('success', 'Планшет добавлен успешно!');

    }

    public function editTablet(TabletUpdateRequest $request, Tablet $tablet)
    {
        $incomingFields = $request->validated();

        $tablet->update($incomingFields);

        return redirect()->route('tablets.show', ['tablet' => $tablet])->with('success', 'Данные успешно обновлены!');
    }

}

