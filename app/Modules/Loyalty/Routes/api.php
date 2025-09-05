<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Loyalty\Controllers\{
    LoyaltyController,
    TierController,
};

Route::group([
    'prefix' => 'loyalty',
    'middleware' => ['api', 'module:loyalty']
], function () {
    
    // Customer Loyalty Routes
    Route::prefix('customers/{customerId}')->name('loyalty.customers.')->group(function () {
        Route::get('/profile', [LoyaltyController::class, 'getCustomerProfile'])->name('profile');
        Route::get('/points', [LoyaltyController::class, 'getCustomerPoints'])->name('points');
        Route::get('/points/history', [LoyaltyController::class, 'getPointsHistory'])->name('points.history');
        Route::get('/points/expiring', [LoyaltyController::class, 'getExpiringPoints'])->name('points.expiring');
        Route::post('/points/redeem', [LoyaltyController::class, 'redeemPoints'])->name('points.redeem');
        Route::post('/points/transfer', [LoyaltyController::class, 'transferPoints'])->name('points.transfer');
        Route::get('/tier-info', [TierController::class, 'customerTierInfo'])->name('tier-info');
    });
    
    // Tier Management Routes
    Route::prefix('tiers')->name('loyalty.tiers.')->group(function () {
        Route::get('/', [TierController::class, 'index'])->name('index');
        Route::post('/', [TierController::class, 'store'])->name('store');
        Route::get('/{id}', [TierController::class, 'show'])->name('show');
        Route::put('/{id}', [TierController::class, 'update'])->name('update');
        Route::delete('/{id}', [TierController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [TierController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{id}/customers', [TierController::class, 'tierCustomers'])->name('customers');
        Route::post('/reorder', [TierController::class, 'reorder'])->name('reorder');
    });
    
    // Analytics and Reports
    Route::prefix('analytics')->name('loyalty.analytics.')->group(function () {
        Route::get('/dashboard', [LoyaltyController::class, 'dashboard'])->name('dashboard');
        Route::get('/customer-segments', [LoyaltyController::class, 'customerSegments'])->name('customer-segments');
        Route::get('/engagement-metrics', [LoyaltyController::class, 'engagementMetrics'])->name('engagement-metrics');
        Route::get('/roi-report', [LoyaltyController::class, 'roiReport'])->name('roi-report');
        Route::get('/tier-performance', [TierController::class, 'tierPerformance'])->name('tier-performance');
    });
});