<?php

namespace Modules\Cart\Services;

use Modules\Core\Services\BaseCacheService;

class CartCacheService extends BaseCacheService
{
    // =========================================================
    //  Cache Keys
    // =========================================================
    
    private function cartKey(int $customerId, ?int $restaurantId = null): string
    {
        return $restaurantId 
            ? "cart.{$customerId}.{$restaurantId}"
            : "cart.{$customerId}";
    }

    private function countKey(int $customerId): string
    {
        return "cart_count.{$customerId}";
    }

    // =========================================================
    //  Cache Methods
    // =========================================================

    /**
     * Cache cart data
     */
    public function rememberCart(int $customerId, ?int $restaurantId, callable $callback)
    {
        return $this->remember($this->cartKey($customerId, $restaurantId), self::TTL_LONG, $callback);
    }

    /**
     * Cache cart count
     */
    public function rememberCount(int $customerId, callable $callback)
    {
        return $this->remember($this->countKey($customerId), self::TTL_LONG, $callback);
    }

    // =========================================================
    //  Invalidation Methods
    // =========================================================

    /**
     * Invalidate cart cache
     */
    public function forgetCart(int $customerId, ?int $restaurantId = null): void
    {
        if ($restaurantId) {
            $this->forget($this->cartKey($customerId, $restaurantId));
        } else {
            // Clear all restaurant carts for this customer
            $this->forgetPattern("cart.{$customerId}.*");
        }
        $this->forget($this->countKey($customerId));
    }

    /**
     * Invalidate cart count cache
     */
    public function forgetCount(int $customerId): void
    {
        $this->forget($this->countKey($customerId));
    }
}
