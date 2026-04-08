<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['nullable', 'string', 'max:255'],
            'organization_type' => ['nullable', 'string', 'max:255'],
            'specialty' => ['nullable', 'array'],
            'specialty.*' => ['string', 'max:255'],
            'city' => ['nullable', 'array'],
            'city.*' => ['string', 'max:255'],
            'brick_label' => ['nullable', 'array'],
            'brick_label.*' => ['string', 'max:255'],
        ];
    }
}
