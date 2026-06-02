<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDataController;
use App\Http\Controllers\EmployeeEventController;
use App\Http\Controllers\EmployeeTerritoryController;

Route::get('/', [EmployeeController::class, 'searchEmployee'])->name('employees.search');

Route::middleware('auth')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/employee/{id}', [EmployeeController::class, 'showEmployee'])->name('employees.show');
    Route::get('/my-team', [EmployeeController::class, 'myTeam'])->name('employees.my-team');
});

Route::middleware(['auth', 'can:editor'])->group(function () {
    Route::get('/create-employee', [EmployeeController::class, 'createEmployeeForm']);
    Route::post('/create-employee', [EmployeeController::class, 'createEmployee']);
    Route::get('/edit-employee/{employee}', [EmployeeController::class, 'showEditEmployee']);
    Route::put('/edit-employee/{employee}', [EmployeeController::class, 'actuallyEditEmployee']);
    Route::delete('/delete-employee/{employee}', [EmployeeController::class, 'deleteEmployee']);

    Route::put('/employees/{employee}/dismiss', [EmployeeEventController::class, 'updateStatus'])
        ->name('employees.updateStatus');
    Route::put('/employees/{employee}/update-status-event', [EmployeeEventController::class, 'addingEvent'])
        ->name('employees.updateStatusAndEvent');
    Route::patch('/events/{event}', [EmployeeEventController::class, 'update'])
        ->name('events.update');
    Route::delete('/events/{id}', [EmployeeEventController::class, 'destroy'])
        ->name('events.destroy');

    Route::post('/employees/{employee}/upload-photo', [EmployeeController::class, 'uploadPhoto'])
        ->name('employees.uploadPhoto');

    Route::put('/employees/{id}/update-credentials', [EmployeeController::class, 'updateCredentials'])
        ->name('employees.updateCredentials');
    Route::delete('/employees/credentials/{id}', [EmployeeController::class, 'deleteCredential']);

    Route::post('/assign-employee/{territory}', [EmployeeTerritoryController::class, 'assignEmployee'])
        ->name('assign.employee');
    Route::patch('/employee-territory/{id}/update', [EmployeeTerritoryController::class, 'updateDate'])
        ->name('employee-territory.updateDate');

    Route::post('/upload-employees', [EmployeeDataController::class, 'uploadEmployees']);
});
