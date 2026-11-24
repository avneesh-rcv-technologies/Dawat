<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Http\Controllers\Api\HomeController;
use Modules\Payments\Http\Controllers\Api\OrderRefundController;
use Modules\Payments\Http\Controllers\Api\RefundController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group. Now create something great!
|
*/

Route::prefix('api/payment')->name('api.payment.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
});

// Api\HomeController will be generated automatically by the module generator
Route::prefix('api/payment/refunds')->middleware('auth:sanctum')->name('api.payment.refunds.')
    ->controller(RefundController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->whereNumber('id')->name('show');
        Route::patch('/{id}', 'update')->whereNumber('id')->middleware('throttle:30,1')->name('update');
        Route::patch('/{id}/status', 'updateStatus')->whereNumber('id')->middleware('throttle:30,1')->name('status.update');
    });
Route::get('api/orders/{orderId}/refunds', [OrderRefundController::class, 'index'])
    ->whereNumber('orderId')->middleware('auth:sanctum')->name('api.orders.refunds');
