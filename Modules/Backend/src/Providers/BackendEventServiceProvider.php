<?php

namespace Modules\Backend\Providers;

use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Backend\Listeners\GlobalSearchEvent;
use Modules\Backend\Listeners\RegisterMenuItems;
use Modules\Backend\Listeners\RegisterAdminPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class BackendEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterMenu::class => [
            RegisterMenuItems::class,
        ],
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
