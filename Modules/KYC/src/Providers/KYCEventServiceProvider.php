<?php

namespace Modules\KYC\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Core\Events\RegisterMenu;
use Modules\KYC\Listeners\GlobalSearchEvent;
use Modules\KYC\Listeners\RegisterMenuItems;
use Modules\KYC\Listeners\RegisterAdminPermissions;

class KYCEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Modules\Core\Events\RegisterSettingsMenu::class => [
            \Modules\KYC\Listeners\RegisterKycSettingsMenu::class,
        ],
        RegisterMenu::class => [
            RegisterMenuItems::class,
        ],
        RegisterGlobalSearch::class => [
            GlobalSearchEvent::class,
        ],
        \Modules\Core\Events\RegisterPermissions::class => [
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