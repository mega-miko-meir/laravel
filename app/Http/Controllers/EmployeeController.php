<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeCredentialsUpdateRequest;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeCredential;
use App\Models\EmployeeEvent;
use App\Services\TeamService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
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

    /**
     * Display a listing of employees.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $activeOnly = $request->input('active_only', 1);
        $sort = $request->input('sort', 'event_date');
        $order = $request->input('order', 'asc');

        $employees = \Illuminate\Support\Facades\DB::table('employees as e')
            ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
            ->where('ev.event_type', 'hired')
            ->whereRaw('ev.id = (
                SELECT ee.id FROM employee_events ee
                WHERE ee.employee_id = ev.employee_id
                ORDER BY ee.event_date DESC
                LIMIT 1
            )')
            ->select('e.*', 'ev.event_type', 'ev.event_date')
            ->orderBy('ev.event_date', 'DESC')
            ->get();

        if ($request->ajax()) {
            return view('components.employee-card', compact('employees', 'sort', 'order'))->render();
        }

        return view('home', compact('employees', 'sort', 'order', 'activeOnly'));
    }

    /**
     * Search for employees.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function searchEmployee(Request $request)
    {
        $query = $request->input('search');
        $sort = $request->input('sort', 'latest_event_date');
        $order = $request->input('order', 'desc');
        $activeOnly = $request->input('active_only', 1);

        $queryNormalized = strtolower(trim($query));
        $isRoleSearch = in_array($queryNormalized, ['rm', 'rep', 'ffm']);

        $employees = Employee::with(['latestEvent', 'territories'])
            ->where(function ($q) use ($query, $queryNormalized, $isRoleSearch) {
                if (!$query) {
                    return;
                }

                if ($isRoleSearch) {
                    $q->whereHas('territories', function ($q2) use ($queryNormalized) {
                        $q2->whereRaw('LOWER(role) = ?', [$queryNormalized]);
                    });
                    return;
                }

                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('full_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('position', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhereHas('territories', function ($q2) use ($query) {
                        $q2->where('team', 'like', "{$query}%")
                            ->orWhere('city', 'like', "%{$query}%");
                    })
                    ->orWhereHas('latestEvent', function ($q3) use ($query) {
                        $q3->where('event_type', 'like', "%{$query}%");
                    });
            })
            ->when($activeOnly == 1, function ($q) {
                $q->active();
            })
            ->get();

        $employees = $employees->when(
            $order === 'desc',
            fn($c) => $c->sortByDesc(fn($e) =>
                $sort === 'latest_event_date'
                    ? optional($e->latestEvent)->event_date
                    : data_get($e, $sort)
            ),
            fn($c) => $c->sortBy(fn($e) =>
                $sort === 'latest_event_date'
                    ? optional($e->latestEvent)->event_date
                    : data_get($e, $sort)
            )
        );

        if ($request->ajax()) {
            return view('components.employee-card', compact('employees'))->render();
        }

        return view('home', [
            'employees' => $employees,
            'query' => $query,
            'sort' => $sort,
            'order' => $order,
            'activeOnly' => $activeOnly,
        ]);
    }

    /**
     * Display the user's team.
     *
     * @return \Illuminate\View\View
     */


    public function myTeam(TeamService $teamService)
    {
        $ffms = $teamService->getTeamStructure();

        $productTerritories = \App\Models\Territory::where('role', 'Product')
            ->with(['employeeTerritories.employee.latestEvent'])
            ->orderBy('department', 'desc')
            ->orderBy('team')
            ->orderBy('city')
            ->get()
            ->groupBy(fn($t) => $t->department ?? 'Без департамента');

        return view('my-team', compact('ffms', 'productTerritories'));
    }



    /**
     * Update employee credentials.
     *
     * @param EmployeeCredentialsUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCredentials(EmployeeCredentialsUpdateRequest $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $validated = $request->validated();

        // Проверяем, есть ли уже такой логин
        $credential = EmployeeCredential::where('employee_id', $employee->id)
            ->where('system', $validated['system'])
            ->first();

        $payload = [
            'user_name' => trim($validated['user_name'] ?? '') ?: '',
            'login' => trim($validated['login'] ?? '') ?: '',
            'password' => trim($validated['password'] ?? '') ?: '',
            'add_password' => trim($validated['add_password'] ?? '') ?: '',
        ];

        if ($credential) {
            // Обновляем существующий логин
            $credential->update($payload);
        } else {
            // Создаём новый
            EmployeeCredential::create([
                'employee_id' => $employee->id,
                'system' => $validated['system'],
            ] + $payload);
        }

        return redirect()->back()->with('success', 'Данные обновлены.');
    }
}
