<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TabletUpdateDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_value' => 'required|date',
            'field_name' => 'required|in:assigned_at,returned_at',
        ];
    }
}
