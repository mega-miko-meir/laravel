<?php

use App\Models\Listing;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\BrickController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Http\Controllers\TabletController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TerritoryController;
use App\Http\Controllers\EmployeeEventController;
use App\Http\Controllers\EmployeeTabletController;
use App\Http\Controllers\ExcelDataUploadController;
use App\Http\Controllers\EmployeeTerritoryController;
use App\Http\Controllers\EmployeeCredentialsController;

// Route::get('/rennes', []);

Route::post('/register', [UserController::class, 'register']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/login', [UserController::class, 'login']);


Route::get('/create-employee', [EmployeeController::class, 'createEmployeeForm']);
Route::post('/create-employee', [EmployeeController::class, 'createEmployee']);
Route::get('/edit-employee/{employee}', [EmployeeController::class, 'showEditEmployee']);
Route::put('/edit-employee/{employee}', [EmployeeController::class, 'actuallyEditEmployee']);

Route::delete('/delete-employee/{employee}', [EmployeeController::class, 'deleteEmployee']);
Route::get('/employee/{id}', [EmployeeController::class,'showEmployee'])->name('employees.show');

Route::get('/employees', [EmployeeController::class, 'index']);


Route::get('/', [EmployeeController::class, 'searchEmployee'])->name('employees.search');
// Route::get('/employees/search', [EmployeeController::class, 'searchEmployee'])->name('employees.search');



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
Route::post('/assign-territory/{employee}', [TerritoryController::class, 'assignTerritory']);
Route::post('/unassign-territory/{employee}/{territory}', [TerritoryController::class, 'unassignTerritory']);

// Route::patch('/territory/{id}/confirm', [EmployeeController::class, 'confirmTerritory'])->name('territory.confirm');
Route::patch('/confirm-territory/{employee}/{territory}', [TerritoryController::class, 'confirmTerritory'])->name('confirm.territory');
Route::patch('/confirm-tablet/{employee}/{tablet}', [EmployeeTabletController::class, 'confirmTablet'])->name('confirm.tablet');


Route::get('/chatbot', function(){
    return view('chatbot');
});

Route::post('/chatbot', [ChatbotController::class, 'handle']);

Route::get('/print', function(){
    return view('print');
});

// Task route
Route::middleware('auth')->group(function () {
    Route::resource('tasks', TaskController::class);
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
Route::post('/upload-tablets-assignment', [ExcelDataUploadController::class, 'uploadTabletsAssignment']);

Route::get('/export-excel', [EmployeeController::class, 'exportToExcel']);

// Route::get('/upload-pdf', [EmployeeTabletController::class, 'showForm']);
Route::post('/upload-assign-pdf/{employee}/{tablet}', [EmployeeTabletController::class, 'assignTabletWithPdf']);

Route::get('/upload-assign-pdf/{id}', [EmployeeTabletController::class, 'download']);

Route::post('/tablet-unassign/{employee}/{tablet}', [EmployeeTabletController::class, 'tabletUnassign'])
->name('tablet-unassign');

// Route::post('/unassign-tablet/{employee}/{tablet}', [EmployeeTabletController::class, 'unassignTablet'])
//     ->name('unassign-tablet');

Route::get('/tablets', [TabletController::class, 'searchTablet'])->name('tablets.search');
Route::get('/tablets/{tablet}', [TabletController::class, 'showTablet'])->name('tablets.show');

Route::get('/territories', [TerritoryController::class, 'searchTerritory'])->name('territories.search');
Route::get('/territories/{territory}', [TerritoryController::class, 'showTerritory'])->name('territories.show');

Route::put('/employees/{employee}/dismiss', [EmployeeEventController::class, 'updateStatus'])->name('employees.updateStatus');

Route::put('/employees/{employee}/update-status-event', [EmployeeEventController::class, 'addingEvent'])
    ->name('employees.updateStatusAndEvent');


Route::put('/employees/{id}/update-credentials', [EmployeeCredentialsController::class, 'updateCredentials'])
    ->name('employees.updateCredentials');

Route::delete('/employees/credentials/{id}', [EmployeeController::class, 'deleteCredential']);


// Добавление территории к сотруднику
Route::post('/assign-employee/{territory}', [EmployeeTerritoryController::class, 'assignEmployee'])->name('assign.employee');
// Добавление планшета к сотруднику
Route::post('/assign-employee2/{tablet}', [EmployeeTabletController::class, 'assignEmployee2'])->name('assign.employee2');

Route::patch('/employee-territory/{id}/update', [EmployeeTerritoryController::class, 'updateDate'])->name('employee-territory.updateDate');
Route::patch('/employee-tablet/{id}/update', [TabletController::class, 'updateDate'])->name('employee-tablet.updateDate');


Route::get('/alpine', function(){
    return view('alpine-for-practice');
});

