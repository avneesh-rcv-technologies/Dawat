<?php

namespace Modules\Cart\Traits;

use Modules\Cart\Models\CartItem;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait CartItemable
{
    /**
     * Get all cart items for this itemable entity (FoodItem, Product, etc.)
     */
    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'item');
    }

    /**
     * Get cart items count for this itemable entity
     */
    public function getCartItemsCountAttribute(): int
    {
        return $this->cartItems()->count();
    }

    /**
     * Get total quantity in carts for this itemable entity
     */
    public function getTotalCartQuantityAttribute(): int
    {
        return $this->cartItems()->sum('quantity');
    }

    /**
     * Check if this item is currently in any cart
     */
    public function isInCart(): bool
    {
        return $this->cartItems()->exists();
    }
}