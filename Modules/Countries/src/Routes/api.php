<?php

use Illuminate\Support\Facades\Route;
use Modules\Countries\Http\Controllers\Api\HomeController;

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

Route::prefix('api/countries')->name('api.countries.')->group(function () {
    Route::get('/' , [\Modules\Countries\Http\Controllers\CountryController::class , 'getCountry'])->name('country');
    Route::get('/states-by-country/{countryId}', [\Modules\Countries\Http\Controllers\StateController::class, 'getByCountry'])->name('states-by-country');
    Route::get('/cities-by-state/{stateId}', [\Modules\Countries\Http\Controllers\CityController::class, 'getByState'])->name('cities-by-state');
    Route::get('/cities-by-country/{countryId}', [\Modules\Countries\Http\Controllers\CityController::class, 'getByCountry'])->name('cities-by-country');
});

// Api\HomeController will be generated automatically by the module generator 