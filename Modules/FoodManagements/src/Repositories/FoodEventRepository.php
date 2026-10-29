<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FoodEvent;

class FoodEventRepository
{
    public function getAll()
    {
        return FoodEvent::latest();
    }

    public function findById(int $id): FoodEvent
    {
        return FoodEvent::with([
            'foodItems.categories',
            'foodItems.tags',
            'foodCategories.parent',
            'foodCategories.children'
        ])->findOrFail($id);
    }

    public function create(array $data): FoodEvent
    {
        return FoodEvent::create($data);
    }

    public function update(int $id, array $data): FoodEvent
    {
        $event = FoodEvent::findOrFail($id);
        $event->update($data);

        return $event->load([
            'foodItems.categories',
            'foodItems.tags',
            'foodCategories.parent',
            'foodCategories.children'
        ]);
    }

    public function delete(int $id): bool
    {
        return (bool) FoodEvent::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return FoodEvent::whereIn('id', $ids)->delete();
    }

    public function toggleStatus(array $ids, bool $status): int
    {
        return FoodEvent::whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    /**
     * Get active events with their associated items and categories
     */
    public function getActiveEventsWithDetails()
    {
        return FoodEvent::active()
            ->with([
                'foodItems' => function ($query) {
                    $query->where('is_active', true)
                        ->where('is_available', true)
                        ->with(['categories', 'tags']);
                },
                'foodCategories' => function ($query) {
                    $query->where('status', true)
                        ->with(['parent', 'children']);
                }
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($event) {
                return $this->formatEventForApi($event);
            });
    }

    /**
     * Get event with all associated food items (direct + from categories)
     */
    public function getEventWithAllItems(int $eventId)
    {
        $event = $this->findById($eventId);
        
        if (!$event->isActive()) {
            return null;
        }

        $allItems = $event->getAllFoodItems();

        return [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'status' => $event->status,
            ],
            'food_items' => $allItems->map(function ($item) {
                return $this->formatFoodItemForApi($item);
            }),
            'food_categories' => $event->foodCategories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'food_type' => $category->food_type,
                    'image_url' => fn_get_image('category_image', $category->id)['url'] ?? '',
                ];
            }),
            'meta' => [
                'total_items' => $allItems->count(),
                'direct_items' => $event->foodItems()->count(),
                'category_items' => $allItems->count() - $event->foodItems()->count(),
                'categories_count' => $event->foodCategories()->count(),
            ]
        ];
    }

    /**
     * Format event for API response
     */
    private function formatEventForApi($event)
    {

        return [
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'description' => $event->description,
            'status' => $event->status,
            'created_at' => $event->created_at,
            'updated_at' => $event->updated_at,
            'items' => $event->foodItems->map(function ($item) {
                return $this->formatFoodItemForApi($item);
            }),
            'categories' => $event->foodCategories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'type' => $category->food_type,
                    'image_url' => fn_get_image('category_image', $category->id)['url'] ?? '',
                ];
            }),
            'stats' => [
                'direct_items_count' => $event->foodItems->count(),
                'categories_count' => $event->foodCategories->count(),
                'total_items_count' => $event->getAllFoodItems()->count(),
            ]
        ];
    }

    /**
     * Format food item for API response
     */
    private function formatFoodItemForApi($item)
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'sale_price' => $item->sale_price,
            'list_price' => $item->list_price,
            'tax' => $item->tax,
            'in_stock' => $item->in_stock,
            'is_available' => $item->is_available,
            'primary_image' => fn_get_image('food_image', $item->id)['url'] ?? '',
            'categories' => $item->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ];
            }),
            'tags' => $item->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ];
            }),
        ];
    }
}