<?php

namespace Modules\Countries\Listeners;

use Modules\Countries\Models\Country;
use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Countries\Models\CountryState;

class GlobalSearchEvent
{
    /**
     * Handle the event.
     */
    public function handle(RegisterGlobalSearch $event): void
    {
        $query = $event->query;
        $moduleFilter = $event->module;

        // Only super admin should see countries in global search
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin) {
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();
            
            // If restaurant admin, don't register this preference
            if ($restaurant) {
                return;
            }
        }

        $event->addSearchPreference('countries', 'Countries');
        $event->addSearchPreference('states', 'States');
        
        if (strlen($query) < 2) {
            return;
        }

        // If module filter is set and isn't 'themes' or 'all', skip
        if ($moduleFilter !== 'all' && !in_array($moduleFilter, ['countries', 'states'])) {
            return;
        }

        // Search stores by name or slug
        $stores = Country::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($stores as $s) {
            $event->addResult([
                'id' => $s->id,
                'title' => $s->name,
                'url' => '/settings/countries?filters[name]=' . urlencode($s->name),
                'type' => 'country',
                'module' => 'countries',
                'module_name' => 'Countries',
            ]);
        }

        // states
        $stores = CountryState::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($stores as $s) {
            $event->addResult([
                'id' => $s->id,
                'title' => $s->name,
                'url' => '/settings/states?filters[name]=' . $s->name,
                'type' => 'state',
                'module' => 'countries',
                'module_name' => 'Countries',
            ]);
        }
    }
}
