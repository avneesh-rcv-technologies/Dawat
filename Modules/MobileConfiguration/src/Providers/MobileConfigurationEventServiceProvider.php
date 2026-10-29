<?php

namespace Modules\MobileConfiguration\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\MobileConfiguration\Listeners\RegisterMenuItems;
use Modules\MobileConfiguration\Listeners\RegisterAdminPermissions;
use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\MobileConfiguration\Listeners\RegisterMobileSettings;
use Modules\Core\Events\RegisterSettingsMenu;

class MobileConfigurationEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // RegisterMenu::class => [
        //     RegisterMenuItems::class,
        // ],
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
        ],
        RegisterSettingsMenu::class => [
            RegisterMobileSettings::class,
            // add more listeners from other modules
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
