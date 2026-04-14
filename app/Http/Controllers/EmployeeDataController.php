<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadExcelFileRequest;
use App\Services\EmployeeExportService;
use App\Services\EmployeeImportService;
use Illuminate\Http\Request;

class EmployeeDataController extends Controller
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

    /**
     * Upload employees from Excel.
     *
     * @param UploadExcelFileRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadEmployees(UploadExcelFileRequest $request): \Illuminate\Http\RedirectResponse
    {
        return app(EmployeeImportService::class)->uploadBricks($request);
    }
}
