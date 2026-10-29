<?php

namespace Modules\Cart\Traits;

use Modules\Cart\Models\Cart;
use Modules\Cart\Services\CartService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCart
{
    /**
     * Get all carts owned by this entity (Customer, Guest, etc.)
     */
    public function ownedCarts(): MorphMany
    {
        return $this->morphMany(Cart::class, 'owner');
    }

    /**
     * Get customer's cart for a specific restaurant using session
     */
    public function getCart(int $restaurantId, ?string $sessionId = null): ?Cart
    {
        $sessionId = $sessionId ?: session()->getId();
        
        return $this->ownedCarts()
            ->where('session_id', $sessionId)
            ->forRestaurant($restaurantId)
            ->with(['items.item'])
            ->first();
    }

    /**
     * Get all customer's carts for current session
     */
    public function getCarts(?string $sessionId = null)
    {
        $sessionId = $sessionId ?: session()->getId();
        
        return $this->ownedCarts()
            ->where('session_id', $sessionId)
            ->with(['items.item', 'cartable'])
            ->get();
    }

    /**
     * Get total cart items count across all restaurants
     */
    public function getTotalCartItems(): int
    {
        return $this->ownedCarts()
            ->withSum('items', 'quantity')
            ->get()
            ->sum('items_sum_quantity') ?? 0;
    }

    /**
     * Get cart items count for a specific restaurant using session
     */
    public function getCartItemsCount(int $restaurantId, ?string $sessionId = null): int
    {
        $cart = $this->getCart($restaurantId, $sessionId);
        return $cart ? $cart->total_items : 0;
    }

    /**
     * Check if customer has items in cart for a specific restaurant using session
     */
    public function hasCartItems(int $restaurantId, ?string $sessionId = null): bool
    {
        return $this->getCartItemsCount($restaurantId, $sessionId) > 0;
    }

    /**
     * Add item to cart using CartService
     */
    public function addToCart(
        int $restaurantId,
        int $foodItemId,
        int $quantity = 1,
        ?array $selectedOptions = null,
        ?string $specialInstructions = null
    ): array {
        $cartService = app(CartService::class);
        
        return $cartService->addToCart(
            $restaurantId,
            $foodItemId,
            $quantity,
            $selectedOptions,
            $specialInstructions,
            $this->id
        );
    }

    /**
     * Clear cart for a specific restaurant using session
     */
    public function clearCart(int $restaurantId, ?string $sessionId = null): array
    {
        $cartService = app(CartService::class);
        $sessionId = $sessionId ?: session()->getId();
        
        return $cartService->clearCart($restaurantId, $this->id, $sessionId);
    }

    /**
     * Get cart summary for all restaurants
     */
    public function getCartSummary(): array
    {
        $carts = $this->getCarts();
        
        $summary = [
            'total_restaurants' => $carts->count(),
            'total_items' => 0,
            'total_amount' => 0,
            'restaurants' => []
        ];

        foreach ($carts as $cart) {
            $cartable = $cart->cartable;
            $restaurantSummary = [
                'restaurant_id' => $cart->cartable_id,
                'restaurant_name' => $cartable ? $cartable->name : 'Unknown',
                'items_count' => $cart->total_items,
                'total_amount' => $cart->total,
                'currency' => fn_format_default_currency(),
                'formatted_total' => $cart->formatted_total
            ];

            $summary['restaurants'][] = $restaurantSummary;
            $summary['total_items'] += $cart->total_items;
            $summary['total_amount'] += $cart->total;
        }

        return $summary;
    }
}