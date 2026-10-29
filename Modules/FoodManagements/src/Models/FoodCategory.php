<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Restaurant\Models\Restaurant;

class FoodCategory extends Model
{
    use HasFactory;

    protected $table = 'food_categories';

    protected $fillable = [
        'restaurant_id',
        'name',
        'slug',
        'description',
        'status',
        'image',
        'food_type',
        'parent_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /* ================= Constants ================= */

    public const TYPE_ALL     = 'all';
    public const TYPE_VEG     = 'veg';
    public const TYPE_NON_VEG = 'non_veg';
    public const TYPE_VEGAN   = 'vegan';

    /* ================= Relationships ================= */

    /**
     * Restaurant this category belongs to
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Parent category
     * NULL → main category
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Child categories (sub-categories)
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Food items assigned to this category/sub-category
     */
    public function foodItems()
    {
        return $this->belongsToMany(
            FoodItem::class,
            'food_category_items'
        );
    }

    /* ================= Scopes ================= */

    /**
     * Only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Only root categories (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Only sub-categories
     */
    public function scopeSub($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Filter by food type
     */
    public function scopeFoodType($query, string $type)
    {
        return $query->where('food_type', $type);
    }

    /**
     * Filter by restaurant
     */
    public function scopeByRestaurant($query, int $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    /* ================= Helpers ================= */

    /**
     * Check if this category allows given food type
     */
    public function allowsFoodType(string $foodType): bool
    {
        return $this->food_type === self::TYPE_ALL
            || $this->food_type === $foodType;
    }

    /**
     * Check if this is a main category
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a sub-category
     */
    public function isSubCategory(): bool
    {
        return ! is_null($this->parent_id);
    }

    // for image upload
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }

}
