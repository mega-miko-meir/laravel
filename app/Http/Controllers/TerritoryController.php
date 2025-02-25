<?php

namespace App\Http\Controllers;

use App\Models\Brick;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\View\Components\territory as ComponentsTerritory;

class TerritoryController extends Controller
{
    public function searchTerritory(Request $request){
        $query = $request->input('search');
        $sort = $request->input('sort', 'territory_name');
        $order = $request->input('order', 'asc');

        $territories = Territory::where('territory_name', 'like', "%$query%")
            ->orWhere('city', 'like', "%$query%")
            ->orWhere('department', 'like', "%$query%")
            ->orWhere('manager_id', 'like', "%$query%")
            ->orWhereHas('employees', function ($q) use ($query) {
                $q->where('full_name', 'like', "%$query%");
            })
            ->orderBy($sort, $order)
            ->get();

        return view('territories', ['territories' => $territories, 'query' => $query, 'sort' => $sort, 'order' => $order]);
    }

    public function showTerritory(Territory $territory)
    {

        $employee = $territory->employee;
        $bricks = Brick::all();
        $selectedBricks = $employee->territories->first()->bricks ?? collect();
        $previousUsers = $territory->employees()
        ->withPivot('assigned_at', 'unassigned_at')
        ->orderByDesc('employee_territory.assigned_at')
        ->get();


        return view('show-territory', compact('territory', 'previousUsers', 'employee', 'bricks', 'selectedBricks'));
    }

    public function createTerritoryForm(){
        $parentTerritories = Territory::with('employee')
            ->where('role', 'RM')
            ->get();


        return view('create-edit-territory', compact('parentTerritories'));
    }


    public function editTerritoryForm(Territory $territory){
        $parentTerritories = Territory::with('employee')
        ->where('role', 'RM')
        ->get();
        return view('create-edit-territory', ['territory' => $territory, 'parentTerritories' => $parentTerritories]);
    }


    public function createTerritory(Request $request){
        $incomingFields = $request->validate([
            'territory' => 'required',
            'territory_name' => 'required',
            'department' => 'required',
            'team' => 'required',
            'role' => 'required',
            'city' => 'required',
            'manager_id' => 'nullable|integer',
            'old_employee_id' => 'nullable|string',
            'parent_territory_id' => 'nullable|string'
        ]);

        $existingTerritory = Territory::where('territory_name', $incomingFields['territory_name'])->first();

        if ($existingTerritory) {
            return redirect()->back()->with('error', 'Такая территория уже существует!');
        }

        Territory::create($incomingFields);

        // return view('territory.create', [
        //     'territory' => null, // Для формы создания новой территории
        //     'territories' => Territory::all(), // Передаем все территории
        // ]);

        return redirect()->back()->with('success', 'Territory added successfully!');
    }

    public function editTerritory(Request $request, Territory $territory)
    {
        $incomingFields = $request->validate([
            'territory' => 'required|unique:territories,territory_name,' . $territory->id,
            'territory_name' => 'required|unique:territories,territory_name,' . $territory->id,
            'department' => 'required',
            'team' => 'required',
            'role' => 'required',
            'city' => 'required',
            'manager_id' => 'nullable|integer',
            'old_employee_id' => 'nullable|string',
            'parent_territory_id' => 'nullable|string'
        ]);

        $territory->update($incomingFields);

        return back()->with('success', 'Данные успешно обновлены!');
        // return view('territories.create', [
        //     'territory' => $territory, // Передаем текущую территорию
        //     'territories' => Territory::where('id', '!=', $territory->id)->get(), // Исключаем текущую из списка
        // ]);
    }




}
