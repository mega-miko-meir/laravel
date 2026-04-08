<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required',
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'birth_date' => 'nullable',
            'email' => 'required|email|unique:employees,email',
            'hiring_date' => 'nullable|date',
            'position' => 'nullable',
            'status' => 'nullable',
        ];
    }
}
