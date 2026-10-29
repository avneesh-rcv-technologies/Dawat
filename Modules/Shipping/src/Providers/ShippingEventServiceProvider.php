<?php

namespace Modules\Shipping\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Shipping\Models\Shipping;
use Modules\Shipping\Observers\ShippingObserver;

class ShippingEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Modules\Shipping\Events\ShippingStatusUpdated::class => [
            \Modules\Shipping\Listeners\LogShippingActivity::class,
            \Modules\Shipping\Listeners\UpdateShippingMetrics::class,
            \Modules\Shipping\Listeners\SyncWithExternalTracking::class,
        ],
        \Modules\Core\Events\RegisterSettingsMenu::class => [
            \Modules\Shipping\Listeners\RegisterShippingSettings::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
        
        // Register model observers
        // Shipping::observe(ShippingObserver::class); // Removed - not needed for food delivery
    }
} 