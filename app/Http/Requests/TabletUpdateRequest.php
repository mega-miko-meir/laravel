<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TabletUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'model' => 'required',
            'status' => 'nullable|in:active,lost,damaged,written-off,admin',
            'invent_number' => 'nullable',
            'serial_number' => 'required',
            'imei' => 'nullable',
            'beeline_number' => 'nullable',
            'responsible_id' => 'nullable|exists:employees,id',
        ];
    }
}
