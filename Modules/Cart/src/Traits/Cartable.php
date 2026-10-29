<?php

namespace Modules\Cart\Traits;

use Modules\Cart\Models\Cart;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Cartable
{
    /**
     * Get all carts for this cartable entity (Restaurant, Vendor, etc.)
     */
    public function carts(): MorphMany
    {
        return $this->morphMany(Cart::class, 'cartable');
    }

    /**
     * Get active carts for this cartable entity
     */
    public function activeCarts(): MorphMany
    {
        return $this->carts()->whereHas('items');
    }

    /**
     * Get cart count for this cartable entity
     */
    public function getCartCountAttribute(): int
    {
        return $this->carts()->count();
    }

    /**
     * Get active cart count for this cartable entity
     */
    public function getActiveCartCountAttribute(): int
    {
        return $this->activeCarts()->count();
    }
}