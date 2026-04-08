<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TabletUpdatePdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pdf_value' => 'required|mimes:pdf|max:5120',
            'field_name' => 'required|in:pdf_path,unassign_pdf',
        ];
    }
}
