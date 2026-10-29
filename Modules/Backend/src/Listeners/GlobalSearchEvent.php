<?php

namespace Modules\Backend\Listeners;

use Modules\Core\Events\RegisterGlobalSearch;

class GlobalSearchEvent
{
    /**
     * Handle the event.
     */
    public function handle(RegisterGlobalSearch $event): void
    {
        $query = $event->query;
        $moduleFilter = $event->module;

        // Only super admin should see roles in global search
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin) {
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();
            
            // If restaurant admin, don't register this preference
            if ($restaurant) {
                return;
            }
        }
        
        $event->addSearchPreference('roles', 'Roles');
    }
}
