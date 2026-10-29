<?php

use Illuminate\Support\Facades\Route;
use Modules\Stripe\Processors\StripeProcessor;
use Modules\Stripe\Http\Controllers\HomeController;

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


Route::prefix('stripe')->middleware('web')->name('stripe.')->group(function () {
    Route::get('/checkout/cancel', [StripeProcessor::class , 'cancel'])->name('checkout.cancel');
});