<?php

namespace Modules\Cache\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterCachePermissions
{
    public function handle(RegisterPermissions $event): void
    {
        $event->registerArray([
            'name' => 'Cache',
            'code' => 'application_cache',
            'guard' => ['admin'],
            'key' => [
                'cache.index' => 'View cache profiles',
                'cache.show' => 'View cache keys',
                'cache.flush' => 'Flush cache profile',
                'cache.flush-all' => 'Flush all clearable caches',
                'cache.bulk-flush' => 'Bulk flush cache profiles',
                'cache.forget' => 'Delete individual cache keys',
            ],
        ]);
    }
}
