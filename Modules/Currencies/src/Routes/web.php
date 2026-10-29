<?php

use Illuminate\Support\Facades\Route;
use Modules\Currencies\Http\Controllers\CurrencyController;

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

Route::middleware('web')->group(function () {
    Route::middleware(['check.permission'])->group(function () {
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::resource('currencies', CurrencyController::class);
        });
    });
});
