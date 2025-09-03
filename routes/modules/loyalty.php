<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Loyalty\Controllers\{LoyaltyController, TierController};

Route::middleware(['api'])->group(function () {
    // Loyalty tiers
    Route::prefix('tiers')->group(function () {
        Route::get('/', [TierController::class, 'index']);
    });
    
    // Customer loyalty
    Route::prefix('customers/{customerId}')->group(function () {
        Route::get('/points', [LoyaltyController::class, 'getCustomerPoints']);
        Route::post('/redeem', [LoyaltyController::class, 'redeemPoints']);
        Route::get('/tier-info', [TierController::class, 'customerTierInfo']);
        Route::get('/history', [LoyaltyController::class, 'getPointsHistory']);
    });
});