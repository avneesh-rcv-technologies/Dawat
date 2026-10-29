<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\HomeController;
use Modules\Shipping\Http\Controllers\Admin\ShippingMethodController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('shipping')->name('shipping.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
});

// Admin Routes
Route::prefix(config('core::prefix.admin'))->middleware('web')->name('admin.')->group(function () {
    Route::middleware(['check.permission'])->group(function () {
        
        Route::resource('shipping_methods', ShippingMethodController::class);
        Route::get('shipping_methods/config/{processorCode}', [ShippingMethodController::class, 'loadConfigForm'])->name('shipping_methods.config');
    });
}); 