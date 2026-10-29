<?php

use Illuminate\Support\Facades\Route;
use Modules\Acl\Http\Controllers\RolesController;
use Modules\Acl\Http\Controllers\CustomerController;
use Modules\Acl\Http\Controllers\SupportAdminController;

/*
|--------------------------------------------------------------------------
| ACL Module Web Routes
|--------------------------------------------------------------------------
|
| All guard-specific routes are automatically loaded from individual
| route files: admin.php, user.php, etc.
|
*/

Route::middleware('web')->group(function () {
    Route::middleware(['check.permission'])->group(function () {
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::resource('roles', RolesController::class);
            Route::post('/roles/bulk-delete', [RolesController::class, 'bulkDelete'])->name('roles.bulk-delete');
        });

        // Customer Management Routes
        Route::prefix('customers')->name('admin.customer.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/store', [CustomerController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
            Route::get('/{id}/view', [CustomerController::class, 'view'])->name('view');
            Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('delete');
            
            // Bulk operations
            Route::post('/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-status', [CustomerController::class, 'bulkStatus'])->name('bulk-status');
        });

        // Support Messages Management Routes
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [SupportAdminController::class, 'index'])->name('index');
            Route::get('/{id}', [SupportAdminController::class, 'show'])->name('show');
            Route::put('/{id}/status', [SupportAdminController::class, 'updateStatus'])->name('update-status-single');
            Route::delete('/{id}', [SupportAdminController::class, 'destroy'])->name('destroy');
            
            // Bulk operations
            Route::post('/bulk-delete', [SupportAdminController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/update-status', [SupportAdminController::class, 'bulkUpdateStatus'])->name('update-status');
        });
    });
});


