<?php

namespace Modules\FoodManagements\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Core\Events\RegisterPermissions;
use Modules\FoodManagements\Listeners\GlobalSearchEvent;
use Modules\FoodManagements\Listeners\RegisterAdminPermissions;

class FoodManagementsEventServiceProvider extends ServiceProvider
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
            \Modules\FoodManagements\Listeners\RegisterMenuItems::class,
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