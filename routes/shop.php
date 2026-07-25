<?php

use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('cart', [CartController::class, 'show'])->name('cart.show');
Route::post('cart/items', [CartController::class, 'storeItem'])->name('cart.items.store');
Route::put('cart/items/{cartItem}', [CartController::class, 'updateItem'])->name('cart.items.update');
Route::delete('cart/items/{cartItem}', [CartController::class, 'destroyItem'])->name('cart.items.destroy');
