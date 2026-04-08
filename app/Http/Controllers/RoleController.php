<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleUpsertRequest;
use App\Models\Role;

class RoleController extends Controller
{
    public function index(){
        return Role::all();
    }

    public function store(RoleUpsertRequest $request){
        $role = Role::create($request->validated());


        return redirect('/users')->with('success', 'Congrats! You created a new role');
    }

    public function show($id){
        return Role::find($id);
    }

    public function update(RoleUpsertRequest $request, $id){
        $role = Role::find($id);
        $role->update($request->validated());

        return redirect('/users')->with('success', 'Congrats! You updated the role');
    }

    public function destroy($id){
        Role::destroy($id);

        return redirect('/users')->with('success', 'Congrats! You deleted the role');
    }
}
