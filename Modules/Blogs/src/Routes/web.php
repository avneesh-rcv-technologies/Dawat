<?php

use Illuminate\Support\Facades\Route;

use Modules\Blogs\Http\Controllers\Blogs\BlogController;
use Modules\Blogs\Http\Controllers\Blogs\BlogAuthorController;
use Modules\Blogs\Http\Controllers\Blogs\BlogCategoryController;
use Modules\Blogs\Http\Controllers\Blogs\BlogCtaController;


Route::middleware('web')->group(function () {
    Route::middleware(['check.permission'])->group(function () {
        Route::prefix(config('core::prefix.admin'))->name('admin.')->group(function () {
            Route::prefix('blogs')->name('blogs.')->group(function () {
                // Blogs
                Route::get('blogs/{id}/preview', [BlogController::class, 'preview'])
                    ->name('blogs.preview');
                Route::resource('blogs', BlogController::class);
                Route::post('blogs/content-image', [BlogController::class, 'uploadContentImage'])
                    ->name('blogs.content-image');
                Route::match(['delete', 'post'], 'blogs/bulk-delete', [BlogController::class, 'bulkDelete'])
                    ->name('blogs.bulk-delete');
                Route::post('blogs/toggle-status', [BlogController::class, 'toggleStatus'])
                    ->name('blogs.toggle-status');

                // Blog Authors
                Route::resource('blog_authors', BlogAuthorController::class);
                Route::match(['delete', 'post'], 'blog_authors/bulk-delete', [BlogAuthorController::class, 'bulkDelete'])
                    ->name('blog_authors.bulk-delete');
                Route::post('blog_authors/toggle-status', [BlogAuthorController::class, 'toggleStatus'])
                    ->name('blog_authors.toggle-status');

                // Blog CTAs
                Route::resource('blog_ctas', BlogCtaController::class);
                Route::match(['delete', 'post'], 'blog_ctas/bulk-delete', [BlogCtaController::class, 'bulkDelete'])
                    ->name('blog_ctas.bulk-delete');
                Route::post('blog_ctas/toggle-status', [BlogCtaController::class, 'toggleStatus'])
                    ->name('blog_ctas.toggle-status');

                // Blog Categories
                Route::resource('blog_categories', BlogCategoryController::class);
                Route::match(['delete', 'post'], 'blog_categories/bulk-delete', [BlogCategoryController::class, 'bulkDelete'])
                    ->name('blog_categories.bulk-delete');
                Route::post('blog_categories/toggle-status', [BlogCategoryController::class, 'toggleStatus'])
                    ->name('blog_categories.toggle-status');
            });
        });
    });
});
