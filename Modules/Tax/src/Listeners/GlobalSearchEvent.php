<?php

namespace Modules\Tax\Listeners;


use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Tax\Models\TaxCategory;

class GlobalSearchEvent
{
    /**
     * Handle the event.
     */
    public function handle(RegisterGlobalSearch $event): void
    {
        $query = $event->query;
        $moduleFilter = $event->module;

        // Only super admin should see tax in global search
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin) {
            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();
            
            // If restaurant admin, don't register this preference
            if ($restaurant) {
                return;
            }
        }

        $event->addSearchPreference('tax_categories', 'Tax');
        
         if (strlen($query) < 2) {
            return;
        }

        /**
         * -----------------------------
         * PARTS SEARCH
         * -----------------------------
         */
        if ($moduleFilter === 'all' || $moduleFilter === 'tax_categories') {

            $parts = TaxCategory::where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($parts as $p) {
                $event->addResult([
                    'id' => $p->id,
                    'title' => $p->name,
                    'url' => '/admin/tax-category?filters[name]=' . $p->name,
                    'type' => 'tax_category',
                    'module' => 'Tax',
                    'module_name' => 'Tax',
                ]);
            }
        }

    
}
}
