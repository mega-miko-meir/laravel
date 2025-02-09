<?php

use App\Models\Listing;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

use App\Http\Controllers\BrickController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Http\Controllers\TabletController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TerritoryController;
use App\Http\Controllers\EmployeeTabletController;

// Route::get('/', function(){
//     $employees = Employee::all();
//     return view('home', ['employees' => $employees]);
// });

Route::post('/register', [UserController::class, 'register']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/login', [UserController::class, 'login']);


Route::get('/create-employee', [EmployeeController::class, 'createEmployeeForm']);
Route::post('/create-employee', [EmployeeController::class, 'createEmployee']);
Route::get('/employee/{id}', [EmployeeController::class,'showEmployee'])->name('employees.show');
Route::get('/edit-employee/{employee}', [EmployeeController::class, 'showEditEmployee']);
Route::put('/edit-employee/{employee}', [EmployeeController::class, 'actuallyEditEmployee']);
Route::delete('/delete-employee/{employee}', [EmployeeController::class, 'deleteEmployee']);
Route::get('/', [EmployeeController::class, 'searchEmployee'])->name('employees.search');

// Tablet assignment
Route::post('/assign-tablet/{employee}', [EmployeeTabletController::class, 'assignTablet']);
Route::post('/unassign-tablet/{employee}/{tablet}', [EmployeeTabletController::class, 'unassignTablet'])->name('unassign-tablet');
Route::post('/print-act/{employee}/{tablet}', [EmployeeTabletController::class, 'printAct']);

// Territory assignment
Route::post('/assign-territory/{employee}', [EmployeeController::class, 'assignTerritory']);
Route::post('/unassign-territory/{employee}/{territory}', [EmployeeController::class, 'unassignTerritory']);

// Route::patch('/territory/{id}/confirm', [EmployeeController::class, 'confirmTerritory'])->name('territory.confirm');
Route::patch('/confirm-territory/{employee}/{territory}', [EmployeeController::class, 'confirmTerritory'])->name('confirm.territory');


Route::get('/test', function(){
    return view('test');
});

Route::get('/print', function(){
    return view('print');
});

Route::post('/form-template/{employee}', [BrickController::class, 'formTemplate']);

// Brick handling routes
Route::match(['POST', 'DELETE'],'/assign-bricks/{territory}/{brick?}', [BrickController::class, 'handleBricks'])->name('assign.bricks');



Route::get('/upload', function () {
    return view('upload'); // Открывается страница для загрузки файла
});
Route::post('/uploadBricks', [BrickController::class, 'uploadBricks']);
Route::post('/uploadTerritories', [BrickController::class, 'uploadTerritories']);
Route::post('/uploadEmployees', [BrickController::class, 'uploadEmployees']);
Route::get('/bricks', [BrickController::class, 'showBricks']);

Route::get('/export-excel', [EmployeeController::class, 'exportToExcel']);

// Route::get('/upload-pdf', [EmployeeTabletController::class, 'showForm']);
Route::post('/upload-assign-pdf/{employee}/{tablet}', [EmployeeTabletController::class, 'uploadAssignPdf']);

Route::get('/upload-assign-pdf/{id}', [EmployeeTabletController::class, 'download']);

Route::post('/upload-unassign-pdf/{employee}/{tablet}', [EmployeeTabletController::class, 'uploadUnassignPdf'])
->name('upload-unassign-pdf');

// Route::post('/unassign-tablet/{employee}/{tablet}', [EmployeeTabletController::class, 'unassignTablet'])
//     ->name('unassign-tablet');

Route::get('/tablets', [TabletController::class, 'searchTablet'])->name('tablets.search');
Route::get('/tablets/{tablet}', [TabletController::class, 'showTablet'])->name('tablets.show');

Route::get('/territories', [TerritoryController::class, 'searchTerritory'])->name('territories.search');
Route::get('/territories/{territory}', [TerritoryController::class, 'showTerritory'])->name('territories.show');
