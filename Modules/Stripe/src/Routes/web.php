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
// The signed link is a bearer link for this order's existing PaymentIntent.
// Stripe's return parameters are ignored for signing and never trusted as payment proof.
Route::get('/stripe/order-payment/{order}/{reference}', [\Modules\Stripe\Http\Controllers\OrderPaymentController::class, 'show'])
    ->whereNumber('order')->where('reference', 'pi_[a-zA-Z0-9]+')
    ->middleware(['web', 'signed:payment_intent,payment_intent_client_secret,redirect_status', 'throttle:30,1'])
    ->name('stripe.payment.show');
