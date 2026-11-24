<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\HomeController;
use Modules\Restaurant\Http\Controllers\RestaurantAuthController;
use Modules\Website\Http\Controllers\ContactQueryController;

/*
|--------------------------------------------------------------------------
| Web Routes — Website Module
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {
    Route::get('/settings/contact-queries', [ContactQueryController::class, 'index'])
        ->middleware('check.permission:admin')
        ->name('website.contact-queries.index');

    Route::post('/settings/contact-queries/bulk-delete', [ContactQueryController::class, 'bulkDelete'])
        ->middleware('check.permission:admin')
        ->name('website.contact-queries.bulk-delete');

    Route::get('/settings/contact-queries/{contactQuery}', [ContactQueryController::class, 'show'])
        ->middleware('check.permission:admin')
        ->name('website.contact-queries.show');
    Route::delete('/settings/contact-queries/{contactQuery}', [ContactQueryController::class, 'destroy'])
        ->middleware('check.permission:admin')
        ->name('website.contact-queries.destroy');

    Route::get('/',            [HomeController::class, 'index'])->name('website.home');
    Route::get('/about',        [HomeController::class, 'about'])->name('website.about');
    Route::get('/how-it-works', [HomeController::class, 'howItWorks'])->name('website.how-it-works');
    Route::get('/blogs',        [HomeController::class, 'blogs'])->name('website.blogs');
    Route::get('/blogs/preview/{slug}', [HomeController::class, 'blogPreview'])->middleware('signed')->name('website.blog-preview');
    Route::get('/blogs/{slug}', [HomeController::class, 'blogDetail'])->name('website.blog-detail');
    Route::get('/authors/{slug}', [HomeController::class, 'authorDetail'])->name('website.author-detail');
    Route::post('/contact', [ContactQueryController::class, 'store'])->middleware('throttle:5,1,website-contact')->name('website.contact.store');
    Route::get('/contact',      [HomeController::class, 'contact'])->name('website.contact');
    Route::get('/partner',      [HomeController::class, 'partner'])->name('website.partner');
    Route::post('/partner/verify-captcha', [HomeController::class, 'verifyPartnerCaptcha'])
        ->middleware('throttle:5,1,website-partner-interest')->name('website.partner.verify-captcha');
    Route::get('/partner/sign-up', [HomeController::class, 'partnerSignup'])->name('website.partner-signup');
    Route::post('/partner/sign-up/verify-captcha', [HomeController::class, 'verifyPartnerSignupCaptcha'])
        ->middleware('throttle:5,1,website-partner-signup-captcha')->name('website.partner-signup.verify-captcha');
    Route::get('/partner/login', [RestaurantAuthController::class, 'showLoginForm'])->name('website.partner-login');
    Route::post('/partner/login', [RestaurantAuthController::class, 'login'])
        ->middleware(['throttle:restaurant-partner-login', 'password.transport'])->name('website.partner.login');
    Route::get('/partner/forgot-password', [RestaurantAuthController::class, 'showForgotPasswordForm'])->name('website.partner.password.request');
    Route::post('/partner/forgot-password', [RestaurantAuthController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:restaurant-partner-forgot-password')->name('website.partner.password.email');
    Route::get('/partner/reset-password/{token}', [RestaurantAuthController::class, 'showResetPasswordForm'])->name('website.partner.password.reset');
    Route::post('/partner/reset-password', [RestaurantAuthController::class, 'reset'])
        ->middleware(['throttle:restaurant-partner-reset-password', 'password.transport'])->name('website.partner.password.update');
    Route::get('/partner/email/verify/{id}/{hash}', [RestaurantAuthController::class, 'verifyEmail'])
        ->middleware('throttle:6,1,partner-verification')->whereNumber('id')->name('website.partner.verification.verify');
    Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('website.privacy-policy');
    Route::get('/terms-and-conditions', [HomeController::class, 'termsAndConditions'])->name('website.terms-and-conditions');
});
