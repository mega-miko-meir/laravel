<?php

namespace App\Http\Controllers;

use App\Models\Tablet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TabletController extends Controller
{
    public function searchTablet(Request $request){
        $query = $request->input('search');

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
    $previousUsers = DB::table('employee_tablet')
        ->join('employees', 'employee_tablet.employee_id', '=', 'employees.id')
        ->where('employee_tablet.tablet_id', $tablet->id)
        ->select(
            'employees.id',
            'employees.full_name',
            'employee_tablet.assigned_at',
            'employee_tablet.returned_at',
            'employee_tablet.pdf_path',
            'employee_tablet.unassign_pdf'

        )
        ->orderByDesc('employee_tablet.assigned_at') // Сортируем по дате выдачи
        ->get();

    return view('show-tablet', compact('tablet', 'previousUsers'));
}


}

