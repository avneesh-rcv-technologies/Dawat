<?php

namespace Modules\EmailNotification\Providers;

use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\EmailNotification\Listeners\RegisterAdminPermissions;
use Modules\EmailNotification\Listeners\RegisterNotificationSettings;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EmailNotificationEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterSettingsMenu::class => [
            RegisterNotificationSettings::class,
            // add more listeners from other modules
        ],

        // RegisterMenu::class => [
        //     RegisterMenuItems::class,
        // ],
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
} 