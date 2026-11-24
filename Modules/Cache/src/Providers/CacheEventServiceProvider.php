<?php

namespace Modules\Cache\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Cache\Listeners\RegisterCacheMenu;
use Modules\Cache\Listeners\RegisterCachePermissions;
use Modules\Cache\Listeners\RegisterCacheSettings;
use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingsMenu;

class CacheEventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        RegisterMenu::class => [
            RegisterCacheMenu::class,
        ],
        RegisterSettingsMenu::class => [
            RegisterCacheSettings::class,
        ],
        RegisterPermissions::class => [
            RegisterCachePermissions::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
