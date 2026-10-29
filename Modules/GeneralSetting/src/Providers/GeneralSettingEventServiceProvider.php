<?php

namespace Modules\GeneralSetting\Providers;

use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\GeneralSetting\Listeners\Settings;
use Modules\GeneralSetting\Listeners\RegisterMenuItems;
use Modules\GeneralSetting\Listeners\RegisterAdminPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class GeneralSettingEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterSettingsMenu::class => [
            Settings::class,
        ],

        RegisterMenu::class => [
            RegisterMenuItems::class,
        ],
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
        ],

        // RegisterGlobalSearch::class => [
        //     GlobalSearchEvent::class,
        // ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
} 