<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(){
        return Role::all();
    }

    public function store(Request $request){
        $role = Role::create($request->only('name'));


        return redirect('/users')->with('success', 'Congrats! You created a new role');
    }

    public function show($id){
        return Role::find($id);
    }

    public function update(Request $request, $id){
        $role = Role::find($id);
        $role->update($request->only('name'));

        return redirect('/users')->with('success', 'Congrats! You updated the role');
    }

    public function destroy($id){
        Role::destroy($id);

        return redirect('/users')->with('success', 'Congrats! You deleted the role');
    }
}
