<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'string',
            'description' => 'nullable|string',
            'status' => 'in:todo,in_progress,done',
            'deadline' => 'nullable|date',
        ];
    }
}
