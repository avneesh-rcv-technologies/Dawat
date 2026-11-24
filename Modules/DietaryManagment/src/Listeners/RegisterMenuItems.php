<?php

namespace Modules\DietaryManagment\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    public function handle(RegisterMenu $event): void
    {
        $event->addItem([
            'label' => 'Dietary Manage',
            'icon' => 'eco',
            'guard' => 'admin',
            'permission' => [
                'admin.dietarymanagment.labels.index',
                'admin.dietarymanagment.certifications.index',
            ],
            'position' => 79,
            'children' => [
                [
                    'label' => 'Labels',
                    'icon' => 'label',
                    'route' => 'admin.dietarymanagment.labels.index',
                    'permission' => 'admin.dietarymanagment.labels.index',
                ],
                [
                    'label' => 'Certifications',
                    'icon' => 'verified',
                    'route' => 'admin.dietarymanagment.certifications.index',
                    'permission' => 'admin.dietarymanagment.certifications.index',
                ],
            ],
        ]);
    }
}
