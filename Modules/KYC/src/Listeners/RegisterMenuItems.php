<?php

namespace Modules\KYC\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    public function handle(RegisterMenu $event): void
    {
        // $children = [
        //     [
        //         'label' => 'KYC Submissions',
        //         'route' => 'admin.kyc.submissions.index',
        //         'permission' => 'admin.kyc.submissions.index',
        //         'position' => 1,
        //     ],
        //     [
        //         'label' => 'KYC Requests',
        //         'route' => 'admin.kyc.requests.index',
        //         'permission' => 'admin.kyc.requests.index',
        //         'position' => 2,
        //     ],
        // ];

        $event->addItem([
            'label' => 'KYC',
            'icon' => 'verified_user',
            'guard' => 'admin',
            'permission' => '',
            'position' => 45,
            'route' => 'admin.kyc.submissions.index',
        ]);
    }
}
