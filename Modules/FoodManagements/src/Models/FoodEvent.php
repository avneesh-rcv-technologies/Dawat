<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodEvent extends Model
{
    use HasFactory;

    protected $table = 'food_events';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /* ================= Relationships ================= */

    /**
     * Food items associated with this event
     */
    public function foodItems()
    {
        return $this->belongsToMany(
            FoodItem::class,
            'food_event_items'
        );
    }

    /**
     * Food categories associated with this event
     */
    public function foodCategories()
    {
        return $this->belongsToMany(
            FoodCategory::class,
            'food_event_categories'
        );
    }

    /* ================= Scopes ================= */

    /**
     * Only active events
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /* ================= Helpers ================= */

    /**
     * Check if this event is active
     */
    public function isActive(): bool
    {
        return $this->status;
    }

    /**
     * Get all food items from associated categories and direct items
     */
    public function getAllFoodItems()
    {
        // Get direct food items
        $directItems = $this->foodItems()
            ->where('is_active', true)
            ->where('is_available', true)
            ->get();

        // Get food items from associated categories
        $categoryItems = collect();
        foreach ($this->foodCategories as $category) {
            $items = $category->foodItems()
                ->where('is_active', true)
                ->where('is_available', true)
                ->get();
            $categoryItems = $categoryItems->merge($items);
        }

        // Merge and remove duplicates
        return $directItems->merge($categoryItems)->unique('id');
    }

    /**
     * Get formatted event data for API
     */
    public function getFormattedData()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'food_items_count' => $this->foodItems()->count(),
            'food_categories_count' => $this->foodCategories()->count(),
            'total_items_count' => $this->getAllFoodItems()->count(),
        ];
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