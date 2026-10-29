<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Http\Controllers\HomeController;
use Modules\Payments\Http\Controllers\Payments;

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

Route::prefix(config('core::prefix.admin'))->middleware('web')->name('admin.')->group(function () {
    Route::middleware(['check.permission'])->group(function () {
        
        Route::resource('payments', Payments::class);
        Route::resource('payment_methods', HomeController::class);

        Route::get('payment_methods/config/{code}', [HomeController::class, 'loadConfigForm'])->name('payment_methods.config');
    });
});