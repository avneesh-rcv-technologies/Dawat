<?php

namespace Modules\FoodManagements\Services;

use Modules\Core\Services\BaseCacheService;

class FoodItemCacheService extends BaseCacheService
{
    // =========================================================
    //  Cache Keys
    // =========================================================
    
    private function itemKey(int $id): string
    {
        return "food_item.{$id}.options";
    }

    private function categoriesKey(int $restaurantId): string
    {
        return "categories.{$restaurantId}";
    }

    private function itemsByCategoryKey(int $restaurantId, int $categoryId, array $filters): string
    {
        $filterHash = md5(json_encode($filters));
        return "food_items.{$restaurantId}.{$categoryId}.{$filterHash}";
    }

    private function featuredKey(int $restaurantId): string
    {
        return "featured_items.{$restaurantId}";
    }

    private function categoryImageKey(int $categoryId): string
    {
        return "image.FoodCategory.{$categoryId}";
    }

    private function itemImageKey(int $itemId): string
    {
        return "image.FoodItem.{$itemId}";
    }

    // =========================================================
    //  Cache Methods
    // =========================================================

    /**
     * Cache food item with options
     */
    public function rememberItem(int $id, callable $callback)
    {
        return $this->remember($this->itemKey($id), self::TTL_6_HOURS, $callback);
    }

    /**
     * Cache categories for a restaurant
     */
    public function rememberCategories(int $restaurantId, callable $callback)
    {
        return $this->remember($this->categoriesKey($restaurantId), self::TTL_4_HOURS, $callback);
    }

    /**
     * Cache food items by category with filters
     */
    public function rememberItemsByCategory(int $restaurantId, int $categoryId, array $filters, callable $callback)
    {
        return $this->remember(
            $this->itemsByCategoryKey($restaurantId, $categoryId, $filters),
            self::TTL_2_HOURS,
            $callback
        );
    }

    /**
     * Cache featured items
     */
    public function rememberFeatured(int $restaurantId, callable $callback)
    {
        return $this->remember($this->featuredKey($restaurantId), self::TTL_6_HOURS, $callback);
    }

    /**
     * Cache category image
     */
    public function rememberCategoryImage(int $categoryId, callable $callback)
    {
        return $this->remember($this->categoryImageKey($categoryId), self::TTL_MEDIUM, $callback);
    }

    /**
     * Cache food item image
     */
    public function rememberItemImage(int $itemId, callable $callback)
    {
        return $this->remember($this->itemImageKey($itemId), self::TTL_MEDIUM, $callback);
    }

    // =========================================================
    //  Invalidation Methods
    // =========================================================

    /**
     * Invalidate food item cache
     */
    public function forgetItem(int $id): void
    {
        $this->forget($this->itemKey($id));
        $this->forget($this->itemImageKey($id));
    }

    /**
     * Invalidate categories cache
     */
    public function forgetCategories(int $restaurantId): void
    {
        $this->forget($this->categoriesKey($restaurantId));
    }

    /**
     * Invalidate food items by category (all filter variations)
     */
    public function forgetItemsByCategory(int $restaurantId, int $categoryId): void
    {
        $this->forgetPattern("food_items.{$restaurantId}.{$categoryId}.*");
    }

    /**
     * Invalidate all food items for a restaurant
     */
    public function forgetAllItems(int $restaurantId): void
    {
        $this->forgetPattern("food_items.{$restaurantId}.*");
    }

    /**
     * Invalidate featured items cache
     */
    public function forgetFeatured(int $restaurantId): void
    {
        $this->forget($this->featuredKey($restaurantId));
    }

    /**
     * Invalidate category image cache
     */
    public function forgetCategoryImage(int $categoryId): void
    {
        $this->forget($this->categoryImageKey($categoryId));
    }

    /**
     * Invalidate food item image cache
     */
    public function forgetItemImage(int $itemId): void
    {
        $this->forget($this->itemImageKey($itemId));
    }
}
