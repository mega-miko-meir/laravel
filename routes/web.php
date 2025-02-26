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
use App\Http\Controllers\ExcelDataUploadController;

// Route::get('/', function(){
//     $employees = Employee::all();
//     return view('home', ['employees' => $employees]);
// });

Route::post('/register', [UserController::class, 'register']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/login', [UserController::class, 'login']);


Route::get('/create-employee', [EmployeeController::class, 'createEmployeeForm']);
Route::post('/create-employee', [EmployeeController::class, 'createEmployee']);
Route::get('/edit-employee/{employee}', [EmployeeController::class, 'showEditEmployee']);
Route::put('/edit-employee/{employee}', [EmployeeController::class, 'actuallyEditEmployee']);

Route::delete('/delete-employee/{employee}', [EmployeeController::class, 'deleteEmployee']);
Route::get('/employee/{id}', [EmployeeController::class,'showEmployee'])->name('employees.show');
Route::get('/', [EmployeeController::class, 'searchEmployee'])->name('employees.search');

// Creatind and editing territory
Route::get('/create-territory', [TerritoryController::class, 'createTerritoryForm'])->name('territory.create');
Route::post('/create-territory', [TerritoryController::class, 'createTerritory'])->name('territory.create');
Route::get('/edit-territory/{territory}', [TerritoryController::class, 'editTerritoryForm'])->name('territory.edit');
Route::put('/edit-territory/{territory}', [TerritoryController::class, 'editTerritory'])->name('territory.edit');



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
Route::get('/bricks', [BrickController::class, 'showBricks']);

// Uploading files
Route::post('/upload-bricks', [BrickController::class, 'uploadBricks']);
Route::post('/upload-territories', [BrickController::class, 'uploadTerritories']);
Route::post('/upload-employees', [ExcelDataUploadController::class, 'uploadEmployees']);
Route::post('/upload-tablets', [ExcelDataUploadController::class, 'uploadTablets']);

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

Route::put('/employees/{employee}/dismiss', [EmployeeController::class, 'updateStatus'])->name('employees.updateStatus');

Route::put('/employees/{employee}/update-status-event', [EmployeeController::class, 'updateStatusAndEvent'])
    ->name('employees.updateStatusAndEvent');


Route::put('/employees/{id}/update-credentials', [EmployeeController::class, 'updateCredentials'])
    ->name('employees.updateCredentials');

Route::delete('/employees/credentials/{id}', [EmployeeController::class, 'deleteCredential']);

