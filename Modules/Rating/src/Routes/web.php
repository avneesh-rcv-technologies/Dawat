<?php

use Illuminate\Support\Facades\Route;
use Modules\Rating\Http\Controllers\RatingController;

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

Route::middleware(['web', 'check.permission'])->name('ratings.')->group(function () {
    Route::get('/ratings', [RatingController::class, 'index'])->name('index');
    Route::get('/ratings/create', [RatingController::class, 'create'])->name('create');
    Route::post('/ratings', [RatingController::class, 'store'])->name('store');
    Route::get('/ratings/{id}', [RatingController::class, 'show'])->name('show');
    Route::get('/ratings/{id}/edit', [RatingController::class, 'edit'])->name('edit');
    Route::put('/ratings/{id}', [RatingController::class, 'update'])->name('update');
    Route::delete('/ratings/{id}', [RatingController::class, 'destroy'])->name('destroy');
    Route::post('/ratings/{id}/approve', [RatingController::class, 'approve'])->name('approve');
    Route::post('/ratings/{id}/toggle-status', [RatingController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/ratings/bulk-delete', [RatingController::class, 'bulkDelete'])->name('bulk-delete');
});


// HomeController will be generated automatically by the module generator 