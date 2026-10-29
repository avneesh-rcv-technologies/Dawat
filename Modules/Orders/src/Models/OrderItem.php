<?php

namespace Modules\Orders\Models;

use Modules\Orders\Models\Order;
use Modules\FoodManagements\Models\FoodItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'item_name',
        'sku',
        'price',
        'quantity',
        'stock_reserved_quantity',
        'tax_rate',
        'selected_options',
        'special_instructions',
        'total_price'
    ];

    protected $casts = [
        'stock_reserved_quantity' => 'integer',
        'selected_options' => 'array',
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    /* ================= Relationships ================= */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Polymorphic relationship to item (FoodItem, Product, etc.)
     * Usage examples:
     * - For FoodItem: $orderItem->item_type = 'Modules\FoodManagements\Models\FoodItem'; $orderItem->item_id = $foodItemId;
     */
    public function item()
    {
        return $this->morphTo();
    }

    /**
     * Legacy relationship for backward compatibility
     * @deprecated Use item() instead
     */
    public function foodItem()
    {
        return $this->morphTo('item');
    }

    /* ================= Accessors & Mutators ================= */

    public function getTotalAttribute()
    {
        return $this->total_price;
    }

    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function getOptionsTotal()
    {
        if (!$this->selected_options) {
            return 0;
        }

        $total = 0;
        foreach ($this->selected_options as $option) {
            $total += $option['price'] ?? 0;
        }
        
        return $total * $this->quantity;
    }

    public function getTaxAmount()
    {
        return ($this->total_price * $this->tax_rate) / 100;
    }

    /* ================= Helpers ================= */

    public function calculateTotalPrice()
    {
        $basePrice = $this->price * $this->quantity;
        $optionsPrice = $this->getOptionsTotal();
        
        return $basePrice + $optionsPrice;
    }

    public function getFormattedOptions()
    {
        if (!$this->selected_options) {
            return '';
        }

        $formatted = [];
        foreach ($this->selected_options as $option) {
            $formatted[] = ($option['name'] ?? 'Option') . ' (+$' . number_format($option['price'] ?? 0, 2) . ')';
        }

        return implode(', ', $formatted);
    }

    /* ================= Boot Method ================= */

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($orderItem) {
            if (!$orderItem->total_price) {
                $orderItem->total_price = $orderItem->calculateTotalPrice();
            }
        });
    }
}
