<?php

namespace App\Http\Controllers;

use App\Services\EmployeeExportService;
use Illuminate\Http\Request;

class EmployeeExportController extends Controller
{
    /**
     * Export employees to Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return app(EmployeeExportService::class)->exportToExcel($request);
    }
}