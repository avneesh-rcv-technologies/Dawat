<?php

namespace Modules\Acl\Providers;

use Modules\Acl\Listeners\Settings;
use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Acl\Listeners\RegisterAdminPermissions;
use Modules\Acl\Listeners\RegisterCustomerMenu;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class AclEventServiceProvider extends ServiceProvider
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
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
        ],
        RegisterMenu::class => [
            RegisterCustomerMenu::class,
        ],
        RegisterGlobalSearch::class => [
            // GlobalSearchEvent::class,
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