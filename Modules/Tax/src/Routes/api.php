<?php

use Illuminate\Support\Facades\Route;
use Modules\Tax\Http\Controllers\Api\HomeController;
use Modules\Tax\Http\Controllers\Api\FoodItemTaxController;

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

Route::prefix('api/tax')->name('api.tax.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    
    // Food Item Tax Routes
    Route::get('/configuration', [FoodItemTaxController::class, 'getTaxConfiguration'])->name('configuration');
    Route::get('/food-item/{foodItemId}/calculate', [FoodItemTaxController::class, 'calculateTax'])->name('food-item.calculate');
    Route::post('/cart/calculate', [FoodItemTaxController::class, 'calculateCartTax'])->name('cart.calculate');
    Route::post('/subtotal/calculate', [FoodItemTaxController::class, 'calculateSubtotalTax'])->name('subtotal.calculate');
});

// Api\HomeController will be generated automatically by the module generator 