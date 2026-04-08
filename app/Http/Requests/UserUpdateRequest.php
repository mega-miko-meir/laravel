<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|max:255',
            'first_name' => 'nullable|max:255',
            'last_name' => 'nullable|max:255',
            'position' => 'nullable|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('id'))],
            'role_id' => 'required',
            'password' => 'nullable|min:8|confirmed',
        ];
    }
}
