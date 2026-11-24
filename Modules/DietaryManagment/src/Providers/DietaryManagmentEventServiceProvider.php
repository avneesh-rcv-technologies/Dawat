<?php

namespace Modules\DietaryManagment\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\DietaryManagment\Listeners\RegisterMenuItems;
use Modules\DietaryManagment\Listeners\RegisterAdminPermissions;

class DietaryManagmentEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RegisterMenu::class => [RegisterMenuItems::class],
        RegisterPermissions::class => [RegisterAdminPermissions::class],
    ];
}
