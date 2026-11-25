<?php

namespace Modules\Rating\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    /**
     * Keep ratings in the admin workspace navigation only. Restaurant owners
     * access their ratings from the Settings hub instead.
     */
    public static function visibleInSidebar(): bool
    {
        $admin = auth('admin')->user();

        return !app(\Modules\Restaurant\Services\RestaurantOwnerRoleResolver::class)->isOwner($admin);
    }

    /**
     * Handle the event.
     *
     * @param  \Modules\Core\Events\RegisterMenu  $event
     * @return void
     */
    public function handle(RegisterMenu $event)
    {
        $event->addItem([
            'label' => 'Ratings-Reviews',
            'icon' => 'star',
            'permission' => 'ratings.workflow.manage',
            'visibility' => [self::class, 'visibleInSidebar'],
            'position' => 98,
            'route' => 'ratings.workflow.admin.index'
        ]);
    }
}
