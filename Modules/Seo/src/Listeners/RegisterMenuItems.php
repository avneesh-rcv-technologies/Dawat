<?php

namespace Modules\Seo\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    public function handle(RegisterMenu $event): void
    {
        $event->addItem([
            'label' => 'SEO',
            'icon' => 'web',
            'permission' => '',
            'position' => 93,
            'route' => '',
            'children' => [
                [
                    'label' => 'Analytics',
                    'permission' => 'admin.seo.analytics.index',
                    'route' => 'admin.seo.analytics.index',
                ],
                [
                    'label' => 'Configuration',
                    'permission' => 'admin.seo.settings.index',
                    'route' => 'admin.seo.settings.index',
                ],
                [
                    'label' => 'Pages',
                    'permission' => 'admin.seo.pages.index',
                    'route' => 'admin.seo.pages.index',
                ],
                [
                    'label' => 'Redirects',
                    'permission' => 'admin.seo.redirects.index',
                    'route' => 'admin.seo.redirects.index',
                ],
                [
                    'label' => 'Sitemap',
                    'permission' => 'admin.seo.sitemap.index',
                    'route' => 'admin.seo.sitemap.index',
                ],
                [
                    'label' => 'Robots.txt',
                    'permission' => 'admin.seo.robots.index',
                    'route' => 'admin.seo.robots.index',
                ],
            ],
        ]);
    }
}
