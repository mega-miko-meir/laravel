<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeTerritoryUpdateDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_value' => 'required|date',
            'field_name' => 'required|in:assigned_at,unassigned_at',
        ];
    }
}
