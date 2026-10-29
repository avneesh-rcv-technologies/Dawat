<?php

namespace Modules\Restaurant\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class RestaurantEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Modules\Core\Events\RegisterMenu::class => [
            \Modules\Restaurant\Listeners\RegisterMenuItems::class,
        ],
        \Modules\Core\Events\RegisterPermissions::class => [
            \Modules\Restaurant\Listeners\RegisterAdminPermissions::class,
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