<?php

namespace Modules\Cache\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterCacheMenu
{
    public function handle(RegisterMenu $event): void
    {
        // $event->addItem([
        //     'label' => 'Cache',
        //     'icon' => 'storage',
        //     'permission' => 'cache.index',
        //     'position' => 96,
        //     'route' => 'cache.index',
        //     'guard' => ['admin'],
        // ]);
    }
}
