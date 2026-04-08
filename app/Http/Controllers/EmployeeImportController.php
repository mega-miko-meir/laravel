<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadExcelFileRequest;
use App\Services\EmployeeImportService;

class EmployeeImportController extends Controller
{
    /**
     * Upload employees from Excel.
     *
     * @param UploadExcelFileRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadEmployees(UploadExcelFileRequest $request)
    {
        return app(EmployeeImportService::class)->uploadBricks($request);
    }
}