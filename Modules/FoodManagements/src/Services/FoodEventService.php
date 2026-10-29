<?php

namespace Modules\FoodManagements\Services;

use Illuminate\Support\Str;
use Modules\FoodManagements\Models\FoodEvent;
use Modules\FoodManagements\Repositories\FoodEventRepository;

class FoodEventService
{
    protected FoodEventRepository $repository;

    public function __construct(FoodEventRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data)
    {
        // Generate unique slug
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        return $this->repository->create($data);
    }

    public function find(int $id)
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data)
    {
        // Generate unique slug if name is being updated
        if (isset($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function bulkDelete(array $ids)
    {
        return $this->repository->bulkDelete($ids);
    }

    public function bulkStatusUpdate(array $ids, bool $status): void
    {
        $this->repository->toggleStatus($ids, $status);
    }

    /**
     * Sync food items with the event
     */
    public function syncFoodItems(int $eventId, array $foodItemIds): void
    {
        $event = $this->find($eventId);
        $event->foodItems()->sync($foodItemIds);
    }

    /**
     * Sync food categories with the event
     */
    public function syncFoodCategories(int $eventId, array $categoryIds): void
    {
        $event = $this->find($eventId);
        $event->foodCategories()->sync($categoryIds);
    }

    /**
     * Get active events with details for API
     */
    public function getActiveEventsWithDetails()
    {
        return $this->repository->getActiveEventsWithDetails();
    }

    /**
     * Get event with all associated items for display
     */
    public function getEventWithAllItems(int $eventId)
    {
        return $this->repository->getEventWithAllItems($eventId);
    }

    /**
     * Get event data formatted for frontend display
     */
    public function getEventForDisplay(int $eventId)
    {
        $event = $this->find($eventId);
        
        return [
            'event' => $event->getFormattedData(),
            'food_items' => $event->foodItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'sale_price' => $item->sale_price,
                    'list_price' => $item->list_price,
                    'primary_image' => fn_get_image('food_image', $item->id)['url'] ?? '',
                    'categories' => $item->categories->pluck('name')->join(', '),
                ];
            }),
            'food_categories' => $event->foodCategories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'food_type' => $category->food_type,
                    'items_count' => $category->foodItems()->where('is_active', true)->count(),
                ];
            }),
        ];
    }

    /* ================= PRIVATE ================= */

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (
            FoodEvent::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}