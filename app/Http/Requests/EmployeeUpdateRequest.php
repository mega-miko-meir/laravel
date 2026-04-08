<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            'full_name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'team' => 'nullable',
            'city' => 'nullable',
            'position' => 'nullable',
            'hiring_date' => 'nullable',
            'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($employeeId)],
        ];
    }
}
