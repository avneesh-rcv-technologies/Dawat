<?php

namespace Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_type',
        'plan_id',
        'price',
        'is_active',
        'type', // 'product' or 'direct'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Type constants
    const TYPE_PRODUCT = 'product'; // Admin-mapped product to plan
    const TYPE_DIRECT = 'direct';   // Direct plan purchase (auto-created)

    /**
     * Get the product entity (polymorphic relationship).
     * This can be any product: Product, Course, Service, etc.
     * Returns null for direct plan purchases (type='direct').
     */
    public function product(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if this is a direct plan purchase (no specific product).
     */
    public function isDirectPurchase(): bool
    {
        return $this->type === self::TYPE_DIRECT || ($this->product_type === null && $this->product_id === null);
    }

    /**
     * Check if this is a product-specific mapping.
     */
    public function isProductMapping(): bool
    {
        return $this->type === self::TYPE_PRODUCT && $this->product_type !== null && $this->product_id !== null;
    }

    /**
     * Get display name for this subscription product.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->isDirectPurchase()) {
            return $this->plan->name . ' (Direct Purchase)';
        }
        
        return $this->product 
            ? ($this->product->name ?? class_basename($this->product_type)) . ' - ' . $this->plan->name
            : $this->plan->name;
    }

    /**
     * Get the subscription plan associated with this product.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Get all subscriptions created from this product-plan mapping.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscription_product_id');
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    /**
     * Scope to get only active product-plan mappings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only direct plan purchases.
     */
    public function scopeDirect($query)
    {
        return $query->where('type', self::TYPE_DIRECT);
    }

    /**
     * Scope to get only product mappings.
     */
    public function scopeProductMappings($query)
    {
        return $query->where('type', self::TYPE_PRODUCT)
                    ->whereNotNull('product_type')
                    ->whereNotNull('product_id');
    }
}
