<?php

use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CartCouponController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\ReviewController;
use App\Http\Controllers\Shop\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('cart', [CartController::class, 'show'])->name('cart.show');
Route::post('cart/items', [CartController::class, 'storeItem'])->name('cart.items.store');
Route::put('cart/items/{cartItem}', [CartController::class, 'updateItem'])->name('cart.items.update');
Route::delete('cart/items/{cartItem}', [CartController::class, 'destroyItem'])->name('cart.items.destroy');
Route::post('cart/coupon', [CartCouponController::class, 'store'])->name('cart.coupon.store');
Route::delete('cart/coupon', [CartCouponController::class, 'destroy'])->name('cart.coupon.destroy');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::post('products/{product:slug}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');
});
