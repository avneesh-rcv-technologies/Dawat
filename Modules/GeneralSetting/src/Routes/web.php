<?php

use Illuminate\Support\Facades\Route;
use Modules\GeneralSetting\Http\Controllers\GeneralController;
use Modules\GeneralSetting\Http\Controllers\SettingController;
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
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('clear-cache', [SettingController::class, 'clearCache'])->name('clear_cache');
            Route::get('configuration', [SettingController::class, 'configuration'])->name('configuration');
            Route::post('store', [SettingController::class, 'store'])->name('store');
        });
    });
});
