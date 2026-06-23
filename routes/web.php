<?php

use App\Models\Employee;
use App\Services\QuoteService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeDataController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\KmpController;

// Auth
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/login', fn() => redirect('/'))->name('login');

Route::middleware(['auth', 'can:editor'])->group(function () {
    // Tasks
    Route::resource('tasks', TaskController::class);
});

Route::middleware('auth')->group(function () {

    // Chatbot
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot');
    Route::post('/chatbot', [ChatbotController::class, 'handle']);
    Route::delete('/chatbot/history', [ChatbotController::class, 'clearHistory'])->name('chatbot.clear');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'showDashboard']);
    Route::get('employees/list/{type}', [DashboardController::class, 'filteredList'])->name('employees.filtered');

    // Exports
    Route::post('/export-excel', [EmployeeDataController::class, 'exportToExcel'])->name('export.excel');
    Route::post('/clients/export', [ClientController::class, 'export'])->name('export.onekey');

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');

    // Feedback
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

Route::middleware(['auth', 'can:admin'])->group(function () {
    // Calls (визиты) — только для админа
    Route::get('/calls', [CallController::class, 'index'])->name('calls.index');
    Route::post('/calls/export', [CallController::class, 'export'])->name('calls.export');

    // KMP продажи
    Route::get('/kmp', [KmpController::class, 'index'])->name('kmp.index');
    Route::post('/kmp/export', [KmpController::class, 'export'])->name('kmp.export');
});

// Dev/misc
Route::get('/print', fn() => view('print'));
Route::get('/upload', fn() => view('upload'));
Route::get('/alpine', fn() => view('alpine-for-practice', ['employees' => Employee::select('id', 'full_name')->get()]));
Route::get('/daily-quote', fn(QuoteService $quoteService) => $quoteService->getDailyQuote(true));

// Route files grouped by domain
require __DIR__ . '/employees.php';
require __DIR__ . '/territories.php';
require __DIR__ . '/tablets.php';
require __DIR__ . '/admin.php';
