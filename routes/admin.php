<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;

Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/register', [UserController::class, 'showRegister']);
    Route::post('/register', [UserController::class, 'register']);
    Route::get('/users/{user}/edit', [UserController::class, 'showEdit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.logs');
    Route::get('/activity/export', [ActivityLogController::class, 'export'])->name('activity.export');

    Route::get('/admin/notifications', [NotificationController::class, 'index'])
        ->name('admin.notifications');
    Route::get('/admin/notifications/{notification}', [NotificationController::class, 'show'])
        ->name('admin.notifications.show');

});
