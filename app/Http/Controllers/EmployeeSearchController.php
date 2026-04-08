<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeSearchController extends Controller
{
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
    public function myTeam()
    {
        $ffms = Employee::FFM()
            ->with([
                'employee_territory.children.children.employee',
                'employee_territory.employee'
            ])
            ->get();

        $ffms = $ffms->map(function ($employee) {
            $employee->lastTerritory = $employee->employee_territory()
                ->latest('assigned_at')
                ->first();
            return $employee;
        });

        return view('my-team', compact('ffms'));
    }
}