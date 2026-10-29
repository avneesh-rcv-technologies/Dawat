<?php

namespace Modules\MobileConfiguration\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterMobileSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => "general",
            'title' => 'Mobile Configuration',
            'description' => 'Configure mobile app settings, including banners, features, walkthroughs, and more.',
            'route' => route('mobileconfiguration.cms.index'),
            'icon' => 'article',
            'guard' => 'admin',
            'permission' => 'mobileconfiguration.banners.index'
        ]);
    }
}
