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
// Owners are scoped to their restaurant; staff require the explicit refund permission.
Route::prefix(config('core::prefix.admin'))->middleware(['web', 'auth:admin'])
    ->name('admin.refunds.')->controller(\Modules\Payments\Http\Controllers\RefundController::class)->group(function () {
        Route::get('refunds', 'index')->name('index');
        Route::get('refunds/{id}', 'show')->whereNumber('id')->name('show');
        Route::patch('refunds/{id}', 'update')->whereNumber('id')->middleware('throttle:30,1')->name('update');
    });
