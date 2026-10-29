<?php

namespace Modules\Acl\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Acl\Console\Commands\SetupAclCommand;
use Modules\Acl\Console\Commands\CreateUserCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    protected $commands = [
        SetupAclCommand::class,
        CreateUserCommand::class,
    ];

    public function register()
    {
        $this->commands($this->commands);
    }
}
