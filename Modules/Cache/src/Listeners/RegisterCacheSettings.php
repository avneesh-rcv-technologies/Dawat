<?php

namespace Modules\Cache\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterCacheSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        // $event->register([
        //     'category_code' => 'access_control',
        //     'title' => 'Application Cache',
        //     'description' => 'Inspect Redis cache profiles, keys, sizes, and flush entries',
        //     'route' => route('cache.index'),
        //     'icon' => 'storage',
        //     'permission' => 'cache.index',
        //     'guard' => 'admin',
        // ]);
    }
}
