<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeaturedItem extends Model
{
    use HasFactory;

    protected $table = 'featured_items';

    protected $fillable = [
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'status' => 'boolean',
    ];

    /* ================= Constants ================= */

    public const TYPE_FOOD_ITEM = 'food_item';
    public const TYPE_CATEGORY = 'category';

    public const METHOD_CUSTOM = 'custom';
    public const METHOD_RATING = 'rating';
    public const METHOD_MOST_BUYING = 'most_buying';
    public const METHOD_TRENDING = 'trending';
    public const METHOD_NEW_ARRIVALS = 'new_arrivals';

    public static function getSelectionMethods(): array
    {
        return [
            self::METHOD_CUSTOM => 'Custom Selection',
            self::METHOD_RATING => 'Highest Rated',
            self::METHOD_MOST_BUYING => 'Most Buying',
            self::METHOD_TRENDING => 'Trending Items',
            self::METHOD_NEW_ARRIVALS => 'New Arrivals',
        ];
    }

    /* ================= Accessors ================= */

    /**
     * Get title from JSON data
     */
    public function getTitleAttribute()
    {
        return $this->data['title'] ?? '';
    }

    /**
     * Get description from JSON data
     */
    public function getDescriptionAttribute()
    {
        return $this->data['description'] ?? '';
    }

    /**
     * Get featured_type from JSON data
     */
    public function getFeaturedTypeAttribute()
    {
        return $this->data['featured_type'] ?? '';
    }

    /**
     * Get selection_method from JSON data
     */
    public function getSelectionMethodAttribute()
    {
        return $this->data['selection_method'] ?? 'custom';
    }

    /**
     * Get item_limit from JSON data
     */
    public function getItemLimitAttribute()
    {
        return $this->data['item_limit'] ?? 5;
    }

    /**
     * Get food_item_ids from JSON data
     */
    public function getFoodItemIdsAttribute()
    {
        return $this->data['food_item_ids'] ?? [];
    }

    /**
     * Get food_category_ids from JSON data
     */
    public function getFoodCategoryIdsAttribute()
    {
        return $this->data['food_category_ids'] ?? [];
    }

    /* ================= Relationships ================= */

    /**
     * Get featured food items as a collection (for JSON-based storage)
     */
    public function getFoodItemsAttribute()
    {
        $foodItemIds = $this->food_item_ids;
        if (empty($foodItemIds)) {
            return collect();
        }
        
        return FoodItem::whereIn('id', $foodItemIds)
            ->where('is_active', true)
            ->where('is_available', true)
            ->get();
    }

    /**
     * Get featured food categories as a collection (for JSON-based storage)
     */
    public function getFoodCategoriesAttribute()
    {
        $categoryIds = $this->food_category_ids;
        if (empty($categoryIds)) {
            return collect();
        }
        
        return FoodCategory::whereIn('id', $categoryIds)
            ->where('status', true)
            ->get();
    }

    /**
     * Legacy relationship method for foodItems (returns collection for compatibility)
     * This maintains compatibility with existing blade templates
     */
    public function foodItems()
    {
        return $this->getFoodItemsAttribute();
    }

    /**
     * Legacy relationship method for foodCategories (returns collection for compatibility)
     * This maintains compatibility with existing blade templates
     */
    public function foodCategories()
    {
        return $this->getFoodCategoriesAttribute();
    }

    /* ================= Scopes ================= */

    /**
     * Only active featured items
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Only featured food items
     */
    public function scopeFoodItems($query)
    {
        return $query->whereJsonContains('data->featured_type', 'food_item');
    }

    /**
     * Only featured categories
     */
    public function scopeCategories($query)
    {
        return $query->whereJsonContains('data->featured_type', 'category');
    }

    /* ================= Helpers ================= */

    /**
     * Check if this featured item is active
     */
    public function isActive(): bool
    {
        return $this->status;
    }

    /**
     * Check if this is a featured food item
     */
    public function isFoodItem(): bool
    {
        return $this->featured_type === self::TYPE_FOOD_ITEM;
    }

    /**
     * Check if this is a featured category
     */
    public function isCategory(): bool
    {
        return $this->featured_type === self::TYPE_CATEGORY;
    }

    /**
     * Get the featured item type
     */
    public function getType(): string
    {
        return $this->featured_type ?? 'unknown';
    }

    /**
     * Check if selection method is custom
     */
    public function isCustomSelection(): bool
    {
        return $this->selection_method === self::METHOD_CUSTOM;
    }

    /**
     * Get items based on selection method
     */
    public function getSelectedItems()
    {
        if ($this->isCustomSelection()) {
            // Return manually selected items
            if ($this->isFoodItem()) {
                return $this->food_items;
            } else {
                return $this->food_categories;
            }
        }

        // Return items based on automatic selection method
        return $this->getAutomaticItems();
    }

    /**
     * Get items automatically based on selection method
     */
    public function getAutomaticItems()
    {
        $limit = $this->item_limit ?? 5;

        if ($this->isFoodItem()) {
            return $this->getAutomaticFoodItems($limit);
        } else {
            return $this->getAutomaticCategories($limit);
        }
    }

    /**
     * Get automatic food items based on selection method
     */
    private function getAutomaticFoodItems($limit)
    {
        $query = FoodItem::where('is_active', true)->where('is_available', true);

        switch ($this->selection_method) {
            case self::METHOD_RATING:
                // Assuming you have a rating system
                $query->orderBy('average_rating', 'desc');
                break;
            case self::METHOD_MOST_BUYING:
                // Assuming you have order statistics
                $query->withCount('orders')->orderBy('orders_count', 'desc');
                break;
            case self::METHOD_TRENDING:
                // Items with recent high activity
                $query->where('created_at', '>=', now()->subDays(30))
                      ->orderBy('created_at', 'desc');
                break;
            case self::METHOD_NEW_ARRIVALS:
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get automatic categories based on selection method
     */
    private function getAutomaticCategories($limit)
    {
        $query = FoodCategory::where('status', true);

        switch ($this->selection_method) {
            case self::METHOD_RATING:
                // Categories with highest rated items
                $query->withAvg('foodItems', 'average_rating')
                      ->orderBy('food_items_avg_average_rating', 'desc');
                break;
            case self::METHOD_MOST_BUYING:
                // Categories with most orders
                $query->withCount(['foodItems as total_orders' => function($q) {
                    $q->withCount('orders');
                }])->orderBy('total_orders', 'desc');
                break;
            case self::METHOD_TRENDING:
            case self::METHOD_NEW_ARRIVALS:
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get the featured item names
     */
    public function getItemNames(): string
    {
        $names = [];
        
        $foodItems = $this->food_items;
        if ($foodItems->count() > 0) {
            $names = array_merge($names, $foodItems->pluck('name')->toArray());
        }
        
        $foodCategories = $this->food_categories;
        if ($foodCategories->count() > 0) {
            $names = array_merge($names, $foodCategories->pluck('name')->toArray());
        }

        return implode(', ', $names) ?: 'No items selected';
    }

    /**
     * Get the featured item image (first available)
     */
    public function getItemImage(): ?string
    {
        // Try to get image from first food item
        $foodItems = $this->food_items;
        if ($foodItems->count() > 0) {
            $firstItem = $foodItems->first();
            if ($firstItem) {
                return fn_get_image('food_image', $firstItem->id)['url'] ?? null;
            }
        }
        
        // Try to get image from first category
        $foodCategories = $this->food_categories;
        if ($foodCategories->count() > 0) {
            $firstCategory = $foodCategories->first();
            if ($firstCategory) {
                return $firstCategory->image_url;
            }
        }

        return null;
    }

    /**
     * Get formatted data for API
     */
    public function getFormattedData(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->getType(),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'item' => $this->getItemData(),
        ];
    }

    /**
     * Get the associated item data
     */
    private function getItemData(): array
    {
        $data = [
            'food_items' => [],
            'food_categories' => [],
        ];

        $foodItems = $this->food_items;
        if ($foodItems->count() > 0) {
            $data['food_items'] = $foodItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'sale_price' => $item->sale_price,
                    'list_price' => $item->list_price,
                    'image' => fn_get_image('food_image', $item->id)['url'] ?? null,
                    'categories' => $item->categories->pluck('name')->join(', '),
                ];
            })->toArray();
        }

        $foodCategories = $this->food_categories;
        if ($foodCategories->count() > 0) {
            $data['food_categories'] = $foodCategories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'food_type' => $category->food_type,
                    'image' => $category->image_url,
                    'items_count' => $category->foodItems()->where('is_active', true)->count(),
                ];
            })->toArray();
        }

        return $data;
    }
}