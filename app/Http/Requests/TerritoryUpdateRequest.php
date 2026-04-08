<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TerritoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $territoryId = $this->route('territory')?->id;

        return [
            'territory' => ['required', Rule::unique('territories', 'territory_name')->ignore($territoryId)],
            'territory_name' => ['required', Rule::unique('territories', 'territory_name')->ignore($territoryId)],
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
