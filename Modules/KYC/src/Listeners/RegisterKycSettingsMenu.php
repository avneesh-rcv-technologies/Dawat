<?php

namespace Modules\KYC\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterKycSettingsMenu
{
    /**
     * Handle the event.
     *
     * @param RegisterSettingsMenu $event
     * @return void
     */
    public function handle(RegisterSettingsMenu $event)
    {
        // Add KYC menu item to Settings
        $event->register([
            'title' => 'KYC',
            'description' => 'Manage KYC types, fields, and submissions',
            'icon' => 'verified_user',
            'route' => '/admin/kyc/types',
            'category_code' => 'general',
            'permission' => 'admin.dashboard' // Using a generic admin permission to allow all admins to access
        ]);
    }
}
