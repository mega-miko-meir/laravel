<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BrickController;
use App\Http\Controllers\TerritoryController;

Route::get('/territories', [TerritoryController::class, 'searchTerritory'])->name('territories.search');
Route::get('/territories/{territory}', [TerritoryController::class, 'showTerritory'])->name('territories.show');

Route::middleware(['auth', 'can:editor'])->group(function () {
    Route::get('/create-territory', [TerritoryController::class, 'createTerritoryForm'])->name('territory.create');
    Route::post('/create-territory', [TerritoryController::class, 'createTerritory']);
    Route::get('/edit-territory/{territory}', [TerritoryController::class, 'editTerritoryForm'])->name('territory.edit');
    Route::put('/edit-territory/{territory}', [TerritoryController::class, 'editTerritory']);

    Route::post('/assign-territory/{employee}', [TerritoryController::class, 'assignTerritory']);
    Route::post('/unassign-territory/{employee}/{territory}', [TerritoryController::class, 'unassignTerritory']);
    Route::patch('/confirm-territory/{employee}/{territory}', [TerritoryController::class, 'confirmTerritory'])
        ->name('confirm.territory');

    Route::match(['POST', 'DELETE'], '/assign-bricks/{territory}/{brick?}', [BrickController::class, 'handleBricks'])
        ->name('assign.bricks');
    Route::post('/upload-bricks', [BrickController::class, 'uploadBricks']);
    Route::post('/form-template/{employee}', [BrickController::class, 'formTemplate']);
});
