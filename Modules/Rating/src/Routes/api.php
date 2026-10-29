<?php

use Illuminate\Support\Facades\Route;
use Modules\Rating\Http\Controllers\Api\RatingApiController;

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
    // Public API routes for ratings
    Route::prefix('ratings')->name('ratings.')->group(function () {
        Route::get('/', [RatingApiController::class, 'index'])->name('index');
        Route::post('/', [RatingApiController::class, 'store'])->name('store');
        Route::get('/stats', [RatingApiController::class, 'stats'])->name('stats');
        Route::get('/{id}', [RatingApiController::class, 'show'])->name('show');
    });
});


// Api\HomeController will be generated automatically by the module generator 