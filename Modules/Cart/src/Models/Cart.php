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
        'delivery_address_id',
        'delivery_fee',
        'delivery_distance_miles',
        'delivery_available',
        'delivery_unavailable_reason',
        'estimated_delivery_minutes',
        'estimated_delivery_at',
        'delivery_calculated_at',
        'event_guest_count',
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
        'delivery_address_id' => 'integer',
        'delivery_fee' => 'decimal:2',
        'delivery_distance_miles' => 'decimal:4',
        'delivery_available' => 'boolean',
        'estimated_delivery_minutes' => 'integer',
        'estimated_delivery_at' => 'datetime',
        'delivery_calculated_at' => 'datetime',
        'event_guest_count' => 'integer',
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
        return $this->discount_amount > 0;
    }

    /**
     * Calculate and update cart totals
     */
    public function updateTotals(): void
    {
        // Reload after adds/removals so a previously loaded relation cannot omit a line.
        $this->load('items');
        $subtotalCents = $this->items->sum(fn ($item) => (int) round((float) $item->total_price * 100));
        $taxCents = $this->items->sum(fn ($item) => (int) round((float) $item->total_tax_amount * 100));
        $displayCents = $this->items->sum(fn ($item) => (int) round((float) $item->total_display_price * 100));
        $itemDiscountCents = (int) round($this->item_discount_amount * 100);
        $regularSubtotalCents = $subtotalCents + $itemDiscountCents;
        $couponDiscountCents = min($subtotalCents, max(0, (int) round($this->coupon_discount_amount * 100)));
        $discountCents = $itemDiscountCents + $couponDiscountCents;
        $previousBreakdown = $this->discount_breakdown ?? [];
        $couponPercentage = $this->coupon_code
            ? (($previousBreakdown['summary_version'] ?? null) === 1
                ? ($previousBreakdown['coupon_discount_percentage'] ?? null)
                : $this->discount_percentage)
            : null;

        $this->subtotal = $subtotalCents / 100;
        $this->tax_amount = $taxCents / 100;
        $this->discount_amount = $discountCents / 100;
        // Weighted effective saving against the regular pre-tax cart price.
        $this->discount_percentage = $regularSubtotalCents > 0
            ? round($discountCents * 100 / $regularSubtotalCents, 2)
            : 0;
        // Item savings are already included in line prices; subtract only the coupon.
        $this->total = max(0, $displayCents - $couponDiscountCents) / 100;
        $this->discount_breakdown = array_replace($this->coupon_code ? $previousBreakdown : [], [
            'summary_version' => 1,
            'coupon_code' => $this->coupon_code,
            'subtotal_before_discount' => number_format($regularSubtotalCents / 100, 2, '.', ''),
            'subtotal_after_item_discount' => number_format($subtotalCents / 100, 2, '.', ''),
            'item_discount_amount' => number_format($itemDiscountCents / 100, 2, '.', ''),
            'coupon_discount_amount' => number_format($couponDiscountCents / 100, 2, '.', ''),
            'coupon_discount_percentage' => $couponPercentage,
            'discount_amount' => $this->discount_amount,
            'discount_percentage' => $this->discount_percentage,
        ]);
        $this->tax_breakdown = $this->calculateTaxBreakdown();
        app(\Modules\Cart\Services\CartDeliveryService::class)->apply($this);
        $this->total = round((float) $this->total + (float) ($this->delivery_fee ?? 0), 2);
        $this->save();
    }

    public function getItemDiscountAmountAttribute(): float
    {
        $cents = $this->items->sum(fn ($item) => $item->selected_option_snapshot !== null
            ? max(0, (int) round((float) $item->discount_price * 100)) : 0);

        return $cents / 100;
    }

    public function getCouponDiscountAmountAttribute(): float
    {
        if (empty($this->coupon_code)) {
            return 0;
        }

        $breakdown = $this->discount_breakdown ?? [];

        // Legacy coupon writes put their amount in discount_amount before recalculation.
        return (float) (($breakdown['summary_version'] ?? null) === 1
            ? ($breakdown['coupon_discount_amount'] ?? 0)
            : $this->discount_amount);
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
                    $rate = (float) ($taxData['rate'] ?? 0);
                    if (isset($breakdown[$taxCategory]) && (float) $breakdown[$taxCategory]['rate'] !== $rate) {
                        $taxCategory .= '_'.str_replace('.', '_', number_format($rate, 2, '.', ''));
                    }
                    if (!isset($breakdown[$taxCategory])) {
                        $breakdown[$taxCategory] = [
                            'name' => $taxData['name'] ?? $taxCategory,
                            'rate' => $taxData['rate'] ?? 0,
                            'amount' => 0,
                        ];
                    }
                    $breakdown[$taxCategory]['amount'] = round($breakdown[$taxCategory]['amount'] + ($taxData['amount'] ?? 0), 2);
                }
            }
        }

        return $breakdown;
    }
}
