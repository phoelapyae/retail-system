<?php

use Illuminate\Support\Facades\Route;
use App\Core\Http\Controllers\ModuleController;

Route::get('/hi', function() {
    dd('hello');
});

Route::prefix('system')->group(function () {
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/health', [ModuleController::class, 'health']);
    Route::get('/metrics', [ModuleController::class, 'metrics']);
});