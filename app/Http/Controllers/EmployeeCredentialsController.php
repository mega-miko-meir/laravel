<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeCredentialsUpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeCredential;

class EmployeeCredentialsController extends Controller
{
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
