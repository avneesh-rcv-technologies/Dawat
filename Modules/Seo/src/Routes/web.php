<?php

use Illuminate\Support\Facades\Route;
use Modules\Seo\Http\Controllers\Admin\AnalyticsController;
use Modules\Seo\Http\Controllers\Admin\PageController;
use Modules\Seo\Http\Controllers\Admin\RedirectController;
use Modules\Seo\Http\Controllers\Admin\RobotsController as AdminRobotsController;
use Modules\Seo\Http\Controllers\Admin\SettingsController;
use Modules\Seo\Http\Controllers\Admin\SitemapController as AdminSitemapController;
use Modules\Seo\Http\Controllers\RobotsController;
use Modules\Seo\Http\Controllers\SitemapController;

Route::middleware('web')->group(function () {
    Route::get(config('seo.sitemap.path', '/sitemap.xml'), [SitemapController::class, 'index'])
        ->name('seo.sitemap');
    Route::get(config('seo.robots.path', '/robots.txt'), [RobotsController::class, 'index'])
        ->name('seo.robots');

    Route::prefix(config('core::prefix.admin', '') . '/seo')
        ->middleware(['check.permission'])
        ->name('admin.seo.')
        ->group(function () {
            Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

            Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
            Route::put('analytics', [AnalyticsController::class, 'update'])->name('analytics.update');

            Route::get('pages', [PageController::class, 'index'])->name('pages.index');
            Route::get('pages/{pageKey}/edit', [PageController::class, 'edit'])->name('pages.edit');
            Route::put('pages/{pageKey}', [PageController::class, 'update'])->name('pages.update');

            Route::get('sitemap', [AdminSitemapController::class, 'index'])->name('sitemap.index');
            Route::post('sitemap/regenerate', [AdminSitemapController::class, 'regenerate'])->name('sitemap.regenerate');

            Route::get('robots', [AdminRobotsController::class, 'index'])->name('robots.index');
            Route::put('robots', [AdminRobotsController::class, 'update'])->name('robots.update');

            Route::resource('redirects', RedirectController::class)->except(['show']);
            Route::post('redirects/import', [RedirectController::class, 'import'])
                ->name('redirects.import');
        });
});
