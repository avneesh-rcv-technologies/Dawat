<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\Transaction;
use Modules\Orders\Http\Controllers\HomeController;
use Modules\Orders\Http\Controllers\OrderController;
use Modules\Orders\Http\Controllers\Orders\Productlists;
use Modules\Orders\Http\Controllers\Statuses\OrdersStatusController;

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
Route::prefix(config('core::prefix.admin'))->middleware('web')->name('admin.')->group(function () {
    Route::middleware(['check.permission'])->group(function () {
        
        Route::get('orders/analytics', [OrderController::class, 'analytics'])->name('orders.analytics');
        Route::resource('orders', OrderController::class);
        Route::post('orders/bulk-delete', [OrderController::class, 'bulkDelete'])->name('orders.bulk-delete');
        Route::post('orders/toggle-status', [OrderController::class, 'updateStatus'])->name('orders.toggle-status');
        Route::post('orders/{order}/update-status', [OrderController::class, 'updateSingleStatus'])->name('orders.update-status');

        // order edit
        Route::get('transactions/analytics', [Transaction::class, 'analytics'])->name('transactions.analytics');
        Route::resource('transactions', Transaction::class);
        Route::post('transactions/bulk-delete', [Transaction::class, 'bulkDelete'])->name('transactions.bulk-delete');
        Route::get('transactions/reload/{id}', [Transaction::class, 'reload'])->name('transactions.reload');
        Route::post('transactions/toggle-status', [Transaction::class, 'updateStatus'])->name('transactions.toggle-status');


        Route::resource('orders-statuses', OrdersStatusController::class);

        Route::get('/orders/{order}/view-attachment', [OrderController::class, 'viewAttachment'])->name('orders.view-attachment');
        Route::post('/orders/{order}/store-attachment', [OrderController::class, 'storeAttachment'])->name('orders.store-attachment');
        Route::delete('/attachments/{id}', [OrderController::class, 'deleteAttachment'])->name('attachments.destroy');
        
        // Update attachment status
        Route::put('/orders/{order}/attachment', [OrderController::class, 'updateAttachmentStatus'])
            ->name('orders.attachment.update');

        Route::post('orders/{order}/set-payment-method',[OrderController::class,'updateMethod'])->name('orders.setPaymentMethod');

    });
});
// HomeController will be generated automatically by the module generator 