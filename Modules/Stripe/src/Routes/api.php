<?php

use Illuminate\Support\Facades\Route;
use Modules\Stripe\Http\Controllers\Api\HomeController;
use Modules\Stripe\Http\Controllers\WebhookController;
use Modules\Stripe\Processors\StripeProcessor;

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


 
// Route::prefix('api/stripe')->name('api.stripe.')->group(function () {
//     Route::post('/webhook', [StripeProcessor::class, 'handleWebhook'])
//         ->withoutMiddleware(['auth:sanctum'])
//         ->name('webhook'); // Webhook must be public and not require authentication
// });

Route::post('/webhook/stripe', [StripeProcessor::class, 'handleWebhook'])->name('stripe.webhook'); // must be public

// Client calls this after Stripe confirms the PaymentIntent. The server verifies
// the intent with Stripe, so the client cannot mark an order paid by itself.
Route::post('/api/stripe/confirm-payment', [StripeProcessor::class, 'confirmPayment'])
    ->middleware('auth:sanctum')
    ->name('stripe.confirm-payment');


