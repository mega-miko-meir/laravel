<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelDataUploadController;
use App\Http\Controllers\TabletController;
use App\Http\Controllers\EmployeeTabletController;

Route::middleware('auth')->group(function () {
    Route::get('/tablets', [TabletController::class, 'searchTablet'])->name('tablets.search');
    Route::get('/tablets/{tablet}', [TabletController::class, 'showTablet'])->name('tablets.show');

    Route::post('/print-act/{employee}/{tablet}', [EmployeeTabletController::class, 'printAct']);
    Route::post('/print-act2/{employee}/{tablet}', [EmployeeTabletController::class, 'printAct2']);
    Route::get('/api/city-check', [EmployeeTabletController::class, 'cityCheck']);
});

Route::middleware(['auth', 'can:editor'])->group(function () {
    Route::get('/create-tablet', [TabletController::class, 'createTabletForm'])->name('tablet.create');
    Route::post('/create-tablet', [TabletController::class, 'createTablet'])->name('tablet.store');
    Route::get('/edit-tablet/{tablet}', [TabletController::class, 'editTabletForm'])->name('tablet.edit');
    Route::put('/edit-tablet/{tablet}', [TabletController::class, 'editTablet'])->name('tablet.update');

    Route::post('/assign-tablet/{employee}', [EmployeeTabletController::class, 'assignTablet']);
    Route::post('/unassign-tablet/{employee}/{tablet}', [EmployeeTabletController::class, 'unassignTablet'])
        ->name('unassign-tablet');
    Route::patch('/confirm-tablet/{employee}/{tablet}', [EmployeeTabletController::class, 'confirmTablet'])
        ->name('confirm.tablet');

    Route::post('/assign-employee2/{tablet}', [EmployeeTabletController::class, 'assignEmployee2'])
        ->name('assign.employee2');
    Route::post('/upload-assign-pdf/{employee}/{tablet}', [EmployeeTabletController::class, 'assignTabletWithPdf']);
    Route::get('/upload-assign-pdf/{id}', [EmployeeTabletController::class, 'download']);
    Route::post('/employee-tablet/{id}/updatePdf', [EmployeeTabletController::class, 'updatePdf']);

    Route::patch('/employee-tablet/{id}/update', [TabletController::class, 'updateDate'])
        ->name('employee-tablet.updateDate');
    Route::patch('/employee-tablet/{id}/updatePdf', [TabletController::class, 'updatePdf'])
        ->name('employee-tablet.updatePdf');

    Route::post('/tablet-unassign/{employee}/{tablet}', [EmployeeTabletController::class, 'tabletUnassign'])
        ->name('tablet-unassign');

    Route::post('/upload-tablets', [ExcelDataUploadController::class, 'uploadTablets']);
    Route::post('/upload-tablets-assignment', [ExcelDataUploadController::class, 'uploadTabletsAssignment']);
});

Route::middleware('auth')->post('/export/tablets', [TabletController::class, 'exportToExcel'])->name('export.tablets');
