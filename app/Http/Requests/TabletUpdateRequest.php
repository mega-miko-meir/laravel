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
            'status' => 'nullable',
            'invent_number' => 'required',
            'serial_number' => 'required',
            'imei' => 'required',
            'beeline_number' => 'required',
        ];
    }
}
