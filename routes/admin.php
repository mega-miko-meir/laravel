<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CrmMappingController;
use App\Http\Controllers\KmpMappingController;
use App\Http\Controllers\DataIntegrityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TerritoryChangeController;
use App\Http\Controllers\DoubleVisitPlanController;
use App\Http\Controllers\TargetClientsController;

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/register', [UserController::class, 'showRegister']);
    Route::post('/register', [UserController::class, 'register']);
    Route::get('/users/{user}/edit', [UserController::class, 'showEdit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.logs');
    Route::get('/activity/export', [ActivityLogController::class, 'export'])->name('activity.export');

    Route::get('/admin/notifications', [NotificationController::class, 'index'])
        ->name('admin.notifications');
    Route::get('/admin/notifications/{notification}', [NotificationController::class, 'show'])
        ->name('admin.notifications.show');

    // Привязка CRM/KMP и проверка данных объединены на одной странице с вкладками
    // (/admin/data-integrity) — старые URL остаются рабочими как редиректы на
    // нужную вкладку, чтобы не ломать существующие ссылки/закладки.
    Route::get('/admin/data-integrity', [DataIntegrityController::class, 'index'])->name('admin.data-integrity');

    Route::get('/admin/crm-mapping', fn() => redirect()->route('admin.data-integrity', ['tab' => 'crm']))->name('admin.crm-mapping');
    Route::post('/admin/crm-mapping/auto-match', [CrmMappingController::class, 'autoMatch'])->name('admin.crm-mapping.auto');
    Route::post('/admin/crm-mapping/link', [CrmMappingController::class, 'link'])->name('admin.crm-mapping.link');

    Route::get('/admin/kmp-mapping', fn() => redirect()->route('admin.data-integrity', ['tab' => 'kmp']))->name('admin.kmp-mapping');
    Route::post('/admin/kmp-mapping/auto-match', [KmpMappingController::class, 'autoMatch'])->name('admin.kmp-mapping.auto');
    Route::post('/admin/kmp-mapping/link', [KmpMappingController::class, 'link'])->name('admin.kmp-mapping.link');

    Route::get('/admin/data-quality', fn() => redirect()->route('admin.data-integrity', ['tab' => 'quality']))->name('admin.data-quality');

    Route::post('/admin/reports/weekly-dismissed', [ReportController::class, 'sendWeeklyDismissed'])->name('admin.reports.weekly-dismissed');

    Route::get('/admin/territory-changes', [TerritoryChangeController::class, 'index'])->name('admin.territory-changes');
    Route::get('/admin/territory-changes/export', [TerritoryChangeController::class, 'export'])->name('admin.territory-changes.export');

    Route::get('/admin/double-visit-plan', [DoubleVisitPlanController::class, 'index'])->name('admin.double-visit-plan');
    Route::get('/admin/double-visit-plan/data', [DoubleVisitPlanController::class, 'data'])->name('admin.double-visit-plan.data');
    Route::get('/admin/double-visit-plan/export', [DoubleVisitPlanController::class, 'export'])->name('admin.double-visit-plan.export');

    Route::get('/admin/target-clients', [TargetClientsController::class, 'index'])->name('admin.target-clients');
    Route::get('/admin/target-clients/data', [TargetClientsController::class, 'data'])->name('admin.target-clients.data');
    Route::get('/admin/target-clients/export/doctors', [TargetClientsController::class, 'exportDoctors'])->name('admin.target-clients.export.doctors');
    Route::get('/admin/target-clients/export/pharmacies', [TargetClientsController::class, 'exportPharmacies'])->name('admin.target-clients.export.pharmacies');

});
