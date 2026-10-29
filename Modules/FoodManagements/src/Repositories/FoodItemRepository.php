<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FoodItem;

class FoodItemRepository
{
    public function getAll()
    {
        return FoodItem::latest();
    }

    public function findById(int $id): FoodItem
    {
        return FoodItem::with([
            'categories.parent', // 👈 IMPORTANT
            'tags',
            'optionGroups.options',
        ])->findOrFail($id);
    }

    public function create(array $data): FoodItem
    {
        return FoodItem::create($data);
    }

    public function update(int $id, array $data): FoodItem
    {
        $item = FoodItem::findOrFail($id);
        $item->update($data);

        return $item->load([
            'categories.parent',
            'tags',
            'optionGroups.options',
        ]);
    }

    public function delete(int $id): bool
    {
        return FoodItem::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return FoodItem::whereIn('id', $ids)->delete();
    }

    public function toggleStatus(array $ids, bool $status): int
    {
        return FoodItem::whereIn('id', $ids)
            ->update(['is_active' => $status]);
    }

     /**
     * Get food items by category with filters and pagination
     */
    public function getFoodItemsByCategory(int $categoryId, array $filters = [], int $perPage = 20, int $page = 1)
    {
        // Collect the category + all its children IDs
        $childIds = \Modules\FoodManagements\Models\FoodCategory::where('parent_id', $categoryId)
            ->pluck('id')
            ->toArray();
        $allCategoryIds = array_merge([$categoryId], $childIds);

        $query = FoodItem::whereHas('categories', function ($q) use ($allCategoryIds) {
            $q->whereIn('food_categories.id', $allCategoryIds);
        })
        ->where('is_active', true)
        ->where('is_available', true)
        ->where('in_stock', true)
        ->with(['tags', 'categories']);

        // Filter by restaurant via category
        if (!empty($filters['restaurant_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('food_categories.restaurant_id', $filters['restaurant_id']);
            });
        }

        // Filter by food type
        if (!empty($filters['food_type']) && $filters['food_type'] !== 'all') {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('food_type', $filters['food_type'])
                  ->orWhere('food_type', 'all');
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('food_tags.id', $filters['tags']);
            });
        }

        // Filter by feature variant (e.g. size=Large, spice=Medium)
        if (!empty($filters['variant_id'])) {
            $query->whereHas('featureValues', function ($q) use ($filters) {
                $q->where('variant_id', $filters['variant_id']);
            });
        }

        $paginator = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(function ($item) {
                return $this->formatFoodItem($item);
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'next_page' => $paginator->nextPageUrl(),
                'prev_page' => $paginator->previousPageUrl(),
            ]
        ];
    }

    /**
     * Get food items by tag with filters and pagination
     */
    public function getFoodItemsByTag(int $tagId, array $filters = [], int $perPage = 20, int $page = 1)
    {
        $query = FoodItem::whereHas('tags', function ($q) use ($tagId) {
            $q->where('food_tags.id', $tagId);
        })
        ->where('is_active', true)
        ->where('is_available', true)
        ->where('in_stock', true)
        ->with(['tags', 'categories']);

        // Apply filters
        if (!empty($filters['food_type']) && $filters['food_type'] !== 'all') {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('food_type', $filters['food_type'])
                  ->orWhere('food_type', 'all');
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        $paginator = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(function ($item) {
                return $this->formatFoodItem($item);
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'next_page' => $paginator->nextPageUrl(),
                'prev_page' => $paginator->previousPageUrl(),
            ]
        ];
    }

    /**
     * Format food item for API response
     */
    private function formatFoodItem($item)
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'sale_price' => $item->sale_price,
            'list_price' => $item->list_price,
            'in_stock' => $item->in_stock,
            'quantity_type' => $item->quantity_type,
            'quantity' => $item->quantity,
            'quantity_label' => $item->isGroupingQuantityType() ? 'Minimum Guests' : 'Stock Quantity',
            'formatted_quantity' => $item->quantity ? 
                ($item->isGroupingQuantityType() ? $item->quantity . ' guests minimum' : $item->quantity . ' in stock') : 
                'N/A',
            'is_available' => $item->is_available,
            'primary_image' => fn_get_image('food_image', $item->id)['url'] ?? '',
            'tags' => $item->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ];
            }),
            'categories' => $item->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ];
            }),
        ];
    }
}
