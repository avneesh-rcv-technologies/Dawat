<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Restaurant\Models\Restaurant;

class FoodItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'slug',
        'description',
        'long_description',
        'sale_price',
        'cost_price',
        'list_price',
        'in_stock',
        'quantity_type',
        'quantity',
        'is_available',
        'is_active',
        'food_type',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'list_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    protected $appends = ['image', 'images'];

    public function getImageAttribute(): ?array
    {
        if (!$this->exists || !$this->id) {
            return null;
        }

        $image = fn_get_image('food_image', $this->id);

        if (!is_array($image)) {
            return null;
        }

        return [
            'id' => $image['id'] ?? $image['file_link_id'] ?? null,
            'file_link_id' => $image['file_link_id'] ?? null,
            'url' => $image['url'] ?? null,
            'path' => $image['path'] ?? null,
            'original_name' => $image['original_name'] ?? null,
        ];
    }

    public function getImagesAttribute(): array
    {
        if (!$this->exists || !$this->id) {
            return [];
        }

        return collect(fn_get_images('food_image', $this->id) ?? [])
            ->map(function ($image) {
                if (!is_array($image)) {
                    return null;
                }

                $imageId = $image['id'] ?? $image['file_link_id'] ?? null;

                if (empty($image['url']) && empty($imageId)) {
                    return null;
                }

                return [
                    'id' => $imageId,
                    'file_link_id' => $image['file_link_id'] ?? null,
                    'url' => $image['url'] ?? null,
                    'path' => $image['path'] ?? null,
                    'original_name' => $image['original_name'] ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /* ================= Relations ================= */

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function categories()
    {
        return $this->belongsToMany(
            FoodCategory::class,
            'food_category_items'
        );
    }

    public function optionGroups()
    {
        return $this->belongsToMany(
            FoodOptionGroup::class,
            'food_item_option_groups'
        );
    }

    public function tags()
    {
        return $this->belongsToMany(
            FoodTag::class,
            'food_item_tags'
        );
    }

    public function options()
    {
        return $this->belongsToMany(FoodOption::class, 'food_item_options')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function featureValues()
    {
        return $this->hasMany(FoodFeatureValue::class, 'food_item_id');
    }

    public function features()
    {
        return $this->belongsToMany(
            FoodFeature::class,
            'food_feature_values',
            'food_item_id',
            'feature_id'
        )->withPivot(['variant_id', 'value', 'value_int', 'lang_code']);
    }

    /* ================= Tax-Aware Pricing Methods ================= */

    /**
     * Get the display price with tax applied based on settings
     */
    public function getDisplayPriceAttribute(): float
    {
        return fn_get_food_item_display_price($this);
    }

    /**
     * Get tax calculation details for this food item
     */
    public function getTaxCalculation(?array $customTaxCategoryIds = null): array
    {
        return fn_calculate_food_item_tax($this, $customTaxCategoryIds);
    }

    /**
     * Get formatted price with tax information
     */
    public function getFormattedPriceWithTax(?string $currency = null): string
    {
        return fn_format_price_with_tax_info($this, $currency);
    }

    /**
     * Get tax breakdown for display
     */
    public function getTaxBreakdown(?array $customTaxCategoryIds = null): array
    {
        return fn_get_tax_breakdown($this, $customTaxCategoryIds);
    }

    public function getOptionPrice($optionId)
    {
        $option = $this->options()->where('food_option_id', $optionId)->first();
        return $option ? $option->pivot->price : 0;
    }

    public function setOptionPrice($optionId, $price)
    {
        $this->options()->syncWithoutDetaching([$optionId => ['price' => $price]]);
    }

    public function getAllAvailableOptions()
    {
        return $this->optionGroups()
            ->with([
                'options' => function ($query) {
                    $query->where('is_active', true);
                }
            ])
            ->get()
            ->pluck('options')
            ->flatten();
    }

    /* ================= Quantity Type Helper Methods ================= */

    /**
     * Check if this food item uses manual quantity type
     */
    public function isManualQuantityType(): bool
    {
        return $this->quantity_type === 'manual';
    }

    /**
     * Check if this food item uses grouping quantity type
     */
    public function isGroupingQuantityType(): bool
    {
        return $this->quantity_type === 'grouping';
    }

    /**
     * Check if this food item uses piece quantity type
     */
    public function isPieceQuantityType(): bool
    {
        return $this->quantity_type === 'piece';
    }

    /**
     * Get the quantity label based on type
     */
    public function getQuantityLabelAttribute(): string
    {
        return $this->isGroupingQuantityType() ? 'Minimum Guests' : 'Stock Quantity';
    }

    /**
     * Get formatted quantity display
     */
    public function getFormattedQuantityAttribute(): string
    {
        if (!$this->quantity) {
            return 'N/A';
        }

        return $this->isGroupingQuantityType()
            ? $this->quantity . ' guests minimum'
            : $this->quantity . ' in stock';
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

    public function longDescription(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }

}
