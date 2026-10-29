<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\Api\CartApiController;

/*
|--------------------------------------------------------------------------
| Cart API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Cart module. These routes handle
| cart operations like adding items, updating quantities, removing items,
| and clearing the cart.
|
| Authentication: These routes support both guest users (session-only) 
| and authenticated customers (Bearer token + session).
|
*/

Route::prefix('api/cart')->name('api.cart.')->group(function () {
    // Apply optional customer authentication middleware
    Route::middleware(['optional.customer.auth'])->group(function () {
        // Get cart details
        Route::get('/', [CartApiController::class, 'getCart'])->name('get');

        // Add item to cart
        Route::post('/add-items', [CartApiController::class, 'addToCart'])->name('add');

        // Clear entire cart
        Route::delete('/clear', [CartApiController::class, 'clearCart'])->name('clear');

        // Get cart count
        Route::get('/count', [CartApiController::class, 'getCartCount'])->name('count');
        // Update cart item quantity
        Route::put('/update-item', [CartApiController::class, 'updateCartItem'])->name('update-item');

        // Remove item from cart
        Route::delete('/remove-item', [CartApiController::class, 'removeFromCart'])->name('remove-item');
        
        // Coupon operations
        Route::post('/apply-coupon', [CartApiController::class, 'applyCoupon'])->name('apply-coupon');
        Route::delete('/remove-coupon', [CartApiController::class, 'removeCoupon'])->name('remove-coupon');
       
    });
});
