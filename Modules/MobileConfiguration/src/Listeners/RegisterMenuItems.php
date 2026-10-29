<?php

namespace Modules\MobileConfiguration\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    public function handle(RegisterMenu $event): void
    {
        $event->addItem([
            'label' => 'Mobile Configuration',
            'icon' => 'smartphone',
            'guard' => 'admin',
            'permission' => 'mobileconfiguration.banners.index',
            'position' => 40,
            'children' => [
                // [
                //     'label' => 'Banners',
                //     'route' => 'mobileconfiguration.banners.index',
                //     'icon' => 'image',
                //     'position' => 1,
                //     'guard' => 'admin',
                //     'permission' => 'mobileconfiguration.banners.index',
                // ],
                // [
                //     'label' => 'Features',
                //     'route' => 'mobileconfiguration.features.index',
                //     'icon' => 'featured_play_list',
                //     'position' => 2,
                //     'guard' => 'admin',
                //     'permission' => 'mobileconfiguration.features.index',
                // ],
                // [
                //     'label' => 'Walkthroughs',
                //     'route' => 'mobileconfiguration.walkthroughs.index',
                //     'icon' => 'tour',
                //     'position' => 3,
                //     'guard' => 'admin',
                //     'permission' => 'mobileconfiguration.walkthroughs.index',
                // ],
                // [
                //     'label' => 'CMS Pages',
                //     'route' => 'mobileconfiguration.cms.index',
                //     'icon' => 'article',
                //     'position' => 4,
                //     'guard' => 'admin',
                //     'permission' => 'mobileconfiguration.cms.index',
                // ],
                // [
                //     'label' => 'Homepage Layouts',
                //     'route' => 'mobileconfiguration.homepage-layouts.index',
                //     'icon' => 'dashboard_customize',
                //     'position' => 5,
                //     'guard' => 'admin',
                //     'permission' => 'mobileconfiguration.homepage-layouts.index',
                // ],
            ]
        ]);
    }
}
