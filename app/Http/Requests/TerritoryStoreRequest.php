<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TerritoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'territory' => 'required',
            'territory_name' => 'required',
            'department' => 'required',
            'team' => 'nullable',
            'role' => 'required',
            'city' => 'required',
            'manager_id' => 'nullable|integer',
            'old_employee_id' => 'nullable|string',
            'parent_territory_id' => 'nullable|string',
        ];
    }
}
