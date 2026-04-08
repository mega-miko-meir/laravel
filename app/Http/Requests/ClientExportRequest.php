<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $exportableColumns = [
            'full_name',
            'organization_type',
            'specialty',
            'specialty2',
            'parent_organization',
            'workplace',
            'primary_address',
            'city',
            'brick_name',
            'brick_label',
            'onekey_id',
            'coordinates',
        ];

        return [
            'full_name' => ['nullable', 'string', 'max:255'],
            'organization_type' => ['nullable', 'string', 'max:255'],
            'specialty' => ['nullable', 'array'],
            'specialty.*' => ['string', 'max:255'],
            'city' => ['nullable', 'array'],
            'city.*' => ['string', 'max:255'],
            'brick_label' => ['nullable', 'array'],
            'brick_label.*' => ['string', 'max:255'],
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string', Rule::in($exportableColumns)],
        ];
    }
}
