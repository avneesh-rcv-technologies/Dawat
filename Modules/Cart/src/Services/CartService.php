<?php

namespace Modules\Cart\Services;

use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Repositories\CartRepository;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Acl\Models\Customer;
use Modules\Restaurant\Models\Restaurant;
use Modules\Cart\Events\CartItemAdded;
use Modules\Cart\Events\CartItemUpdated;
use Modules\Cart\Events\CartItemRemoved;
use Modules\Cart\Events\CartCleared;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected CartRepository $cartRepository;

    public function __construct(
        CartRepository $cartRepository,
    ) {
        $this->cartRepository = $cartRepository;
    }

    public function validateRestaurant(int $restaurantId): array
    {
        $restaurant = \Modules\Restaurant\Models\Restaurant::find($restaurantId);

        if (!$restaurant) {
            return ['valid' => false, 'message' => 'Restaurant not found'];
        }

        if ($restaurant->status !== 'active') {
            return ['valid' => false, 'message' => 'Restaurant is closed'];
        }

        if (!$restaurant->is_accepting_orders) {
            return ['valid' => false, 'message' => 'Restaurant is not accepting orders'];
        }

        $now = \Carbon\Carbon::now();
        $dayOfWeek = strtolower($now->format('l'));

        $rawHours = $restaurant->hours?->hours ?? null;

        if (is_string($rawHours)) {
            $rawHours = json_decode($rawHours, true) ?? [];
        }

        if (!is_array($rawHours) || empty($rawHours[$dayOfWeek])) {
            return ['valid' => false, 'message' => 'Restaurant hours not configured'];
        }

        $segments = $rawHours[$dayOfWeek];

        if (isset($segments['open']) || isset($segments['from']) || isset($segments['to'])) {
            $segments = [$segments];
        }

        $isOpenNow = false;
        $nextCloseTime = null;

        foreach ($segments as $segment) {
            if (!is_array($segment)) {
                continue;
            }

            $isOpen = filter_var($segment['open'] ?? true, FILTER_VALIDATE_BOOLEAN);
            if (!$isOpen) {
                continue;
            }

            $from = trim((string) ($segment['from'] ?? ''));
            $to = trim((string) ($segment['to'] ?? ''));

            if ($from === '' || $to === '') {
                continue;
            }

            $openTime = \Carbon\Carbon::createFromFormat('H:i', $from);
            $closeTime = \Carbon\Carbon::createFromFormat('H:i', $to);

            if ($now->between($openTime, $closeTime)) {
                $isOpenNow = true;
                $nextCloseTime = $closeTime;
                break;
            }
        }

        if (!$isOpenNow) {
            return ['valid' => false, 'message' => 'Restaurant is closed today'];
        }

        if ($restaurant->order_time_limit && $nextCloseTime) {
            $minutesUntilClose = $now->diffInMinutes($nextCloseTime);
            if ($minutesUntilClose < $restaurant->order_time_limit) {
                return ['valid' => false, 'message' => "Orders must be placed {$restaurant->order_time_limit} minutes before closing"];
            }
        }

        return ['valid' => true, 'message' => 'Restaurant is valid'];
    }


    /**
     * Get or create cart for current session (works for both guest and authenticated users)
     */
    public function getOrCreateCart(int $restaurantId, ?int $userId = null, ?string $sessionId = null): Cart
    {
        // For authenticated users, find cart by owner_id (no session needed)
        if ($userId) {
            $cart = Cart::where('owner_type', Customer::class)
                ->where('owner_id', $userId)
                ->where('cartable_type', Restaurant::class)
                ->where('cartable_id', $restaurantId)
                ->first();

            if ($cart) {
                return $cart;
            }

            // Create new cart for authenticated user
            $cartData = [
                'session_id' => null,
                'owner_type' => Customer::class,
                'owner_id' => $userId,
                'cartable_type' => Restaurant::class,
                'cartable_id' => $restaurantId,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'is_tax_inclusive' => fn_is_tax_inclusive(),
            ];

            return $this->cartRepository->create($cartData);
        }

        // For guests without session, throw error
        throw new \Exception('User ID or Session ID is required for cart operations');
    }


    /**
     * Add item to cart
     */
    // public function addToCart(
    //     int $restaurantId,
    //     int $foodItemId,
    //     int $quantity = 1,
    //     ?array $selectedOptions = null,
    //     ?string $specialInstructions = null,
    //     ?int $userId = null,
    //     ?string $sessionId = null
    // ): array {
    //     return DB::transaction(function () use (
    //         $restaurantId,
    //         $foodItemId,
    //         $quantity,
    //         $selectedOptions,
    //         $specialInstructions,
    //         $userId,
    //         $sessionId
    //     ) {
    //         // Get or create cart
    //         $cart = $this->getOrCreateCart($restaurantId, $userId, $sessionId);

    //         // Get food item with tax calculation
    //         $foodItem = FoodItem::with(['categories', 'optionGroups.options'])
    //             ->findOrFail($foodItemId);

    //         // Validate food item availability
    //         $this->validateFoodItemAvailability($foodItem, $quantity);

    //         // Calculate pricing with tax and options (handles grouping quantity type)
    //         $pricing = $this->calculateItemPricing($foodItem, $selectedOptions, $quantity);

    //         // Check if item with same options already exists
    //         $existingItem = $this->cartRepository->findCartItem(
    //             $cart->id,
    //             $foodItemId,
    //             $selectedOptions
    //         );

    //         if ($existingItem) {
    //             // For grouping type, replace quantity instead of adding
    //             if ($foodItem->isGroupingQuantityType()) {
    //                 $newQuantity = $quantity;
    //             } else {
    //                 $newQuantity = $existingItem->quantity + $quantity;
    //             }

    //             $this->validateFoodItemAvailability($foodItem, $newQuantity);

    //             // Recalculate pricing for new quantity
    //             $newPricing = $this->calculateItemPricing($foodItem, $selectedOptions, $newQuantity);

    //             $cartItem = $this->cartRepository->updateItem($existingItem, [
    //                 'quantity' => $newQuantity,
    //                 'unit_price' => $newPricing['unit_price'],
    //                 'unit_display_price' => $newPricing['unit_display_price'],
    //                 'unit_tax_amount' => $newPricing['unit_tax_amount'],
    //                 'total_price' => $newPricing['total_price'],
    //                 'total_display_price' => $newPricing['total_display_price'],
    //                 'total_tax_amount' => $newPricing['total_tax_amount'],
    //                 'special_instructions' => $specialInstructions ?: $existingItem->special_instructions,
    //             ]);
    //         } else {
    //             // Add new item with clean naming (food_item_id will be transformed to item_id)
    //             $cartItem = $this->cartRepository->addItem($cart, [
    //                 'food_item_id' => $foodItemId, // Will be transformed to item_type/item_id in repository
    //                 'quantity' => $quantity,
    //                 'unit_price' => $pricing['unit_price'],
    //                 'unit_display_price' => $pricing['unit_display_price'],
    //                 'unit_tax_amount' => $pricing['unit_tax_amount'],
    //                 'total_price' => $pricing['total_price'],
    //                 'total_display_price' => $pricing['total_display_price'],
    //                 'total_tax_amount' => $pricing['total_tax_amount'],
    //                 'is_tax_inclusive' => $pricing['is_tax_inclusive'],
    //                 'tax_breakdown' => $pricing['tax_breakdown'],
    //                 'selected_options' => $selectedOptions,
    //                 'special_instructions' => $specialInstructions,
    //             ]);
    //         }

    //         // Dispatch event
    //         event(new CartItemAdded($cart->fresh(), $cartItem, $userId, $sessionId));

    //         return [
    //             'success' => true,
    //             'message' => 'Successfully add to cart item',
    //             'session_id' => $cart->session_id
    //         ];
    //     });
    // }



    public function addToCart(
        int $restaurantId,
        int $foodItemId,
        int $quantity = 1,
        ?array $selectedOptions = null,
        ?string $specialInstructions = null,
        ?int $userId = null,
        ?string $sessionId = null
    ): array {
        $sessionId = $this->getSessionId($sessionId);
        return DB::transaction(function () use (
            $restaurantId,
            $foodItemId,
            $quantity,
            $selectedOptions,
            $specialInstructions,
            $userId,
            $sessionId
        ) {
            // Get or create cart
            $cart = $this->getOrCreateCart($restaurantId, $userId, $sessionId);
            if ($cart->cartable_id != $restaurantId) {
                throw new \Exception(
                    'Cannot add items from different restaurants. ' .
                        'Please clear your cart first to switch restaurants.'
                );
            }


            $foodItem = FoodItem::with(['categories', 'optionGroups.options'])
                ->findOrFail($foodItemId);

            $itemBelongsToRestaurant = $foodItem->categories()
                ->where('restaurant_id', $restaurantId)
                ->exists();

            if (!$itemBelongsToRestaurant) {
                throw new \Exception(
                    'This item does not belong to the selected restaurant'
                );
            }

            // Validate food item availability
            $this->validateFoodItemAvailability($foodItem, $quantity);

            // Calculate pricing with tax and options (handles grouping quantity type)
            $pricing = $this->calculateItemPricing($foodItem, $selectedOptions, $quantity);

            // Check if item with same options already exists
            $existingItem = $this->cartRepository->findCartItem(
                $cart->id,
                $foodItemId,
                $selectedOptions
            );

            if ($existingItem) {
                // For grouping type, replace quantity instead of adding
                if ($foodItem->isGroupingQuantityType()) {
                    $newQuantity = $quantity;
                } else {
                    $newQuantity = $existingItem->quantity + $quantity;
                }

                $this->validateFoodItemAvailability($foodItem, $newQuantity);

                // Recalculate pricing for new quantity
                $newPricing = $this->calculateItemPricing($foodItem, $selectedOptions, $newQuantity);

                $cartItem = $this->cartRepository->updateItem($existingItem, [
                    'quantity' => $newQuantity,
                    'unit_price' => $newPricing['unit_price'],
                    'unit_display_price' => $newPricing['unit_display_price'],
                    'unit_tax_amount' => $newPricing['unit_tax_amount'],
                    'total_price' => $newPricing['total_price'],
                    'total_display_price' => $newPricing['total_display_price'],
                    'total_tax_amount' => $newPricing['total_tax_amount'],
                    'special_instructions' => $specialInstructions ?: $existingItem->special_instructions,
                ]);
            } else {
                // Add new item with clean naming (food_item_id will be transformed to item_id)
                $cartItem = $this->cartRepository->addItem($cart, [
                    'food_item_id' => $foodItemId,
                    'quantity' => $quantity,
                    'unit_price' => $pricing['unit_price'],
                    'unit_display_price' => $pricing['unit_display_price'],
                    'unit_tax_amount' => $pricing['unit_tax_amount'],
                    'total_price' => $pricing['total_price'],
                    'total_display_price' => $pricing['total_display_price'],
                    'total_tax_amount' => $pricing['total_tax_amount'],
                    'is_tax_inclusive' => $pricing['is_tax_inclusive'],
                    'tax_breakdown' => $pricing['tax_breakdown'],
                    'selected_options' => $selectedOptions,
                    'special_instructions' => $specialInstructions,
                ]);
            }

            // Dispatch event
            event(new CartItemAdded($cart->fresh(), $cartItem, $userId, $sessionId));

            return [
                'success' => true,
                'message' => 'Successfully add to cart item',
                'session_id' => $cart->session_id
            ];
        });
    }


    /**
     * Update cart item quantity
     */
    public function updateCartItemQuantity(
        int $cartItemId,
        int $quantity,
        ?int $userId = null,
        ?string $sessionId = null
    ): array {
        $sessionId = $this->getSessionId($sessionId);

        return DB::transaction(function () use ($cartItemId, $quantity, $userId, $sessionId) {

            // Find cart item with proper error handling
            $cartItem = CartItem::with(['cart', 'item'])->find($cartItemId);

            if (!$cartItem) {
                return [
                    'success' => false,
                    'errors' => 'Cart item not found',
                    'message' => 'The specified cart item does not exist'
                ];
            }

            // Validate cart ownership
            try {
                $this->validateCartOwnership($cartItem->cart, $userId, $sessionId);
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'errors' => $e->getMessage(),
                    'message' => 'Unauthorized access to cart item'
                ];
            }

            if ($quantity <= 0) {
                // Remove item if quantity is 0 or negative
                return $this->removeFromCart($cartItemId, $userId, $sessionId);
            }

            // Validate food item availability (get actual food item from morph relation)
            $foodItem = $cartItem->item;
            try {
                if ($foodItem) {
                    $this->validateFoodItemAvailability($foodItem, $quantity);
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'errors' => $e->getMessage(),
                    'message' => 'Food item validation failed'
                ];
            }

            // Store old quantity for event
            $oldQuantity = $cartItem->quantity;

            // Recalculate pricing for grouping type items
            if ($foodItem && $foodItem->isGroupingQuantityType()) {
                $newPricing = $this->calculateItemPricing($foodItem, $cartItem->selected_options, $quantity);

                $updatedItem = $this->cartRepository->updateItem($cartItem, [
                    'quantity' => $quantity,
                    'unit_price' => $newPricing['unit_price'],
                    'unit_display_price' => $newPricing['unit_display_price'],
                    'unit_tax_amount' => $newPricing['unit_tax_amount'],
                    'total_price' => $newPricing['total_price'],
                    'total_display_price' => $newPricing['total_display_price'],
                    'total_tax_amount' => $newPricing['total_tax_amount'],
                ]);
            } else {
                // For manual items, just update quantity (totals recalculated in repository)
                $updatedItem = $this->cartRepository->updateItem($cartItem, [
                    'quantity' => $quantity
                ]);
            }

            // Dispatch event
            event(new CartItemUpdated($cartItem->cart->fresh(), $updatedItem, $oldQuantity, $quantity, $userId, $sessionId));

            return [
                'success' => true,
                'message' => 'Cart item updated successfully',
                'session_id' => $cartItem->cart->session_id
            ];
        });
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(
        int $cartItemId,
        ?int $userId = null,
        ?string $sessionId = null
    ): array {
        $sessionId = $this->getSessionId($sessionId);

        return DB::transaction(function () use ($cartItemId, $userId, $sessionId) {
            // First try to find by cart_item_id
            $cartItem = CartItem::with(['cart', 'item'])->find($cartItemId);

            // If not found, maybe it's a food_item_id, search by item_id
            if (!$cartItem) {
                $cartItem = CartItem::with(['cart', 'item'])
                    ->where('item_id', $cartItemId)
                    ->first();
                // dd($cartItem);

            }

            if (!$cartItem) {
                return [
                    'success' => false,
                    'errors' => 'Cart item not found',
                    'message' => 'The specified cart item does not exist'
                ];
            }

            // Validate cart ownership
            try {
                $this->validateCartOwnership($cartItem->cart, $userId, $sessionId);
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'errors' => $e->getMessage(),
                    'message' => 'Unauthorized access to cart item'
                ];
            }

            $cart = $cartItem->cart;
            $cartSessionId = $cart->session_id;

            // Store item data for event before deletion
            $removedItemData = $this->formatCartItem($cartItem);

            $removeResult = $this->cartRepository->removeItem($cartItem);

            // Dispatch event - use original cart data if cart was deleted
            if ($removeResult['cart_deleted']) {
                // Cart was deleted, use the original cart data for the event
                event(new CartItemRemoved($cart, $removedItemData, $userId, $sessionId));
            } else {
                // Cart still exists, use fresh cart data
                event(new CartItemRemoved($removeResult['cart'], $removedItemData, $userId, $sessionId));
            }

            return [
                'success' => true,
                'message' => 'Item removed from cart successfully',
                'session_id' => $cartSessionId
            ];
        });
    }

    /**
     * Clear entire cart
     */
    public function clearCart(
        int $restaurantId,
        ?int $userId = null,
        ?string $sessionId = null
    ): array {
        if (!$userId) {
            return [
                'success' => false,
                'cart' => null,
                'message' => 'User ID is required'
            ];
        }

        return DB::transaction(function () use ($restaurantId, $userId) {
            // Find cart by user ID and restaurant
            $cart = Cart::where('owner_type', Customer::class)
                ->where('owner_id', $userId)
                ->where('cartable_type', Restaurant::class)
                ->where('cartable_id', $restaurantId)
                ->first();

            if (!$cart) {
                return [
                    'success' => true,
                    'cart' => null,
                    'message' => 'Cart is already empty'
                ];
            }

            // Store items data for event before clearing
            $clearedItemsData = $cart->items->map(fn($item) => $this->formatCartItem($item))->toArray();

            $clearResult = $this->cartRepository->clearCart($cart);

            // Dispatch event
            event(new CartCleared($cart, $clearedItemsData, $userId, null));

            return [
                'success' => true,
                'cart' => null,
                'message' => 'Cart cleared successfully'
            ];
        });
    }

    /**
     * Get cart details (always uses session_id)
     */
    // public function getCart(
    //     int $restaurantId,
    //     ?int $userId = null,
    //     ?string $sessionId = null
    // ): array {
    //     $sessionId = $sessionId ?: Session::getId();

    //     if (!$sessionId) {
    //         return [
    //             'success' => false,
    //             'cart' => null,
    //             'message' => 'Session ID is required'
    //         ];
    //     }

    //     // Always find by session (works for both guest and authenticated users)
    //     $cart = $this->cartRepository->findBySession($sessionId, $restaurantId);

    //     if (!$cart) {
    //         return [
    //             'success' => true,
    //             'cart' => null,
    //             'message' => 'Cart is empty',
    //             'session_id' => $sessionId
    //         ];
    //     }

    //     // Verify ownership if user is authenticated
    //     if ($userId) {
    //         if (!$cart->owner_type || !$cart->owner_id) {
    //             // Cart exists but has no customer ownership, associate current customer
    //             $customer = Customer::find($userId);
    //             if ($customer) {
    //                 $cart->owner()->associate($customer);
    //                 $cart->save();
    //             } else {
    //                 $cart->update([
    //                     'owner_id' => $userId,
    //                 ]);
    //             }
    //         } elseif ($cart->owner_id !== $userId) {
    //             // Cart belongs to different customer
    //             return [
    //                 'success' => true,
    //                 'cart' => null,
    //                 'message' => 'Cart is empty',
    //                 'session_id' => $sessionId
    //             ];
    //         }
    //     }

    //     // Get fresh cart with items and update totals
    //     $cartWithItems = $this->cartRepository->getCartWithItems($cart);
    //     $cartWithItems->updateTotals();

    //     // Refresh the cart to get updated totals from database
    //     $cartWithItems = $cartWithItems->fresh(['items.item']);

    //     return [
    //         'success' => true,
    //         'cart' => $this->formatCart($cartWithItems),
    //         'message' => 'Cart retrieved successfully',
    //         'session_id' => $cartWithItems->session_id
    //     ];
    // }

    public function getCart(
        ?int $userId = null,
        ?string $sessionId = null
    ): array {
        if (!$userId) {
            return [
                'success' => false,
                'cart' => null,
                'message' => 'User ID is required'
            ];
        }

        // Find cart by user ID (no session needed)
        $cart = Cart::where('owner_type', Customer::class)
            ->where('owner_id', $userId)
            ->with(['items.item'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return [
                'success' => true,
                'cart' => null,
                'message' => 'Cart is empty'
            ];
        }

        // Get fresh cart with items and update totals
        $cartWithItems = $this->cartRepository->getCartWithItems($cart);
        $cartWithItems->updateTotals();

        // Refresh the cart to get updated totals from database
        $cartWithItems = $cartWithItems->fresh(['items.item']);

        return [
            'success' => true,
            'cart' => $this->formatCart($cartWithItems),
            'message' => 'Cart retrieved successfully'
        ];
    }


    /**
     * Transfer guest cart to user account
     */
    public function transferGuestCart(
        int $userId,
        int $restaurantId,
        ?string $sessionId = null
    ): array {
        // $sessionId = $sessionId ?: Session::getId();
        $sessionId = $this->getSessionId($sessionId);

        $cart = $this->cartRepository->transferGuestCartToUser($sessionId, $userId, $restaurantId);

        return [
            'success' => true,
            'cart' => $cart ? $this->formatCart($this->cartRepository->getCartWithItems($cart)) : null,
            'message' => $cart ? 'Cart transferred successfully' : 'No cart to transfer'
        ];
    }

    /**
     * Get cart count
     */
    public function getCartCount(
        int $restaurantId,
        ?int $userId = null,
        ?string $sessionId = null
    ): int {
        if (!$userId) {
            return 0;
        }

        $cart = Cart::where('owner_type', Customer::class)
            ->where('owner_id', $userId)
            ->where('cartable_type', Restaurant::class)
            ->where('cartable_id', $restaurantId)
            ->with('items')
            ->first();

        return $cart ? $cart->items->sum('quantity') : 0;
    }

    /* ================= Private Helper Methods ================= */

    /**
     * Validate food item availability
     */
    private function validateFoodItemAvailability(FoodItem $foodItem, int $quantity): void
    {
        if (!$foodItem->is_active) {
            throw new \Exception('Food item is not active');
        }

        if (!$foodItem->is_available) {
            throw new \Exception('Food item is not available');
        }

        if (!$foodItem->in_stock) {
            throw new \Exception('Food item is out of stock');
        }

        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero');
        }

        $quantityType = $foodItem->quantity_type ?? 'manual';
        $stockLimit = $foodItem->quantity;

        if ($stockLimit === null || $stockLimit === '') {
            return;
        }

        switch ($quantityType) {
            case 'manual':
            case 'piece':
                if ($quantity > (int) $stockLimit) {
                    throw new \Exception("Only {$stockLimit} items available in stock");
                }
                break;

            case 'grouping':
                if ($quantity < (int) $stockLimit) {
                    throw new \Exception("Minimum {$stockLimit} guests required");
                }
                break;
        }
    }

    /**
     * Calculate item pricing with tax and options
     * For grouping type: calculates per-guest pricing
     */
    private function calculateItemPricing(FoodItem $foodItem, ?array $selectedOptions = null, int $guestCount = 1): array
    {
        // Get base tax calculation
        $taxCalculation = $foodItem->getTaxCalculation();

        // Calculate options price
        $optionsPrice = 0;
        if ($selectedOptions) {
            foreach ($selectedOptions as $option) {
                $optionsPrice += $option['price'] ?? 0;
            }
        }

        // For grouping type items, calculate per-guest pricing
        if ($foodItem->isGroupingQuantityType() && $foodItem->quantity > 0) {
            // Base price is for X guests (e.g., ₹400 for 40 guests)
            // Calculate price per guest
            $pricePerGuest = $foodItem->sale_price / $foodItem->quantity;
            $displayPricePerGuest = $taxCalculation['display_price'] / $foodItem->quantity;

            // Calculate total for actual guest count
            $basePrice = $pricePerGuest * $guestCount;
            $displayPrice = $displayPricePerGuest * $guestCount;

            // Add options price (options are per order, not per guest)
            $basePrice += $optionsPrice;
            $displayPrice += $optionsPrice;

            $taxAmount = $displayPrice - $basePrice;

            return [
                'unit_price' => round($pricePerGuest, 2), // Price per guest
                'unit_display_price' => round($displayPricePerGuest, 2),
                'unit_tax_amount' => round(($displayPricePerGuest - $pricePerGuest), 2),
                'total_price' => round($basePrice, 2),
                'total_display_price' => round($displayPrice, 2),
                'total_tax_amount' => round($taxAmount, 2),
                'is_tax_inclusive' => $taxCalculation['is_tax_inclusive'],
                'tax_breakdown' => $taxCalculation['tax_breakdown'] ?? null,
                'quantity_type' => 'grouping',
                'base_guest_count' => $foodItem->quantity,
                'price_per_guest' => round($pricePerGuest, 2),
            ];
        }

        // For manual/regular items, use standard pricing
        $basePrice = $foodItem->sale_price + $optionsPrice;
        $displayPrice = $taxCalculation['display_price'] + $optionsPrice;
        $taxAmount = $displayPrice - $basePrice;

        return [
            'unit_price' => $basePrice,
            'unit_display_price' => $displayPrice,
            'unit_tax_amount' => $taxAmount,
            'total_price' => $basePrice * $guestCount,
            'total_display_price' => $displayPrice * $guestCount,
            'total_tax_amount' => $taxAmount * $guestCount,
            'is_tax_inclusive' => $taxCalculation['is_tax_inclusive'],
            'tax_breakdown' => $taxCalculation['tax_breakdown'] ?? null,
            'quantity_type' => 'manual',
        ];
    }

    /**
     * Validate cart ownership (user-based verification)
     */
    private function validateCartOwnership(Cart $cart, ?int $userId = null, ?string $sessionId = null): void
    {
        // If user is authenticated, verify customer ownership
        if ($userId) {
            if (!$cart->owner_type || $cart->owner_id !== $userId) {
                throw new \Exception('Unauthorized access to cart - customer mismatch');
            }
        }
    }

    /**
     * Format cart for API response
     */
    private function formatCart(Cart $cart): array
    {

        return [
            'id' => $cart->id,
            'restaurant_id' => $cart->cartable_id, // For backward compatibility
            'currency' => fn_format_default_currency(),
            'subtotal' => $cart->subtotal,
            'tax_amount' => $cart->tax_amount,
            'discount_amount' => $cart->discount_amount,
            'total' => $cart->total,
            'formatted_subtotal' => $cart->formatted_subtotal,
            'formatted_tax_amount' => $cart->formatted_tax_amount,
            'formatted_discount_amount' => $cart->formatted_discount_amount,
            'formatted_total' => $cart->formatted_total,
            'total_items' => $cart->total_items,
            'coupon_code' => $cart->coupon_code,
            'discount_breakdown' => $cart->discount_breakdown,
            'has_discount' => $cart->hasDiscount(),
            'items' => $cart->items->map(fn($item) => $this->formatCartItem($item))->toArray(),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ];
    }

    /**
     * Format cart item for API response
     */
    private function formatCartItem(CartItem $cartItem): array
    {
        // Get the actual item (food item) from morph relation
        $item = $cartItem->item;

        // Use the same object type the file manager stores for food images.
        $itemImage = $item ? fn_get_image('food_image', $item->id) : null;

        $formattedItem = [
            'id' => $cartItem->id,
            'item_id' => $cartItem->item_id, // Clean naming: item_id instead of food_item_id
            'item' => $item ? [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'image' => $itemImage['url'] ?? ($item->image['url'] ?? null),
                'quantity_type' => $item->quantity_type,
                'base_quantity' => $item->quantity,
                'categories' => $item->categories ? $item->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ])->toArray() : [],
            ] : null,
            'quantity' => $cartItem->quantity,
            'unit_price' => $cartItem->unit_price,
            'unit_display_price' => $cartItem->unit_display_price,
            'unit_tax_amount' => $cartItem->unit_tax_amount,
            'total_price' => $cartItem->total_price,
            'total_display_price' => $cartItem->total_display_price,
            'total_tax_amount' => $cartItem->total_tax_amount,
            'is_tax_inclusive' => $cartItem->is_tax_inclusive,
            'selected_options' => $cartItem->selected_options,
            'options_total_price' => $cartItem->options_total_price,
            'selected_options_names' => $cartItem->selected_options_names,
            'special_instructions' => $cartItem->special_instructions,
            'display_name' => $cartItem->display_name,
            'formatted_unit_price' => $cartItem->formatted_unit_price,
            'formatted_unit_display_price' => $cartItem->formatted_unit_display_price,
            'formatted_total_price' => $cartItem->formatted_total_price,
            'formatted_total_display_price' => $cartItem->formatted_total_display_price,
            'created_at' => $cartItem->created_at,
            'updated_at' => $cartItem->updated_at,
        ];

        // Add grouping-specific information
        if ($item && $item->isGroupingQuantityType() && $item->quantity > 0) {
            $pricePerGuest = $item->sale_price / $item->quantity;
            $formattedItem['grouping_info'] = [
                'is_grouping_type' => true,
                'base_price' => $item->sale_price,
                'base_guest_count' => $item->quantity,
                'price_per_guest' => round($pricePerGuest, 2),
                'current_guest_count' => $cartItem->quantity,
                'formatted_price_per_guest' => fn_format_default_currency() . number_format($pricePerGuest, 2),
                'description' => "₹{$item->sale_price} for {$item->quantity} guests (₹" . number_format($pricePerGuest, 2) . " per guest)"
            ];
        } else {
            $formattedItem['grouping_info'] = [
                'is_grouping_type' => false
            ];
        }

        return $formattedItem;
    }

    // get session id
    private function getSessionId(?string $sessionId = null): ?string
    {
        // If provided, use it
        if ($sessionId && !empty($sessionId)) {
            return $sessionId;
        }

        // For authenticated users with token, session is optional
        // Return null to let cart be identified by owner_id instead
        return null;
    }
}
