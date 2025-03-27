<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use App\Models\Employee;
use App\Models\Tablet;
use Illuminate\Support\Facades\Storage;

class PdfUploadService
{
    public function upload(UploadedFile $file, Employee $employee, Tablet $tablet): string
    {
        $timestamp = now()->format('d.m.Y');
        $filename = "Передача_{$employee->first_name}_{$employee->last_name}_{$tablet->serial_number}_{$timestamp}.pdf";

        return $file->storeAs('uploads/assign', $filename, 'public');
    }
}
