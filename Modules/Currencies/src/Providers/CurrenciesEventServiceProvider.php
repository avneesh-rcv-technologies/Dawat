<?php

namespace Modules\Currencies\Providers;

use Modules\Core\Events\RegisterMenu;
use Modules\Currencies\Listeners\Settings;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Currencies\Listeners\GlobalSearchEvent;
use Modules\Currencies\Listeners\RegisterAdminPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class CurrenciesEventServiceProvider extends ServiceProvider
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

        // RegisterMenu::class => [
        //     RegisterMenuItems::class,
        // ],
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
        ],

        RegisterGlobalSearch::class => [
            GlobalSearchEvent::class,
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