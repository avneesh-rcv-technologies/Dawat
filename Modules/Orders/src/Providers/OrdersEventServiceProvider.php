<?php

namespace Modules\Orders\Providers;

use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Orders\Listeners\GlobalSearchEvent;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;
use Modules\Orders\Observers\OrderObserver;
use Modules\Core\Events\RegisterPermissions;
use Modules\Orders\Observers\TransactionObserver;
use Modules\Orders\Listeners\RegisterOrderPermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class OrdersEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
       RegisterPermissions::class => [
            RegisterOrderPermissions::class,
        ],
        \Modules\Core\Events\RegisterMenu::class => [
            \Modules\Orders\Listeners\RegisterMenuItems::class,
        ],
        \Modules\Core\Events\RegisterSettingsMenu::class => [
            \Modules\Orders\Listeners\RegisterOrderStatusSettings::class,
        ],
        RegisterGlobalSearch::class => [
          GlobalSearchEvent::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Register model observers
        Transaction::observe(TransactionObserver::class);
        Order::observe(OrderObserver::class);
    }
}
