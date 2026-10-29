<?php

namespace Modules\FoodManagements\Listeners;

use Modules\Core\Events\RegisterGlobalSearch;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Restaurant\Models\Restaurant;

// class GlobalSearchEvent
// {
//     /**
//      * Handle the event.
//      */
//     public function handle(RegisterGlobalSearch $event): void
//     {
//         $query = $event->query;
//         $moduleFilter = $event->module;

//         $event->addSearchPreference('food_categories', 'Food Categories');
//         $event->addSearchPreference('food_items', 'Food Items');
        
//         if (strlen($query) < 2) {
//             return;
//         }

//         /**
//          * -----------------------------
//          * FOOD CATEGORIES SEARCH
//          * -----------------------------
//          */
//         if ($moduleFilter === 'all' || $moduleFilter === 'food_categories') {

//             $categories = FoodCategory::where('name', 'LIKE', "%{$query}%")
//                 ->orWhere('description', 'LIKE', "%{$query}%")
//                 ->limit(5)
//                 ->get();

//             foreach ($categories as $category) {
//                 $event->addResult([
//                     'id' => $category->id,
//                     'title' => $category->name,
//                     'url' => '/foodmanagements/category?filters[name]=' . urlencode($category->name),
//                     'type' => 'food_category',
//                     'module' => 'FoodManagements',
//                     'module_name' => 'Food Managements',
//                 ]);
//             }
//         }

//         /**
//          * -----------------------------
//          * FOOD ITEMS SEARCH
//          * -----------------------------
//          */
//         if ($moduleFilter === 'all' || $moduleFilter === 'food_items') {

//             $foodItems = FoodItem::where('name', 'LIKE', "%{$query}%")
//                 ->orWhere('description', 'LIKE', "%{$query}%")
//                 ->orWhere('long_description', 'LIKE', "%{$query}%")
//                 ->limit(5)
//                 ->get();

//             foreach ($foodItems as $item) {
//                 $event->addResult([
//                     'id' => $item->id,
//                     'title' => $item->name,
//                     'url' => '/foodmanagements/food-items?filters[name]=' . urlencode($item->name),
//                     'type' => 'food_item',
//                     'module' => 'FoodManagements',
//                     'module_name' => 'Food Managements',
//                 ]);
//             }
//         }
//     }
// }


class GlobalSearchEvent
{
    /**
     * Handle the event.
     */
    public function handle(RegisterGlobalSearch $event): void
    {
        $searchText = $event->query;
        $moduleFilter = $event->module;

        $currentAdmin = auth('admin')->user();
        $restaurant = null;

        if ($currentAdmin) {
            $restaurant = Restaurant::where('admin_id', $currentAdmin->id)->first();
        }

        // Only register preferences if user has access
        // Restaurant admin: always show these options
        // Super admin: always show these options
        $event->addSearchPreference('food_categories', 'Food Categories');
        $event->addSearchPreference('food_items', 'Food Items');

        if (strlen($searchText) < 2) {
            return;
        }

        /**
         * -----------------------------
         * FOOD CATEGORIES SEARCH
         * -----------------------------
         */
        if ($moduleFilter === 'all' || $moduleFilter === 'food_categories') {

            $categoriesQuery = FoodCategory::query();

            // Restaurant admin ke liye sirf uski categories
            if ($restaurant) {
                $categoriesQuery->where('restaurant_id', $restaurant->id);
            }

            $categories = $categoriesQuery
                ->where(function ($q) use ($searchText) {
                    $q->where('name', 'LIKE', "%{$searchText}%")
                      ->orWhere('description', 'LIKE', "%{$searchText}%");
                })
                ->limit(5)
                ->get();

            foreach ($categories as $category) {
                $event->addResult([
                    'id' => $category->id,
                    'title' => $category->name,
                    'url' => '/foodmanagements/category?filters[category_name]=' . urlencode($category->name),
                    'type' => 'food_category',
                    'module' => 'FoodManagements',
                    'module_name' => 'Food Managements',
                ]);
            }
        }

        /**
         * -----------------------------
         * FOOD ITEMS SEARCH
         * -----------------------------
         */
        if ($moduleFilter === 'all' || $moduleFilter === 'food_items') {

            $foodItemsQuery = FoodItem::query();

            // Restaurant admin ke liye sirf uske food items
            if ($restaurant) {
                $foodItemsQuery->whereHas('categories', function ($q) use ($restaurant) {
                    $q->where('restaurant_id', $restaurant->id);
                });
            }

            $foodItems = $foodItemsQuery
                ->where(function ($q) use ($searchText) {
                    $q->where('name', 'LIKE', "%{$searchText}%")
                      ->orWhere('description', 'LIKE', "%{$searchText}%")
                      ->orWhere('long_description', 'LIKE', "%{$searchText}%");
                })
                ->limit(5)
                ->get();

            foreach ($foodItems as $item) {
                $event->addResult([
                    'id' => $item->id,
                    'title' => $item->name,
                    'url' => '/foodmanagements/food-items?filters[name]=' . urlencode($item->name),
                    'type' => 'food_item',
                    'module' => 'FoodManagements',
                    'module_name' => 'Food Managements',
                ]);
            }
        }
    }
}