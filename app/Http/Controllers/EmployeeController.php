<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeCredentialsUpdateRequest;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeCredential;
use App\Models\EmployeeEvent;
use App\Models\Nobel\Call;
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
    public function showEmployee(int $id)
    {
        $employee = Employee::with(['tablets', 'territories', 'employee_territory', 'employee_tablet', 'credentials', 'events'])->findOrFail($id);

        $lastTerritory  = $employee->employee_territory()->latest('assigned_at')->first();
        $lastTablet     = $employee->employee_tablet()->withPivot('assigned_at', 'returned_at')->orderByDesc('assigned_at')->first();

        $visitStats = null;
        if ($employee->crm_employee_id) {
            $crmId = $employee->crm_employee_id;
            $base  = Call::where('employee_id', $crmId)
                ->where('appointment_status', 'Выполнено')
                ->whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку']);

            $total     = (clone $base)->count();
            $avgDur    = (int) ((clone $base)->where('appointment_duration', '>', 0)->avg('appointment_duration') ?? 0);
            $lastDate  = (clone $base)->max('appointment_Date');

            $thisMonth = (clone $base)->whereYear('appointment_Date', now()->year)->whereMonth('appointment_Date', now()->month)->count();
            $lastMonth = (clone $base)->whereYear('appointment_Date', now()->subMonth()->year)->whereMonth('appointment_Date', now()->subMonth()->month)->count();

            $monthly = (clone $base)
                ->selectRaw("DATE_FORMAT(appointment_Date, '%Y-%m') as month, COUNT(*) as total")
                ->whereNotNull('appointment_Date')
                ->where('appointment_Date', '>=', now()->subMonths(5)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $topSpec = (clone $base)
                ->selectRaw('customer_spesiality, COUNT(*) as cnt')
                ->whereNotNull('customer_spesiality')->where('customer_spesiality', '<>', '')
                ->groupBy('customer_spesiality')->orderByDesc('cnt')->limit(3)->get();

            $doctorVisits   = (clone $base)->where('appointment_type', 'Визит к врачу')->count();
            $pharmacyVisits = (clone $base)->where('appointment_type', 'Визит в аптеку')->count();

            $visitStats = compact('total', 'avgDur', 'lastDate', 'thisMonth', 'lastMonth', 'monthly', 'topSpec', 'crmId', 'doctorVisits', 'pharmacyVisits');
        }

        return view('employee', [
            'employee'             => $employee,
            'lastTerritory'        => $lastTerritory,
            'lastTablet'           => $lastTablet,
            'selectedBricks'       => $lastTerritory?->bricks ?? collect(),
            'bricks'               => \App\Models\Brick::all(),
            'availableTablets'     => \App\Models\Tablet::free()->with('oldEmployee')->get(),
            'availableTerritories' => \App\Models\Territory::whereNull('employee_id')
                ->with(['employeeTerritories' => fn($q) => $q->with('employee')->latest('assigned_at')])
                ->get(),
            'territoriesHistory'   => $employee->employee_territory()->withPivot('assigned_at', 'unassigned_at', 'id')->orderByDesc('assigned_at')->get(),
            'tabletHistories'      => \App\Models\EmployeeTablet::where('employee_id', $employee->id)->with('tablet')->orderByDesc('assigned_at')->get(),
            'currentStatus'        => $employee->events()->latest('event_date')->value('event_type'),
            'visitStats'           => $visitStats,
        ]);
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
            return view('components.employee-card', compact('employees', 'sort', 'order'))->render();
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
    public function uploadPhoto(\Illuminate\Http\Request $request, Employee $employee)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($employee->photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->photo_path);
        }

        $path = $request->file('photo')->store('employees/photos', 'public');
        $employee->update(['photo_path' => $path]);

        return back()->with('success', 'Фото обновлено.');
    }

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
