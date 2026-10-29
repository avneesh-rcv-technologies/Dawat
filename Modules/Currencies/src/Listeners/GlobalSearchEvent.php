<?php

namespace Modules\Currencies\Listeners;

use Modules\Currencies\Models\Currency;
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

        // Only super admin should see currencies in global search
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin) {
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();
            
            // If restaurant admin, don't register this preference
            if ($restaurant) {
                return;
            }
        }

        $event->addSearchPreference('currencies', 'Currencies');
        
        if (strlen($query) < 2) {
            return;
        }

        // If module filter is set and isn't 'themes' or 'all', skip
        if ($moduleFilter !== 'all' && !in_array($moduleFilter, ['currencies'])) {
            return;
        }

        // Search stores by name or slug
        $stores = Currency::where('code', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($stores as $s) {
            $event->addResult([
                'id' => $s->id,
                'title' => $s->name,
                'url' => '/settings/currencies?filters[code]=' . $s->code,
                'type' => 'currency',
                'module' => 'currencies',
                'module_name' => 'Currencies',
            ]);
        }
    }
}
