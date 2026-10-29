<?php

namespace Modules\Cms\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterCmsSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        $event->register([
            'category_code' => "general",
            'title' => 'CMS Settings',
            'description' => 'Manage CMS settings for the mobile app, including content, layout, and visibility settings.',
            'route' => route('cms.pages.index'),
            'icon' => 'article',
            'guard' => 'admin',
            'permission' => 'cms.pages.index'
        ]);
    }
}
