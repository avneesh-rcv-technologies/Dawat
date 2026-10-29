<?php

namespace Modules\FoodManagements\Services;

use Modules\FoodManagements\Repositories\FeaturedItemRepository;
use Illuminate\Validation\ValidationException;

class FeaturedItemService
{
    protected FeaturedItemRepository $repository;

    public function __construct(FeaturedItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data)
    {
        // Validate the featured type and selection method
        $this->validateFeaturedItemData($data);

        // Prepare JSON data structure
        $jsonData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'featured_type' => $data['featured_type'],
            'selection_method' => $data['selection_method'],
            'item_limit' => $data['item_limit'] ?? 5,
            'food_item_ids' => [],
            'food_category_ids' => [],
        ];

        // Add selected items for custom selection
        if ($data['selection_method'] === 'custom') {
            if ($data['featured_type'] === 'food_item') {
                $jsonData['food_item_ids'] = $data['food_items'] ?? [];
            } else {
                $jsonData['food_category_ids'] = $data['food_categories'] ?? [];
            }
        }

        // Create the featured item with JSON data
        $featuredItemData = [
            'data' => $jsonData,
            'status' => $data['status'] ?? true,
        ];

        return $this->repository->create($featuredItemData);
    }

    public function find(int $id)
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data)
    {
        // Validate the featured type and selection method
        $this->validateFeaturedItemData($data);

        // Get existing featured item
        $featuredItem = $this->repository->findById($id);
        $existingData = $featuredItem->data ?? [];

        // Prepare JSON data structure
        $jsonData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'featured_type' => $data['featured_type'],
            'selection_method' => $data['selection_method'],
            'item_limit' => $data['item_limit'] ?? 5,
            'food_item_ids' => [],
            'food_category_ids' => [],
        ];

        // Add selected items for custom selection
        if ($data['selection_method'] === 'custom') {
            if ($data['featured_type'] === 'food_item') {
                $jsonData['food_item_ids'] = $data['food_items'] ?? [];
            } else {
                $jsonData['food_category_ids'] = $data['food_categories'] ?? [];
            }
        }

        // Update the featured item with JSON data
        $featuredItemData = [
            'data' => $jsonData,
            'status' => $data['status'] ?? true,
        ];

        return $this->repository->update($id, $featuredItemData);
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
     * Get active featured items for display
     */
    public function getActiveFeaturedItems()
    {
        return $this->repository->getActiveFeaturedItems();
    }

    /**
     * Get featured food items only
     */
    public function getFeaturedFoodItems()
    {
        return $this->repository->getFeaturedFoodItems();
    }

    /**
     * Get featured categories only
     */
    public function getFeaturedCategories()
    {
        return $this->repository->getFeaturedCategories();
    }

    /**
     * Get featured item data formatted for frontend display
     */
    public function getFeaturedItemForDisplay(int $id)
    {
        $featuredItem = $this->find($id);
        
        return $featuredItem->getFormattedData();
    }

    /**
     * Get available food items for selection
     */
    public function getAvailableFoodItems()
    {
        return \Modules\FoodManagements\Models\FoodItem::where('is_active', true)
            ->where('is_available', true)
            ->with(['categories'])
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'sale_price' => $item->sale_price,
                    'categories' => $item->categories->pluck('name')->join(', '),
                    'image' => fn_get_image('food_image', $item->id)['url'] ?? '',
                ];
            });
    }

    /**
     * Get available food categories for selection
     */
    public function getAvailableFoodCategories()
    {
        return \Modules\FoodManagements\Models\FoodCategory::where('status', true)
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'food_type' => $category->food_type,
                    'items_count' => $category->foodItems()->where('is_active', true)->count(),
                    'image' => $category->image_url,
                ];
            });
    }

    /* ================= PRIVATE ================= */

    /**
     * Validate featured item data
     */
    private function validateFeaturedItemData(array $data): void
    {
        // Validate featured type
        if (!in_array($data['featured_type'] ?? '', ['food_item', 'category'])) {
            throw ValidationException::withMessages([
                'featured_type' => 'Please select either Food Items or Categories.'
            ]);
        }

        // Validate selection method
        $validMethods = array_keys(\Modules\FoodManagements\Models\FeaturedItem::getSelectionMethods());
        if (!in_array($data['selection_method'] ?? '', $validMethods)) {
            throw ValidationException::withMessages([
                'selection_method' => 'Please select a valid selection method.'
            ]);
        }

        // For custom selection, validate that items are selected
        if ($data['selection_method'] === 'custom') {
            $hasItems = false;
            
            if ($data['featured_type'] === 'food_item') {
                $hasItems = !empty($data['food_items']);
                if (!$hasItems) {
                    throw ValidationException::withMessages([
                        'food_items' => 'Please select at least one food item for custom selection.'
                    ]);
                }
            } else {
                $hasItems = !empty($data['food_categories']);
                if (!$hasItems) {
                    throw ValidationException::withMessages([
                        'food_categories' => 'Please select at least one category for custom selection.'
                    ]);
                }
            }
        }

        // Validate item limit for automatic methods
        if ($data['selection_method'] !== 'custom') {
            $limit = $data['item_limit'] ?? 5;
            if ($limit < 1 || $limit > 20) {
                throw ValidationException::withMessages([
                    'item_limit' => 'Item limit must be between 1 and 20.'
                ]);
            }
        }
    }
}