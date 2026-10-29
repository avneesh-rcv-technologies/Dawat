<?php

namespace Modules\Cart\Repositories;

use Modules\Acl\Models\Customer;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Restaurant\Models\Restaurant;
use Modules\FoodManagements\Models\FoodItem;
use Illuminate\Database\Eloquent\Collection;

class CartRepository
{
    /**
     * Find cart by session ID and restaurant ID (for both guest and authenticated users)
     */
    public function findBySession(string $sessionId, int $restaurantId): ?Cart
    {
        return Cart::with(['items.item'])
            ->forSession($sessionId)
            ->forRestaurant($restaurantId)
            ->first();
    }

    /**
     * Find cart by user ID and restaurant ID (legacy method - use findBySession instead)
     */
    public function findByUser(int $userId, int $restaurantId): ?Cart
    {
        return Cart::with(['items.item'])
            ->forUser($userId)
            ->forRestaurant($restaurantId)
            ->first();
    }

    /**
     * Find cart by session and optionally customer (preferred method)
     */
    public function findBySessionAndCustomer(string $sessionId, int $restaurantId, ?int $customerId = null): ?Cart
    {
        $query = Cart::with(['items.item'])
            ->forSession($sessionId)
            ->forRestaurant($restaurantId);

        if ($customerId) {
            $query->where(function ($q) use ($customerId) {
                $q->where('owner_type', Customer::class)
                    ->where('owner_id', $customerId);
            });
        }

        return $query->first();
    }

    /**
     * Create new cart
     */
    public function create(array $data): Cart
    {
        // Transform legacy data to morph structure
        $morphData = $this->transformToMorphData($data);
        return Cart::create($morphData);
    }

    /**
     * Update cart
     */
    public function update(Cart $cart, array $data): Cart
    {
        $morphData = $this->transformToMorphData($data);
        $cart->update($morphData);
        return $cart->fresh();
    }

    /**
     * Delete cart
     */
    public function delete(Cart $cart): bool
    {
        return $cart->delete();
    }

    /**
     * Find cart item by cart and item
     */
    public function findCartItem(int $cartId, int $itemId, ?array $selectedOptions = null, string $itemType = null): ?CartItem
    {
        $itemType = $itemType ?: FoodItem::class;

        $query = CartItem::where('cart_id', $cartId)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId);

        // If options are provided, match them exactly
        if ($selectedOptions !== null) {
            $query->where(function ($q) use ($selectedOptions) {
                if (empty($selectedOptions)) {
                    $q->whereNull('selected_options')
                        ->orWhere('selected_options', '[]')
                        ->orWhere('selected_options', '{}');
                } else {
                    // Sort the options to ensure consistent comparison
                    $sortedOptions = collect($selectedOptions)->sortBy(function ($option) {
                        return ($option['option_group_id'] ?? 0) . '_' . ($option['option_id'] ?? 0);
                    })->values()->toArray();

                    $q->where('selected_options', json_encode($sortedOptions));
                }
            });
        }

        return $query->first();
    }

    /**
     * Add item to cart
     */
    public function addItem(Cart $cart, array $itemData): CartItem
    {
        // Transform legacy data to morph structure
        $morphItemData = $this->transformItemToMorphData($itemData);

        $cartItem = CartItem::create(array_merge($morphItemData, [
            'cart_id' => $cart->id,
        ]));

        $cartItem->updateTotals();
        $cart->updateTotals();

        return $cartItem->load('item');
    }

    /**
     * Update cart item
     */
    public function updateItem(CartItem $cartItem, array $data): CartItem
    {
        $cartItem->update($data);
        $cartItem->updateTotals();
        $cartItem->cart->updateTotals();

        return $cartItem->fresh(['item']);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(CartItem $cartItem): array
    {
        $cart = $cartItem->cart;
        $result = $cartItem->delete();
        $cartDeleted = false;

        if ($result) {
            // Refresh the cart to get updated items count
            $cart->refresh();

            // If cart is now empty, delete the cart entirely
            if ($cart->items()->count() === 0) {
                $cart->delete();
                $cartDeleted = true;
            } else {
                // Otherwise, just update totals
                $cart->updateTotals();
            }
        }

        return [
            'success' => $result,
            'cart_deleted' => $cartDeleted,
            'cart' => $cartDeleted ? null : $cart->fresh()
        ];
    }

    /**
     * Clear all items from cart
     */
    public function clearCart(Cart $cart): array
    {
        $result = $cart->items()->delete();

        if ($result) {
            // After clearing all items, delete the cart entirely
            $cart->delete();
        }

        return [
            'success' => $result,
            'cart_deleted' => $result
        ];
    }

    /**
     * Get cart with all relationships
     */
    public function getCartWithItems(Cart $cart): Cart
    {
        return $cart->load([
            'items.item',
            'cartable'
        ]);
    }

    /**
     * Transfer guest cart to customer cart (merge session-based cart with customer)
     */
    public function transferGuestCartToUser(string $sessionId, int $userId, int $restaurantId): ?Cart
    {
        $guestCart = $this->findBySession($sessionId, $restaurantId);

        if (!$guestCart) {
            return null;
        }

        // If cart already has customer ownership, just return it
        if ($guestCart->owner_type === Customer::class && $guestCart->owner_id == $userId) {
            return $guestCart;
        }

        // Check if customer already has a cart for this restaurant with different session
        $existingCustomerCart = $this->findByUser($userId, $restaurantId);

        if ($existingCustomerCart && $existingCustomerCart->id !== $guestCart->id) {
            // Merge guest cart items into existing customer cart
            foreach ($guestCart->items as $guestItem) {
                $existingItem = $this->findCartItem(
                    $existingCustomerCart->id,
                    $guestItem->item_id,
                    $guestItem->selected_options,
                    $guestItem->item_type
                );

                if ($existingItem) {
                    // Update quantity
                    $this->updateItem($existingItem, [
                        'quantity' => $existingItem->quantity + $guestItem->quantity
                    ]);
                } else {
                    // Add new item
                    $itemData = $guestItem->toArray();
                    unset($itemData['id'], $itemData['cart_id'], $itemData['created_at'], $itemData['updated_at']);
                    $this->addItem($existingCustomerCart, $itemData);
                }
            }

            // Delete guest cart
            $this->delete($guestCart);

            return $existingCustomerCart;
        } else {
            // Transfer ownership of guest cart to customer (keep same session)
            $customer = Customer::find($userId);
            if ($customer) {
                $guestCart->owner()->associate($customer);
                $guestCart->save();
            } else {
                $guestCart->update([
                    'owner_id' => $userId,
                ]);
            }

            return $guestCart;
        }
    }

    /**
     * Get cart count for session (works for both guest and authenticated users)
     */
    public function getCartCount(?int $userId = null, ?string $sessionId = null, int $restaurantId = null): int
    {
        if (!$sessionId) {
            return 0;
        }

        $query = Cart::where('session_id', $sessionId);

        // If user is authenticated, also filter by customer
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('owner_type', Customer::class)
                    ->where('owner_id', $userId);
            });
        }

        if ($restaurantId) {
            $query->forRestaurant($restaurantId);
        }

        $cart = $query->first();

        return $cart ? $cart->total_items : 0;
    }

    /**
     * Transform legacy data to morph structure
     */
    private function transformToMorphData(array $data): array
    {
        $morphData = $data;

        // Support passing an owner model instance directly
        if (isset($data['owner']) && is_object($data['owner'])) {
            $owner = $data['owner'];
            $morphData['owner_type'] = method_exists($owner, 'getMorphClass') ? $owner->getMorphClass() : get_class($owner);
            $morphData['owner_id'] = $owner->id ?? null;
            unset($morphData['owner']);
        } else {
            // Transform user_id to owner morph (legacy fallback)
            if (isset($data['user_id']) && $data['user_id']) {
                $morphData['owner_type'] = Customer::class;
                $morphData['owner_id'] = $data['user_id'];
                unset($morphData['user_id']);
            } elseif (isset($data['customer_id']) && $data['customer_id']) {
                $morphData['owner_type'] = Customer::class;
                $morphData['owner_id'] = $data['customer_id'];
                unset($morphData['customer_id']);
            } elseif (!isset($morphData['owner_type']) && !isset($morphData['owner_id'])) {
                // For guest users, set null values for owner morph
                $morphData['owner_type'] = null;
                $morphData['owner_id'] = null;
            }
        }

        // Always ensure session_id is present (required for both guest and authenticated users)
        if (!isset($morphData['session_id']) || !$morphData['session_id']) {
            // Session ID is now optional - carts identified by owner_id instead
            $morphData['session_id'] = null;
        }

        // Support passing cartable model instance directly
        if (isset($data['cartable']) && is_object($data['cartable'])) {
            $cartable = $data['cartable'];
            $morphData['cartable_type'] = method_exists($cartable, 'getMorphClass') ? $cartable->getMorphClass() : get_class($cartable);
            $morphData['cartable_id'] = $cartable->id ?? null;
            unset($morphData['cartable']);
        } else {
            // Transform restaurant_id to cartable morph (legacy fallback)
            if (isset($data['restaurant_id'])) {
                $morphData['cartable_type'] = Restaurant::class;
                $morphData['cartable_id'] = $data['restaurant_id'];
                unset($morphData['restaurant_id']);
            }
        }

        return $morphData;
    }

    /**
     * Transform legacy item data to morph structure
     */
    private function transformItemToMorphData(array $itemData): array
    {
        $morphData = $itemData;

        // Support passing item model instance directly
        if (isset($itemData['item']) && is_object($itemData['item'])) {
            $item = $itemData['item'];
            $morphData['item_type'] = method_exists($item, 'getMorphClass') ? $item->getMorphClass() : get_class($item);
            $morphData['item_id'] = $item->id ?? null;
            unset($morphData['item']);
        } else {
            // Transform food_item_id to item morph (legacy fallback)
            if (isset($itemData['food_item_id'])) {
                $morphData['item_type'] = FoodItem::class;
                $morphData['item_id'] = $itemData['food_item_id'];
                unset($morphData['food_item_id']);
            }
        }

        return $morphData;
    }
}
