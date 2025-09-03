<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Ecommerce\Controllers\{OrderController, ProductController};

Route::middleware(['api'])->group(function () {
    // Product routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/low-stock', [ProductController::class, 'lowStock']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });
    
    // Order routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'create']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::put('/{id}/status', [OrderController::class, 'updateStatus']);
    });
});