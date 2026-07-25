<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::put('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::redirect('dashboard', '/orders')->name('dashboard');

require __DIR__.'/shop.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
