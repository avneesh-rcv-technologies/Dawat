<?php

namespace Modules\Cart\Models;

use App\Models\User;
use Modules\Acl\Models\Customer;
use Modules\Cart\Models\CartItem;
use Modules\Discounts\Models\Discount;
use Illuminate\Database\Eloquent\Model;
use Modules\Restaurant\Models\Restaurant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'owner_type',
        'owner_id',
        'cartable_type',
        'cartable_id',
        'subtotal',
        'tax_amount',
        'total',
        'is_tax_inclusive',
        'tax_breakdown',
        'coupon_code',
        'discount_type',
        'discount_id',
        'discount_amount',
        'discount_percentage',
        'discount_breakdown',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_tax_inclusive' => 'boolean',
        'tax_breakdown' => 'array',
        'discount_breakdown' => 'array',
    ];

    /* ================= Relations ================= */

    /**
     * Polymorphic relation to owner (User, Guest, etc.)
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Polymorphic relation to cartable entity (Restaurant, Vendor, etc.)
     */
    public function cartable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Polymorphic relation to discount
     */
    public function discount(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Legacy user relation for backward compatibility
     */
    public function user()
    {
        return $this->owner_type === Customer::class ? $this->owner() : null;
    }

    /**
     * Customer relation (primary relation)
     */
    public function customer()
    {
        return $this->owner_type === Customer::class ? $this->owner() : null;
    }

    /**
     * Legacy restaurant relation for backward compatibility
     */
    public function restaurant()
    {
        return $this->cartable_type === Restaurant::class ? $this->cartable() : null;
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /* ================= Scopes ================= */

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForOwner($query, string $ownerType, int $ownerId)
    {
        return $query->where('owner_type', $ownerType)
                    ->where('owner_id', $ownerId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('owner_type', Customer::class)
                    ->where('owner_id', $userId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('owner_type', Customer::class)
                    ->where('owner_id', $customerId);
    }

    public function scopeForSessionAndCustomer($query, string $sessionId, ?int $customerId = null)
    {
        $query->where('session_id', $sessionId);
        
        if ($customerId) {
            $query->where('owner_type', Customer::class)
                  ->where('owner_id', $customerId);
        }
        
        return $query;
    }

    public function scopeForCartable($query, string $cartableType, int $cartableId)
    {
        return $query->where('cartable_type', $cartableType)
                    ->where('cartable_id', $cartableId);
    }

    public function scopeForRestaurant($query, int $restaurantId)
    {
        return $query->where('cartable_type', Restaurant::class)
                    ->where('cartable_id', $restaurantId);
    }

    /* ================= Helper Methods ================= */

    /**
     * Get total items count in cart
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->items->count() === 0;
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return fn_format_default_currency() . number_format($this->subtotal, 2);
    }

    /**
     * Get formatted tax amount
     */
    public function getFormattedTaxAmountAttribute(): string
    {
        return fn_format_default_currency() . number_format($this->tax_amount, 2);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return fn_format_default_currency() . number_format($this->total, 2);
    }

    /**
     * Get formatted discount amount
     */
    public function getFormattedDiscountAmountAttribute(): string
    {
        return fn_format_default_currency() . number_format($this->discount_amount, 2);
    }

    /**
     * Check if cart has discount applied
     */
    public function hasDiscount(): bool
    {
        return $this->discount_amount > 0 && !empty($this->coupon_code);
    }

    /**
     * Calculate and update cart totals
     */
    public function updateTotals(): void
    {
        // Ensure items are loaded
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        
        $this->subtotal = $this->items->sum('total_price');
        $this->tax_amount = $this->items->sum('total_tax_amount');
        $totalBeforeDiscount = $this->items->sum('total_display_price');
        
        // Apply discount to final total
        $this->total = max(0, $totalBeforeDiscount - $this->discount_amount);
        
        // Update tax breakdown
        $this->tax_breakdown = $this->calculateTaxBreakdown();
        
        $this->save();
    }

    /**
     * Calculate detailed tax breakdown
     */
    private function calculateTaxBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->items as $item) {
            if ($item->tax_breakdown) {
                foreach ($item->tax_breakdown as $taxCategory => $taxData) {
                    if (!isset($breakdown[$taxCategory])) {
                        $breakdown[$taxCategory] = [
                            'name' => $taxData['name'] ?? $taxCategory,
                            'rate' => $taxData['rate'] ?? 0,
                            'amount' => 0,
                        ];
                    }
                    $breakdown[$taxCategory]['amount'] += $taxData['amount'] ?? 0;
                }
            }
        }

        return $breakdown;
    }
}