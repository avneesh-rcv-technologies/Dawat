<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes — Website Module
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {
    Route::get('/',            [HomeController::class, 'index'])->name('website.home');
    Route::get('/about',        [HomeController::class, 'about'])->name('website.about');
    Route::get('/how-it-works', [HomeController::class, 'howItWorks'])->name('website.how-it-works');
    Route::get('/blogs',        [HomeController::class, 'blogs'])->name('website.blogs');
    Route::get('/blogs/{slug}', [HomeController::class, 'blogDetail'])->name('website.blog-detail');
    Route::get('/contact',      [HomeController::class, 'contact'])->name('website.contact');
    Route::get('/partner',      [HomeController::class, 'partner'])->name('website.partner');
});
