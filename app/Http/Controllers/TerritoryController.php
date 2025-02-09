<?php

namespace App\Http\Controllers;

use App\Models\Territory;
use App\View\Components\territory as ComponentsTerritory;
use Illuminate\Http\Request;

class TerritoryController extends Controller
{
    public function searchTerritory(Request $request){
        $query = $request->input('search');

        $territories = Territory::where('territory_name', 'like', "%$query%")
            ->orWhere('city', 'like', "%$query%")
            ->orWhere('department', 'like', "%$query%")
            ->orWhere('manager_id', 'like', "%$query%")
            ->orWhereHas('employees', function ($q) use ($query) {
                $q->where('full_name', 'like', "%$query%");
            })
            // ->with(['currentAssignment' => function ($q) {
            //     $q->select('tablet_id', 'pdf_path', 'unassign_pdf')
            //     ->orderByDesc('id');
            // }])
            ->get();

        return view('territories', ['territories' => $territories, 'query' => $query]);
    }

    public function showTerritory(Territory $territory)
    {
        $previousUsers = $territory->employees()
        ->withPivot('assigned_at', 'unassigned_at')
        ->orderByDesc('employee_territory.assigned_at')
        ->get();


        return view('show-territory', compact('territory', 'previousUsers'));
    }
}
