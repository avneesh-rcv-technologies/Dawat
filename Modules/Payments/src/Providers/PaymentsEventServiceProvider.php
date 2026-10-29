<?php

namespace Modules\Payments\Providers;

use Modules\Core\Events\RegisterPermissions;
use Modules\Core\Events\RegisterSettingsMenu;
use Modules\Payments\Listeners\RegisterAdminPermissions;
use Modules\Payments\Listeners\RegisterPaymentSettings;
use Modules\Payments\Observers\PaymentObserver;
use Modules\Payments\Models\Payments;
use Modules\Payments\Events\PaymentStatusUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class PaymentsEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
        ],
        RegisterSettingsMenu::class => [
            RegisterPaymentSettings::class,
            // add more listeners from other modules
        ],
        PaymentStatusUpdated::class => [
            [PaymentObserver::class, 'handlePaymentStatusUpdated'],
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Register model observers
        Payments::observe(PaymentObserver::class);
    }
}
