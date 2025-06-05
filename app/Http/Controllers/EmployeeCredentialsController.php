<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeCredential;

class EmployeeCredentialsController extends Controller
{
    public function updateCredentials(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        // Проверяем, есть ли уже такой логин
        $credential = EmployeeCredential::where('employee_id', $employee->id)
            ->where('system', $request->system)
            ->first();

        if ($credential) {
            // Обновляем существующий логин
            $credential->update([
                'user_name' => trim($request->user_name) ?: '',
                'login' => trim($request->login) ?: '',
                'password' => trim($request->password) ?: '',
                'add_password' => trim($request->add_password) ?: ''
            ]);
        } else {
            // Создаём новый
            EmployeeCredential::create([
                'employee_id' => $employee->id,
                'system' => $request->system,
                'user_name' => trim($request->user_name) ?: '',
                'login' => trim($request->login) ?: '',
                'password' => trim($request->password) ?: '',
                'add_password' => trim($request->add_password) ?: ''
            ]);
        }

        return redirect()->back()->with('success', 'Данные обновлены.');
    }
}
