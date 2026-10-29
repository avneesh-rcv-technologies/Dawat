<?php

namespace Modules\Discounts\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Discounts\Listeners\GlobalSearchEvent;
use Modules\Core\Events\RegisterPermissions;
use Modules\Discounts\Listeners\RegisterAdminPermissions;

class DiscountsEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterGlobalSearch::class => [
            GlobalSearchEvent::class,
        ],
        \Modules\Core\Events\RegisterMenu::class => [
            \Modules\Discounts\Listeners\RegisterMenuItems::class,
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