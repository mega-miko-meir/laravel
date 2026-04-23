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
use Illuminate\Support\Facades\Storage;
use App\Services\TabletExportService;

class TabletController extends Controller
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Сотрудники которые могут быть ответственными:
     * активны (hired/return_from_leave) и роль территории не Rep и не Product.
     */
    private function getResponsibles()
    {
        return Employee::whereHas('latestEvent', function ($q) {
                $q->whereIn('event_type', ['hired', 'return_from_leave']);
            })
            ->whereHas('employeeTerritoryRecords', function ($q) {
                $q->whereNull('unassigned_at')
                ->whereHas('territory', function ($q2) {
                    $q2->whereNotIn('role', ['Rep', 'Product']);
                });
            })
            ->orderBy('full_name')
            ->get();
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    public function createTabletForm()
    {
        $responsibles = $this->getResponsibles();

        return view('create-edit-tablet', compact('responsibles'));
    }

    public function editTabletForm(Tablet $tablet)
    {
        $responsibles = $this->getResponsibles();

        return view('create-edit-tablet', compact('tablet', 'responsibles'));
    }

    public function createTablet(TabletStoreRequest $request)
    {
        $incomingFields = $request->validated();

        $incomingTablet = Tablet::where('serial_number', $incomingFields['serial_number'])->first();

        if ($incomingTablet) {
            return redirect()->back()->with('error', 'Такой iPad уже существует');
        }

        $tablet = Tablet::create($incomingFields);

        return redirect()->route('tablets.show', ['tablet' => $tablet])
            ->with('success', 'Планшет добавлен успешно!');
    }

    public function editTablet(TabletUpdateRequest $request, Tablet $tablet)
    {
        $incomingFields = $request->validated();

        $tablet->update($incomingFields);

        return redirect()->route('tablets.show', ['tablet' => $tablet])
            ->with('success', 'Данные успешно обновлены!');
    }

    // -------------------------------------------------------------------------
    // Search & show
    // -------------------------------------------------------------------------

    public function searchTablet(Request $request)
    {
        $query      = $request->input('search');
        $activeOnly = $request->boolean('active_only');

        $tablets = Tablet::query()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {

                    // 1. Приводим к строке, только если это не массив
                    $searchString = is_array($query) ? '' : strtolower($query);

                    // 2. Проверяем условия
                    if ($searchString === 'damaged' || (is_array($query) && in_array('damaged', $query))) {
                        $sub->whereIn('status', ['damaged', 'lost']);
                    } else {
                        // Если это массив (и не damaged), ищем точное совпадение по статусам
                        if (is_array($query)) {
                            $sub->whereIn('status', $query);
                        } else {
                            // Стандартный текстовый поиск
                            $sub->where('serial_number', 'like', "%$query%")
                                ->orWhere('invent_number', 'like', "%$query%")
                                ->orWhere('status', 'like', "%$query%")
                                ->orWhere('model', 'like', "%$query%")
                                ->orWhere('beeline_number', 'like', "%$query%")
                                ->orWhereHas('employees', function ($emp) use ($query) {
                                    $emp->where('full_name', 'like', "%$query%");
                                });
                        }
                    }
                });
            })
            ->with([
                'latestAssignment.employee',
                'currentAssignment',
                'responsible',
            ])
            ->get()
            ->sortByDesc(fn($tablet) => optional($tablet->latestAssignment)->assigned_at)
            ->values();

        $freeTablets = Tablet::free()->get();

        $availableEmployees = Employee::whereHas('events', function ($q) {
                $q->whereIn('event_type', ['new', 'hired'])
                ->whereRaw('event_date = (
                    SELECT MAX(event_date)
                    FROM employee_events
                    WHERE employee_events.employee_id = employees.id
                )');
            })
            ->where(function ($q) {
                $q->whereDoesntHave('employee_tablet')
                ->orWhereHas('employee_tablet', function ($subQuery) {
                    $subQuery->whereNotNull('returned_at')
                            ->whereRaw('assigned_at = (
                                SELECT MAX(assigned_at)
                                FROM employee_tablet
                                WHERE employee_tablet.employee_id = employees.id
                            )');
                });
            })
            ->orderBy('full_name')
            ->get();

        $count = $availableEmployees->count();

        return view('tablets', compact('tablets', 'query', 'freeTablets', 'availableEmployees', 'count'));
    }

    public function exportToExcel(Request $request)
    {
        return app(TabletExportService::class)->exportToExcel($request);
    }

    public function showTablet(Tablet $tablet)
    {
        $tablet->load('responsible');

        $previousUsers = $tablet->employees()
            ->withPivot('assigned_at', 'returned_at', 'pdf_path', 'unassign_pdf', 'id', 'employee_id', 'tablet_id')
            ->orderByDesc('employee_tablet.assigned_at')
            ->get();

        $lastTablet = EmployeeTablet::where('tablet_id', $tablet->id)
            ->whereNull('returned_at')
            ->orderByDesc('assigned_at')
            ->first();

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
            ->orderBy('full_name')
            ->get();

        return view('show-tablet', compact('tablet', 'previousUsers', 'lastTablet', 'availableEmployees'));
    }

    // -------------------------------------------------------------------------
    // Date & PDF updates
    // -------------------------------------------------------------------------

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

        $path = $request->file('pdf_value')
            ->store('employee_tablets', 'public');

        if ($record->{$validated['field_name']}) {
            Storage::disk('public')->delete($record->{$validated['field_name']});
        }

        DB::table('employee_tablet')
            ->where('id', $id)
            ->update([$validated['field_name'] => $path]);

        return back()->with('success', 'PDF обновлен');
    }
}
