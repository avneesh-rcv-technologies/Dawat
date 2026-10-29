<?php

namespace Modules\Admin\Providers;

use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Admin\Listeners\RegisterMenuItems;
use Modules\Admin\Listeners\RegisterAdminSettings;
use Modules\Admin\Listeners\RegisterAdminPermissions;
use Modules\Admin\Listeners\GlobalSearchEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Admin\Listeners\Settings;

class AdminEventServiceProvider extends ServiceProvider
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
        RegisterSettingsMenu::class => [
            Settings::class,
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