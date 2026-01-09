<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        $hired_total = DB::table('employee_events as ee1')
            ->where('ee1.event_type', 'hired')
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )')
            ->count();

        $fired_this_month = DB::table('employee_events as ee1')
            ->where('ee1.event_type', 'dismissed')
            ->whereMonth('ee1.event_date', now()->month)
            ->whereYear('ee1.event_date', now()->year)
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )')
            ->count();

        $hired_this_month = DB::table('employee_events as ee1')
            ->where('ee1.event_type', 'hired')
            ->whereMonth('ee1.event_date', now()->month)
            ->whereYear('ee1.event_date', now()->year)
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )')
            ->count();

        $on_maternity_leave = DB::table('employee_events as ee1')
            ->where('ee1.event_type', 'maternity_leave')
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )')
            ->count();

        $fired_last_month = DB::table('employee_events as ee1')
            ->where('ee1.event_type', 'dismissed')
            ->whereMonth('ee1.event_date', now()->subMonth()->month)
            ->whereYear('ee1.event_date', now()->subMonth()->year)
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )')
            ->count();

        $hired_last_month = DB::table('employee_events as ee1')
            ->where('ee1.event_type', 'hired')
            ->whereMonth('ee1.event_date', now()->subMonth()->month)
            ->whereYear('ee1.event_date', now()->subMonth()->year)
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )')
            ->count();

        $fired_this_year = DB::table('employee_events as ee1')
            ->where('ee1.event_type', 'dismissed')
            ->whereYear('ee1.event_date', now()->year)
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )')
            ->count();

        $hired_this_year = DB::table('employee_events as ee1')
            ->where('ee1.event_type', 'hired')
            ->whereYear('ee1.event_date', now()->year)
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )')
            ->count();


        return view('dashboard', compact('hired_total', 'fired_this_month', 'hired_this_month', 'on_maternity_leave', 'fired_last_month', 'hired_last_month', 'fired_this_year', 'hired_this_year'));
    }

    public function filteredList($type)
    {

        // $employees = Employee::with('territories')->get();

        switch ($type) {
            case 'hired_total':
                $employees = DB::table('employees as e')
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
                $title = "Все сотрудники";
                break;

            case 'fired_this_month':
                $employees = DB::table('employees as e')
                    ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
                    ->where('ev.event_type', 'dismissed')
                    ->whereMonth('ev.event_date', now()->month)
                    ->whereYear('ev.event_date', now()->year)
                    ->select('e.*', 'ev.event_type', 'ev.event_date')
                    ->orderBy('ev.event_date', 'DESC')
                    ->get();
                $title = "Уволенные в этом месяце";
                break;

            case 'hired_this_month':
                $employees = DB::table('employees as e')
                    ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
                    ->where('ev.event_type', 'hired')
                    ->whereMonth('ev.event_date', now()->month)
                    ->whereYear('ev.event_date', now()->year)
                    ->select('e.*', 'ev.event_type', 'ev.event_date')
                    ->orderBy('ev.event_date', 'DESC')
                    ->get();
                $title = "Нанятые в этом месяце";
                break;

            case 'on_maternity_leave':
                $employees = DB::table('employees as e')
                    ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
                    ->where('ev.event_type', 'maternity_leave')
                    ->whereRaw('ev.id = (
                        SELECT ee.id FROM employee_events ee
                        WHERE ee.employee_id = ev.employee_id
                        ORDER BY ee.event_date DESC
                        LIMIT 1
                    )')
                    ->select('e.*', 'ev.event_type', 'ev.event_date')
                    ->orderBy('ev.event_date', 'DESC')
                    ->get();
                $title = "В декрете";
                break;

            case 'fired_last_month':
                $employees = DB::table('employees as e')
                    ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
                    ->where('ev.event_type', 'dismissed')
                    ->whereMonth('ev.event_date', now()->subMonth()->month)
                    ->whereYear('ev.event_date', now()->subMonth()->year)
                    ->select('e.*', 'ev.event_type', 'ev.event_date')
                    ->orderBy('ev.event_date', 'DESC')
                    ->get();
                $title = "Уволенные в прошлом месяце";
                break;

            case 'hired_last_month':
                $employees = DB::table('employees as e')
                    ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
                    ->where('ev.event_type', 'hired')
                    ->whereMonth('ev.event_date', now()->subMonth()->month)
                    ->whereYear('ev.event_date', now()->subMonth()->year)
                    ->select('e.*', 'ev.event_type', 'ev.event_date')
                    ->orderBy('ev.event_date', 'DESC')
                    ->get();
                $title = "Нанятые в прошлом месяце";
                break;

            case 'fired_this_year':
                $employees = DB::table('employees as e')
                    ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
                    ->where('ev.event_type', 'dismissed')
                    ->whereYear('ev.event_date', now()->year)
                    ->select('e.*', 'ev.event_type', 'ev.event_date')
                    ->orderBy('ev.event_date', 'DESC')
                    ->get();
                $title = "Уволенные в этом году";
                break;

            case 'hired_this_year':
                $employees = DB::table('employees as e')
                    ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
                    ->where('ev.event_type', 'hired')
                    ->whereYear('ev.event_date', now()->year)
                    ->select('e.*', 'ev.event_type', 'ev.event_date')
                    ->orderBy('ev.event_date', 'DESC')
                    ->get();
                $title = "Нанятые в этом году";
                break;

            default:
                abort(404);
        }

        return view('employees-filtered-list', compact('employees', 'title'));
    }


}
