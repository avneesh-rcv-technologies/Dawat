<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\HomeController;

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

Route::prefix('core')->name('core.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
});

// HomeController will be generated automatically by the module generator 