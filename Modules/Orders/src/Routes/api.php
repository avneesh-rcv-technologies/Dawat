<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\Api\OrderController;
use Modules\Orders\Http\Controllers\Api\OrderTrackingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group. Now create something great!
|
*/

// Public API routes for fetching orders (require authentication)
Route::prefix('api/orders')->name('api.orders.')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/cod/list', [OrderController::class, 'getCodOrders'])->name('cod_orders');
    Route::get('/track', [OrderTrackingController::class, 'trackOrder'])->name('track');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    
    // Order cancellation routes
    Route::get('/{orderId}/can-cancel', [OrderController::class, 'checkCancellation'])->name('check_cancellation');
    Route::post('/{orderId}/cancel', [OrderController::class, 'cancelOrder'])->name('cancel');
});

// Restaurant cancellation policy (public route)
Route::get('api/restaurants/{restaurantId}/cancellation-policy', [OrderController::class, 'getRestaurantCancellationPolicy'])->name('api.restaurants.cancellation_policy');

// Internal route used by admin UI for status updates
Route::prefix('api/cart')->name('api.cart.')->group(function () {
    Route::post('order/{order}/update_status', [OrderController::class, 'updateStatus'])->name('order.update_status');
});

// Note: Cart item operations (quantity, remove, discount) are handled by the Cart module API
// Routes available:
// - POST /api/cart/{restaurantId}/item/{cartItemId} (update quantity)
// - DELETE /api/cart/{restaurantId}/item/{cartItemId} (remove item)  
// - DELETE /api/cart/{restaurantId}/remove-coupon (remove discount) 