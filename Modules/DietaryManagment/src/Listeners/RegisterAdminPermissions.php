<?php

namespace Modules\DietaryManagment\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event): void
    {
        $event->registerArray([
            'name' => 'Dietary Management',
            'code' => 'dietary_labels',
            'guard' => ['admin'],
            'key' => [
                'admin.dietarymanagment.certifications.index' => 'View Certifications',
                'admin.dietarymanagment.certifications.show' => 'View Certificate Documents',
                'restaurant.onboarding.certificates.document' => 'Preview / Download Certificate Documents',
                'admin.dietarymanagment.certifications.review' => 'Review Certificates',
                'admin.dietarymanagment.labels.index' => 'View Dietary Labels',
                'admin.dietarymanagment.labels.create' => 'Create Dietary Label',
                'admin.dietarymanagment.labels.store' => 'Store Dietary Label',
                'admin.dietarymanagment.labels.edit' => 'Edit Dietary Label',
                'admin.dietarymanagment.labels.update' => 'Update Dietary Label',
                'admin.dietarymanagment.labels.destroy' => 'Delete Dietary Label',
            ],
        ]);
    }
}
