<?php

use Illuminate\Support\Facades\Route;
use Modules\Tax\Http\Controllers\TaxCategoryController;
use Modules\Tax\Http\Controllers\TaxController;
use Modules\Tax\Http\Controllers\TaxRuleController;

Route::prefix(config('core::prefix.admin'))->middleware('web')->name('admin.')->group(function () {
    Route::middleware(['check.permission'])->group(function () {

        // Tax CRUD
        Route::resource('tax', TaxController::class);
        Route::post('/tax/{tax}/toggle-status', [TaxController::class, 'toggleStatus'])->name('tax.toggle-status');

        Route::resource('tax-category', TaxCategoryController::class);
        Route::resource('tax-rules', TaxRuleController::class);

    });
});
