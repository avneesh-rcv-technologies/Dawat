<?php

namespace Modules\Filemanager\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterFilemanagerPermissions
{
    public function handle(RegisterPermissions $event): void
    {
        $event->registerArray([
            'name' => 'settings',
            'code' => 'filemanager',
            'guard' => ['admin', 'client', 'member'],
            'key' => [
                'filemanager.delete' => 'Delete Uploaded Files',
            ],
        ]);
    }
}
