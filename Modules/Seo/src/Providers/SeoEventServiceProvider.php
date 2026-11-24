<?php

namespace Modules\Seo\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\Seo\Listeners\RegisterAdminPermissions;
use Modules\Seo\Listeners\RegisterMenuItems;

class SeoEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RegisterMenu::class => [
            RegisterMenuItems::class,
        ],
        RegisterPermissions::class => [
            RegisterAdminPermissions::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
