<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FeaturedItem;

class FeaturedItemRepository
{
    public function getAll()
    {
        return FeaturedItem::latest();
    }

    public function findById(int $id): FeaturedItem
    {
        return FeaturedItem::findOrFail($id);
    }

    public function create(array $data): FeaturedItem
    {
        return FeaturedItem::create($data);
    }

    public function update(int $id, array $data): FeaturedItem
    {
        $featuredItem = FeaturedItem::findOrFail($id);
        $featuredItem->update($data);

        return $featuredItem->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) FeaturedItem::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return FeaturedItem::whereIn('id', $ids)->delete();
    }

    public function toggleStatus(array $ids, bool $status): int
    {
        return FeaturedItem::whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    /**
     * Get active featured items for display
     */
    public function getActiveFeaturedItems()
    {
        return FeaturedItem::active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($featuredItem) {
                return $this->formatFeaturedItemForApi($featuredItem);
            });
    }

    /**
     * Get featured food items only
     */
    public function getFeaturedFoodItems()
    {
        return FeaturedItem::active()
            ->whereJsonContains('data->featured_type', 'food_item')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($featuredItem) {
                return $this->formatFeaturedItemForApi($featuredItem);
            });
    }

    /**
     * Get featured categories only
     */
    public function getFeaturedCategories()
    {
        return FeaturedItem::active()
            ->whereJsonContains('data->featured_type', 'category')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($featuredItem) {
                return $this->formatFeaturedItemForApi($featuredItem);
            });
    }

    /**
     * Format featured item for API response
     */
    private function formatFeaturedItemForApi($featuredItem)
    {
        $data = [
            'id' => $featuredItem->id,
            'title' => $featuredItem->title,
            'description' => $featuredItem->description,
            'type' => $featuredItem->getType(),
            'status' => $featuredItem->status,
            'created_at' => $featuredItem->created_at,
            'updated_at' => $featuredItem->updated_at,
            'food_items' => [],
            'food_categories' => [],
        ];

        // Add food items if any
        $foodItems = $featuredItem->food_items;
        if ($foodItems->count() > 0) {
            $data['food_items'] = $foodItems->map(function ($item) {
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
                ];
            })->toArray();
        }

        // Add food categories if any
        $foodCategories = $featuredItem->food_categories;
        if ($foodCategories->count() > 0) {
            $data['food_categories'] = $foodCategories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'food_type' => $category->food_type,
                    'image_url' => $category->image_url,
                    'items_count' => $category->foodItems()->where('is_active', true)->count(),
                ];
            })->toArray();
        }

        return $data;
    }
}