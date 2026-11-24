<?php

namespace Modules\Website\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterContactQueryPermissions
{
    public function handle(RegisterPermissions $event): void
    {
        $event->registerArray([
            'name' => 'Website',
            'code' => 'contact_queries',
            'guard' => ['admin'],
            'key' => [
                'website.contact-queries.index' => 'View Contact Queries',
                'website.contact-queries.show' => 'View Contact Query Details',
                'website.contact-queries.destroy' => 'Delete Contact Queries',
                'website.contact-queries.bulk-delete' => 'Bulk Delete Contact Queries',
            ],
        ]);
    }
}
