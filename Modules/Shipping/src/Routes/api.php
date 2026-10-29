<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\Api\ShippingController;

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

Route::prefix('api/shipping')->name('api.shipping.')->group(function () {
    Route::post('/calculate', [ShippingController::class, 'calculate'])->name('calculate');
    Route::get('/methods', [ShippingController::class, 'methods'])->name('methods');
    Route::post('/process', [ShippingController::class, 'process'])->name('process');
    Route::get('/track/{trackingNumber}', [ShippingController::class, 'track'])->name('track');
    Route::put('/{shippingId}/status', [ShippingController::class, 'updateStatus'])->name('update-status');
}); 