<?php

use Illuminate\Support\Facades\Route;
use Modules\Rating\Http\Controllers\RatingController;
use Modules\Rating\Http\Controllers\RatingWorkflowController;

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

Route::middleware(['web', 'auth:admin'])->prefix('admin/ratings')->name('ratings.workflow.admin.')->group(function () {
    Route::get('/', [RatingWorkflowController::class, 'adminIndex'])->name('index');
    Route::get('/{id}/details', [RatingWorkflowController::class, 'adminReviewDetails'])->whereNumber('id')->name('details');
    Route::post('/{id}/decision', [RatingWorkflowController::class, 'decideAdmin'])->whereNumber('id')->name('decision');
    Route::post('/{id}/approve', [RatingWorkflowController::class, 'quickApprove'])->whereNumber('id')->name('quick-approve');
    Route::post('/{id}/reject', [RatingWorkflowController::class, 'quickReject'])->whereNumber('id')->name('quick-reject');
});

Route::middleware(['web', 'auth:admin'])->prefix('restaurant/ratings')->name('restaurant.ratings.')->group(function () {
    Route::get('/', [RatingWorkflowController::class, 'ownerIndex'])->name('index');
    Route::get('/{id}/details', [RatingWorkflowController::class, 'ownerReviewDetails'])->whereNumber('id')->name('details');
    Route::post('/{id}/decision', [RatingWorkflowController::class, 'decideOwner'])->whereNumber('id')->name('decision');
    Route::post('/{id}/reply', [RatingWorkflowController::class, 'reply'])->whereNumber('id')->name('reply');
});

Route::middleware(['web', 'auth:customer'])->prefix('orders/{orderId}/ratings')->where(['orderId' => '[0-9]+'])->name('customer.ratings.')->group(function () {
    Route::get('/', [RatingWorkflowController::class, 'customerForm'])->name('form');
    Route::post('/', [RatingWorkflowController::class, 'submit'])->name('submit');
});


// HomeController will be generated automatically by the module generator
