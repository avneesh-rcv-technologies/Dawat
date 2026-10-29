<?php

namespace Modules\FoodManagements\Repositories;

use Modules\FoodManagements\Models\FoodCategory;

class FoodCategoryRepository
{
    public function query()
    {
        return FoodCategory::query();
    }

    public function findById(int $id): FoodCategory
    {
        return FoodCategory::findOrFail($id);
    }

    public function create(array $data): FoodCategory
    {
        return FoodCategory::create($data);
    }

    public function update(int $id, array $data): FoodCategory
    {
        $category = $this->findById($id);
        $category->update($data);

        return $category;
    }

    public function delete(int $id): bool
    {
        return (bool) FoodCategory::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return FoodCategory::whereIn('id', $ids)->delete();
    }

    public function bulkStatusUpdate(array $ids, bool $status): void
    {
        FoodCategory::whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    public function getRootActive(?int $restaurantId = null)
    {
        $query = FoodCategory::root()->active();

        if ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        }

        return $query->get();
    }

    /**
     * Get active root categories with their active subcategories
     */
    public function getActiveCategoriesWithSubcategories(?int $restaurantId = null)
    {

        $query = FoodCategory::root()->active();

        if ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        }
        return $query
            ->with(['children' => function ($q) use ($restaurantId) {
                $q->active()->orderBy('name');
                if ($restaurantId) {
                    $q->where('restaurant_id', $restaurantId);
                }
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => fn_get_image('category_image', $category->id)['url'] ?? '',
                    'type' => $category->food_type,
                    'subcategories' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'description' => $child->description,
                            'image_url' => fn_get_image('category_image', $child->id)['url'] ?? '',
                            'type' => $child->food_type,
                        ];
                    })
                ];
            });
    }

    /**
     * Get complete menu structure for frontend
     */
    public function getCompleteMenu(?string $foodType = null, bool $includeItems = false)
    {
        $query = FoodCategory::root()->active();

        if ($foodType && $foodType !== 'all') {
            $query->where(function ($q) use ($foodType) {
                $q->where('food_type', $foodType)
                    ->orWhere('food_type', 'all');
            });
        }

        $categories = $query->with([
            'children' => function ($query) use ($foodType) {
                $query->active();
                if ($foodType && $foodType !== 'all') {
                    $query->where(function ($q) use ($foodType) {
                        $q->where('food_type', $foodType)
                            ->orWhere('food_type', 'all');
                    });
                }
            }
        ])->orderBy('name')->get();

        if ($includeItems) {
            $categories->load([
                'foodItems' => function ($query) use ($foodType) {
                    $query->where('is_active', true)
                        ->where('is_available', true)
                        ->where('in_stock', true);
                    if ($foodType && $foodType !== 'all') {
                        // Filter items based on category food_type compatibility
                    }
                },
                'children.foodItems' => function ($query) use ($foodType) {
                    $query->where('is_active', true)
                        ->where('is_available', true)
                        ->where('in_stock', true);
                }
            ]);
        }

        return $categories->map(function ($category) use ($includeItems) {
            $data = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image_url' => fn_get_image('category_image', $category->id)['url'] ?? '',
                'type' => $category->food_type,
                'subcategories' => $category->children->map(function ($child) use ($includeItems) {
                    $childData = [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'description' => $child->description,
                        'image_url' => fn_get_image('category_image', $child->id)['url'] ?? '',
                        'type' => $child->food_type,
                    ];

                    if ($includeItems) {
                        $childData['items'] = $child->foodItems->map(function ($item) {
                            return $this->formatFoodItemForMenu($item);
                        });
                    }

                    return $childData;
                })
            ];

            if ($includeItems) {
                $data['items'] = $category->foodItems->map(function ($item) {
                    return $this->formatFoodItemForMenu($item);
                });
            }

            return $data;
        });
    }

    /**
     * Format food item for menu display
     */
    private function formatFoodItemForMenu($item)
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'sale_price' => $item->sale_price,
            'list_price' => $item->list_price,
            'primary_image' => fn_get_image('food_image', $item->id)['url'] ?? '',
            'tags' => $item->tags->pluck('name'),
            'in_stock' => $item->in_stock,
            'is_available' => $item->is_available,
        ];
    }
}
