<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'admin'])
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('products', ProductController::class)->except('show');

        Route::post('products/{product}/images', [ProductImageController::class, 'store'])
            ->name('products.images.store');
        Route::put('product-images/{productImage}', [ProductImageController::class, 'update'])
            ->name('product-images.update');
        Route::delete('product-images/{productImage}', [ProductImageController::class, 'destroy'])
            ->name('product-images.destroy');

        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('coupons', CouponController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });
