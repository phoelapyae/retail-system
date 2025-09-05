<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Ecommerce\Controllers\{
    ProductController,
    OrderController,
};

Route::group([
    'prefix' => 'ecommerce',
    'middleware' => ['api', 'module:ecommerce']
], function () {
    
    // Product Management Routes
    Route::prefix('products')->name('ecommerce.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::get('/featured', [ProductController::class, 'featured'])->name('featured');
        Route::get('/low-stock', [ProductController::class, 'lowStock'])->name('low-stock');
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::put('/{id}/stock', [ProductController::class, 'updateStock'])->name('update-stock');
        
        // Product Images
        Route::post('/{id}/images', [ProductController::class, 'uploadImages'])->name('upload-images');
        Route::delete('/{id}/images/{imageId}', [ProductController::class, 'deleteImage'])->name('delete-image');
    });
    
    // Order Management Routes
    Route::prefix('orders')->name('ecommerce.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'create'])->name('create');
        Route::get('/export', [OrderController::class, 'export'])->name('export');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::put('/{id}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{id}', [OrderController::class, 'cancel'])->name('cancel');
        Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->name('update-status');
        Route::put('/{id}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('update-payment-status');
        Route::post('/{id}/ship', [OrderController::class, 'ship'])->name('ship');
        Route::post('/{id}/deliver', [OrderController::class, 'deliver'])->name('deliver');
        Route::post('/{id}/refund', [OrderController::class, 'refund'])->name('refund');
        Route::get('/{id}/invoice', [OrderController::class, 'invoice'])->name('invoice');
        Route::post('/{id}/send-invoice', [OrderController::class, 'sendInvoice'])->name('send-invoice');
    });
});