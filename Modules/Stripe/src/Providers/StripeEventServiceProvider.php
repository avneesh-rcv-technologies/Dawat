<?php

namespace Modules\Stripe\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class StripeEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // 'SomeEvent' => [
        //     'SomeListener',
        // ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
} 