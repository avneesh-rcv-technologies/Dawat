<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\FoodManagements\Models\FoodItem;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'delivery_fee',
        'estimated_delivery_minutes',
        'estimated_delivery_at',
        'item_type',
        'item_id',
        'quantity',
        'unit_price',
        'unit_display_price',
        'unit_tax_amount',
        'total_price',
        'total_display_price',
        'total_tax_amount',
        'is_tax_inclusive',
        'tax_breakdown',
        'selected_options',
        'special_instructions',
        'serving_option_id',
        'serve_type',
        'serving_name',
        'guest_count',
        'subtotal',
        'tax_amount',
        'final_price',
        'discount_price',
        'display_title',
        'display_subtitle',
        'display_price_label',
        'display_unit_label',
        'display_unit_price_text',
        'display_per_person_price',
        'selected_option_snapshot',
        'note',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'estimated_delivery_minutes' => 'integer',
        'estimated_delivery_at' => 'datetime',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_display_price' => 'decimal:2',
        'unit_tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'total_display_price' => 'decimal:2',
        'total_tax_amount' => 'decimal:2',
        'is_tax_inclusive' => 'boolean',
        'tax_breakdown' => 'array',
        'selected_options' => 'array',
        'serving_option_id' => 'integer',
        'guest_count' => 'integer',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'display_per_person_price' => 'decimal:2',
        'selected_option_snapshot' => 'array',
    ];

    /* ================= Relations ================= */

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Polymorphic relation to item (FoodItem, Product, etc.)
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function servingOption(): BelongsTo
    {
        return $this->belongsTo(\Modules\FoodManagements\Models\FoodItemServingOption::class, 'serving_option_id');
    }

    /**
     * Legacy foodItem relation for backward compatibility
     */
    public function foodItem()
    {
        return $this->item_type === FoodItem::class ? $this->item() : null;
    }

    /* ================= Helper Methods ================= */

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return fn_format_default_currency() . number_format($this->unit_price, 2);
    }

    /**
     * Get formatted unit display price
     */
    public function getFormattedUnitDisplayPriceAttribute(): string
    {
        return fn_format_default_currency() . number_format($this->unit_display_price, 2);
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return fn_format_default_currency() . number_format($this->total_price, 2);
    }

    /**
     * Get formatted total display price
     */
    public function getFormattedTotalDisplayPriceAttribute(): string
    {
        return fn_format_default_currency() . number_format($this->total_display_price, 2);
    }

    /**
     * Calculate total options price
     */
    public function getOptionsTotalPriceAttribute(): float
    {
        if (!$this->selected_options) {
            return 0;
        }

        $total = 0;
        foreach ($this->selected_options as $selectedOption) {
            $optionId = $selectedOption['option_id'] ?? null;
            if ($optionId) {
                $option = \Modules\FoodManagements\Models\FoodOption::find($optionId);
                if ($option && $this->item_type === FoodItem::class) {
                    $total += $option->getPriceForFoodItem($this->item_id);
                }
            }
        }

        return $total;
    }

    /**
     * Get selected options names
     */
    public function getSelectedOptionsNamesAttribute(): array
    {
        if (!$this->selected_options) {
            return [];
        }

        $names = [];
        foreach ($this->selected_options as $selectedOption) {
            $optionGroupId = $selectedOption['option_group_id'] ?? null;
            $optionId = $selectedOption['option_id'] ?? null;
            
            if ($optionGroupId && $optionId) {
                $optionGroup = \Modules\FoodManagements\Models\FoodOptionGroup::find($optionGroupId);
                $option = \Modules\FoodManagements\Models\FoodOption::find($optionId);
                
                if ($optionGroup && $option) {
                    $names[] = $optionGroup->name . ': ' . $option->name;
                }
            }
        }

        return $names;
    }

    /**
     * Update item totals based on quantity
     */
    public function updateTotals(): void
    {
        $this->total_price = $this->unit_price * $this->quantity;
        $this->total_display_price = $this->unit_display_price * $this->quantity;
        $this->total_tax_amount = $this->unit_tax_amount * $this->quantity;
        
        // Update tax breakdown for total quantity
        if ($this->tax_breakdown) {
            $updatedBreakdown = [];
            foreach ($this->tax_breakdown as $taxCategory => $taxData) {
                $updatedBreakdown[$taxCategory] = $taxData;
                $updatedBreakdown[$taxCategory]['amount'] = ($taxData['amount'] ?? 0) * $this->quantity;
            }
            $this->tax_breakdown = $updatedBreakdown;
        }
        
        $this->save();
    }

    /**
     * Check if item has selected options
     */
    public function hasSelectedOptions(): bool
    {
        return !empty($this->selected_options);
    }

    /**
     * Get item display name with options
     */
    public function getDisplayNameAttribute(): string
    {
        $item = $this->item;
        $name = $item ? $item->name : 'Unknown Item';
        
        if ($this->hasSelectedOptions()) {
            $optionNames = $this->selected_options_names;
            if (!empty($optionNames)) {
                $name .= ' (' . implode(', ', $optionNames) . ')';
            }
        }
        
        return $name;
    }
}
