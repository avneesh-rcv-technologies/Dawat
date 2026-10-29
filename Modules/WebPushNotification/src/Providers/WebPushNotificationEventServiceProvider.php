<?php

namespace Modules\WebPushNotification\Providers;

use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\WebPushNotification\Listeners\RegisterWebPushListener;
use Modules\WebPushNotification\Listeners\RegisterAdminPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class WebPushNotificationEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterSettingsMenu::class => [
            RegisterWebPushListener::class,
            // add more listeners from other modules
        ],

        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
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