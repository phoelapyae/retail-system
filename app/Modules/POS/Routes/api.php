<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Controllers\{
    SaleController, 
    RegisterController, 
    ReportController,
};

// Group all POS routes with module prefix and middleware
Route::group([
    'prefix' => 'pos',
    'middleware' => ['api', 'module:pos']
], function () {
    
    // Sales Management Routes
    Route::prefix('sales')->name('pos.sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::post('/', [SaleController::class, 'create'])->name('create');
        Route::get('/{id}', [SaleController::class, 'show'])->name('show');
        Route::put('/{id}', [SaleController::class, 'update'])->name('update');
        Route::delete('/{id}', [SaleController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/complete', [SaleController::class, 'complete'])->name('complete');
        Route::get('/{id}/receipt', [SaleController::class, 'receipt'])->name('receipt');
        Route::post('/{id}/email-receipt', [SaleController::class, 'emailReceipt'])->name('email-receipt');
    });
    
    // Register Management Routes
    Route::prefix('registers')->name('pos.registers.')->group(function () {
        Route::get('/', [RegisterController::class, 'index'])->name('index');
        Route::post('/', [RegisterController::class, 'store'])->name('store');
        Route::get('/{id}', [RegisterController::class, 'show'])->name('show');
        Route::put('/{id}', [RegisterController::class, 'update'])->name('update');
        Route::delete('/{id}', [RegisterController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/open', [RegisterController::class, 'open'])->name('open');
        Route::post('/{id}/close', [RegisterController::class, 'close'])->name('close');
        Route::get('/{id}/status', [RegisterController::class, 'status'])->name('status');
        Route::get('/{id}/daily-report', [RegisterController::class, 'dailyReport'])->name('daily-report');
        Route::get('/{id}/transactions', [RegisterController::class, 'transactions'])->name('transactions');
    });
    
    // Reports Routes
    Route::prefix('reports')->name('pos.reports.')->group(function () {
        Route::get('/daily-sales', [ReportController::class, 'dailySales'])->name('daily-sales');
        Route::get('/monthly-sales', [ReportController::class, 'monthlySales'])->name('monthly-sales');
        Route::get('/cashier-performance', [ReportController::class, 'cashierPerformance'])->name('cashier-performance');
        Route::get('/product-performance', [ReportController::class, 'productPerformance'])->name('product-performance');
        Route::get('/payment-methods', [ReportController::class, 'paymentMethods'])->name('payment-methods');
        Route::get('/hourly-breakdown', [ReportController::class, 'hourlyBreakdown'])->name('hourly-breakdown');
    });
    
    // Quick Actions
    Route::prefix('quick')->name('pos.quick.')->group(function () {
        Route::get('/products/search', [SaleController::class, 'searchProducts'])->name('search-products');
        Route::get('/customers/search', [SaleController::class, 'searchCustomers'])->name('search-customers');
        Route::post('/hold-sale', [SaleController::class, 'holdSale'])->name('hold-sale');
        Route::get('/held-sales', [SaleController::class, 'heldSales'])->name('held-sales');
        Route::post('/retrieve-sale/{id}', [SaleController::class, 'retrieveSale'])->name('retrieve-sale');
    });
});