<?php

namespace Modules\Subscriptions\Providers;

use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Subscriptions\Listeners\RegisterSubscriptionMenu;
use Modules\Subscriptions\Listeners\RegisterSubscriptionPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class SubscriptionsEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterPermissions::class => [
            RegisterSubscriptionPermissions::class,
        ],
        RegisterMenu::class => [
            RegisterSubscriptionMenu::class,
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