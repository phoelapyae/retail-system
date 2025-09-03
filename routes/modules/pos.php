<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Controllers\{SaleController, RegisterController};

Route::middleware(['api'])->group(function () {
    // Sales routes
    Route::prefix('sales')->group(function () {
        Route::post('/', [SaleController::class, 'create']);
        Route::get('/{id}', [SaleController::class, 'show']);
        Route::get('/', [SaleController::class, 'index']);
    });
    
    // Register routes
    Route::prefix('registers')->group(function () {
        Route::get('/', [RegisterController::class, 'index']);
        Route::post('/{id}/open', [RegisterController::class, 'open']);
        Route::post('/{id}/close', [RegisterController::class, 'close']);
        Route::get('/{id}/daily-report', [RegisterController::class, 'dailyReport']);
    });
});