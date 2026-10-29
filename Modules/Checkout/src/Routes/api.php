<?php

use Illuminate\Support\Facades\Route;
use Modules\Checkout\Http\Controllers\Api\CheckoutController;

/*
|--------------------------------------------------------------------------
| Checkout API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/checkout')->middleware(['optional.customer.auth'])->name('api.checkout.')->group(function () {
    // Step 1A: Save Delivery Details with Address (One API for Delivery)
    Route::post('save-delivery-details', [CheckoutController::class, 'saveDeliveryDetails'])->name('save-delivery-details');

    // Step 1B: Save Pickup Details (One API for Pickup)
    Route::post('save-pickup-details', [CheckoutController::class, 'savePickupDetails'])->name('save-pickup-details');

    // Step 2: Get Restaurant Address (for Pickup method - optional)
    Route::get('restaurant-address/{restaurantId}', [CheckoutController::class, 'getRestaurantAddress'])->name('restaurant-address');

    Route::get('food-categories', [CheckoutController::class, 'getFoodCategories'])->name('food-categories');

    // Step 3: Food Items — categories + tabs + filtered items (single API)
    Route::get('food-items', [CheckoutController::class, 'getFoodItems'])->name('food-items');

    // Step 4: Payment Methods
    Route::get('payment-methods', [CheckoutController::class, 'getPaymentMethods'])->name('payment-methods');
    Route::post('set-payment-method', [CheckoutController::class, 'setPaymentMethod'])->name('set-payment-method');

    // Step 5: Review Order
    Route::get('review-order', [CheckoutController::class, 'reviewOrder'])->name('review-order');

    // Step 6: Place Order
    Route::post('place-order', [CheckoutController::class, 'placeOrder'])->name('place-order');

    // Helper: Get Customer's Saved Addresses
    Route::get('my-addresses', [CheckoutController::class, 'getMyAddresses'])->name('my-addresses');
});
