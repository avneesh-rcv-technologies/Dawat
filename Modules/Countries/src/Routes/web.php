<?php

use Illuminate\Support\Facades\Route;
use Modules\Countries\Http\Controllers\CityController;
use Modules\Countries\Http\Controllers\StateController;
use Modules\Countries\Http\Controllers\CountryController;

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

    Route::prefix('settings')->middleware(['web','check.permission'])->name('settings.')->group(function () {
        Route::resource('countries', CountryController::class);
        Route::resource('states', StateController::class);
        Route::resource('cities', CityController::class);

    });
