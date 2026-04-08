<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeCredentialsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'system' => ['required', 'string', 'max:255'],
            'user_name' => ['nullable', 'string', 'max:255'],
            'login' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'add_password' => ['nullable', 'string', 'max:255'],
        ];
    }
}
