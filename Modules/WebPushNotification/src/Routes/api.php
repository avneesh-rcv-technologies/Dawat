<?php

use Illuminate\Support\Facades\Route;
use Modules\WebPushNotification\Http\Controllers\HomeController;

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

Route::prefix('api/firebase')->middleware('web')->name('api.firebase.')->group(function () {
    Route::post('subscribe', [HomeController::class, 'subscribe'])->name('subscribe');
    Route::post('unsubscribe', [HomeController::class, 'unsubscribe'])->name('unsubscribe');
});

Route::prefix('api/app/firebase')->name('api.firebase.')->group(function () {
    Route::post('subscribe', [HomeController::class, 'subscribe'])->name('subscribe');
    Route::post('unsubscribe', [HomeController::class, 'unsubscribe'])->name('unsubscribe');
});