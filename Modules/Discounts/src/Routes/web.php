<?php

use Illuminate\Support\Facades\Route;
use Modules\Discounts\Http\Controllers\DiscountController;

Route::middleware('web')->group(function () {

    Route::middleware(['check.permission'])->group(function () {

        // Discount CRUD
        Route::resource('discount', DiscountController::class);

        // Toggle discount status
        Route::post('/discount/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])->name('discount.toggle-status');

        // AJAX coupon validation
        Route::post('/discount/validate-coupon', [DiscountController::class, 'validateCoupon'])->name('discount.validate-coupon');
    });

    // Frontend coupon application
    Route::post('/apply-coupon', [DiscountController::class, 'applyCoupon'])->name('apply-coupon');
});
