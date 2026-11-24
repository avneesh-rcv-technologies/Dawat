<?php

namespace Modules\Seo\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event): void
    {
        $event->registerArray([
            'name' => 'SEO',
            'code' => 'seo_settings',
            'guard' => ['admin'],
            'key' => [
                'admin.seo.settings.index' => 'View SEO Configuration',
                'admin.seo.settings.update' => 'Update SEO Configuration',
            ],
        ]);

        $event->registerArray([
            'name' => 'SEO',
            'code' => 'seo_analytics',
            'guard' => ['admin'],
            'key' => [
                'admin.seo.analytics.index' => 'View SEO Analytics',
                'admin.seo.analytics.update' => 'Update SEO Analytics',
            ],
        ]);

        $event->registerArray([
            'name' => 'SEO',
            'code' => 'seo_pages',
            'guard' => ['admin'],
            'key' => [
                'admin.seo.pages.index' => 'View SEO Pages',
                'admin.seo.pages.edit' => 'Edit SEO Page Meta',
                'admin.seo.pages.update' => 'Update SEO Page Meta',
            ],
        ]);

        $event->registerArray([
            'name' => 'SEO',
            'code' => 'seo_redirects',
            'guard' => ['admin'],
            'key' => [
                'admin.seo.redirects.index' => 'View SEO Redirects',
                'admin.seo.redirects.create' => 'Create SEO Redirect',
                'admin.seo.redirects.store' => 'Store SEO Redirect',
                'admin.seo.redirects.edit' => 'Edit SEO Redirect',
                'admin.seo.redirects.update' => 'Update SEO Redirect',
                'admin.seo.redirects.destroy' => 'Delete SEO Redirect',
                'admin.seo.redirects.import' => 'Import SEO Redirects',
            ],
        ]);

        $event->registerArray([
            'name' => 'SEO',
            'code' => 'seo_sitemap',
            'guard' => ['admin'],
            'key' => [
                'admin.seo.sitemap.index' => 'View Sitemap Settings',
                'admin.seo.sitemap.regenerate' => 'Regenerate Sitemap',
            ],
        ]);

        $event->registerArray([
            'name' => 'SEO',
            'code' => 'seo_robots',
            'guard' => ['admin'],
            'key' => [
                'admin.seo.robots.index' => 'View Robots.txt',
                'admin.seo.robots.update' => 'Update Robots.txt',
            ],
        ]);
    }
}
