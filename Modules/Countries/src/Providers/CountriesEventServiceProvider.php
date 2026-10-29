<?php

namespace Modules\Countries\Providers;

use Modules\Countries\Listeners\Settings;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Countries\Listeners\GlobalSearchEvent;
use Modules\Countries\Listeners\RegisterAdminPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class CountriesEventServiceProvider extends ServiceProvider
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