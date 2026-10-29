<?php

namespace Modules\Notifications\Providers;

use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingTabs;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Notifications\Listeners\RegisterAdminPermissions;
use Modules\Notifications\Listeners\RegisterGeneralSettingTabs;
use Modules\Notifications\Listeners\RegisterNotificationSettings;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class NotificationsEventServiceProvider extends ServiceProvider
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