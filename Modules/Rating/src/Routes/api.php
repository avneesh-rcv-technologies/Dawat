<?php

use Illuminate\Support\Facades\Route;
use Modules\Rating\Http\Controllers\Api\RatingApiController;
use Modules\Rating\Http\Controllers\RatingWorkflowController;

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

Route::prefix('api/v1')->name('api.')->group(function () {
    Route::middleware('auth:sanctum')->get('/ratings/orders/{orderId}', [RatingApiController::class, 'byOrder'])->whereNumber('orderId')->name('ratings.orders.show');
    Route::middleware('auth:sanctum')->get('/ratings/orders/{orderId}/form', [RatingWorkflowController::class, 'orderForm'])->whereNumber('orderId')->name('ratings.orders.form');
    Route::middleware('auth:sanctum')->post('/ratings/orders/submit', [RatingWorkflowController::class, 'submit'])->name('ratings.orders.submit');
    // Public API routes for ratings
    Route::prefix('ratings')->name('ratings.')->group(function () {
        Route::get('/', [RatingApiController::class, 'index'])->name('index');
        Route::post('/', [RatingApiController::class, 'store'])->name('store');
        Route::get('/stats', [RatingApiController::class, 'stats'])->name('stats');
        Route::get('/{id}', [RatingApiController::class, 'show'])->name('show');
    });
});


// Api\HomeController will be generated automatically by the module generator
