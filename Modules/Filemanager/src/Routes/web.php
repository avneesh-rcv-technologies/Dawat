<?php

use Illuminate\Support\Facades\Route;
use Modules\Filemanager\Http\Controllers\HomeController;

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
        Route::delete('filemanager/{id}/delete', [HomeController::class, 'deleteFile'])->middleware(\Modules\Restaurant\Http\Middleware\EnsureRestaurantFeaturesApproved::class . ':file')->name('filemanager.delete');
    });
}); 

// HomeController will be generated automatically by the module generator
