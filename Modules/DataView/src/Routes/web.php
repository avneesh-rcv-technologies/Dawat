<?php

use Illuminate\Support\Facades\Route;
use Modules\DataView\Http\Controllers\HomeController;

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

Route::prefix('dataview')->name('dataview.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('export', [HomeController::class, 'export'])->name('export');
});

// HomeController will be generated automatically by the module generator 