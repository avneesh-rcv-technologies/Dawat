<?php

namespace Modules\Cart\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Customer;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Restaurant\Models\Restaurant;

class CartV2Service
{
    public function __construct(
        private ServingOptionCartPricing $pricing,
        private CartService $legacy,
        private CartCacheService $cache,
    ) {}

    private function scopeOwner(Builder $query, ?Customer $customer, ?string $sessionId): Builder
    {
        if ($customer !== null) {
            return $query->where('owner_type', $customer->getMorphClass())->where('owner_id', $customer->id);
        }
        if ($sessionId === null || ! preg_match('/\A[a-f0-9]{64}\z/', $sessionId)) {
            throw ValidationException::withMessages(['session_id' => 'Please send a valid cart session ID in the X-Session-ID header, or sign in.']);
        }

        return $query->whereNull('owner_type')->whereNull('owner_id')->where('session_id', $sessionId);
    }

    private function ownedCarts(?Customer $customer, int $restaurantId, ?string $sessionId): Builder
    {
        return $this->scopeOwner(Cart::query(), $customer, $sessionId)
            ->where('cartable_type', (new Restaurant)->getMorphClass())->where('cartable_id', $restaurantId);
    }

    private function lockOwner(?Customer $customer, int $restaurantId): void
    {
        if ($customer !== null) {
            Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
        } else {
            // Serialize guest mutations, including the first cart creation, without a new schema.
            Restaurant::whereKey($restaurantId)->lockForUpdate()->first()
                ?? abort(404, 'This restaurant is no longer available. Please choose another restaurant.');
        }
    }

    private function forgetCustomerCart(?Customer $customer, int $restaurantId): void
    {
        if ($customer !== null) {
            DB::afterCommit(fn () => $this->cache->forgetCart($customer->id, $restaurantId));
        }
        // Guest v2 carts are read directly and do not use the customer cart cache.
    }

    public function get(?Customer $customer, int $restaurantId, ?string $sessionId = null): ?Cart
    {
        return DB::transaction(function () use ($customer, $restaurantId, $sessionId) {
            $this->lockOwner($customer, $restaurantId);
            $cart = $this->ownedCarts($customer, $restaurantId, $sessionId)->with(['items.item', 'items.servingOption'])->lockForUpdate()->first();
            if ($cart) $cart->updateTotals();
            return $cart;
        });
    }

    public function getItem(?Customer $customer, int $lineId, ?string $sessionId = null): CartItem
    {
        return CartItem::whereHas('cart', fn (Builder $query) => $this->scopeOwner($query, $customer, $sessionId)
            ->where('cartable_type', (new Restaurant)->getMorphClass()))
            ->with(['item', 'servingOption'])->find($lineId)
            ?? abort(404, 'This item is no longer in your cart. Please refresh your cart.');
    }

    public function add(?Customer $customer, array $data, ?string $sessionId = null): Cart
    {
        $add = fn () => DB::transaction(function () use ($customer, $data, $sessionId) {
            $this->scopeOwner(Cart::query(), $customer, $sessionId);
            $this->lockOwner($customer, (int) $data['restaurant_id']);
            $otherRestaurantHasItems = $this->scopeOwner(Cart::query(), $customer, $sessionId)
                ->where('cartable_type', (new Restaurant)->getMorphClass())
                ->where('cartable_id', '!=', (int) $data['restaurant_id'])
                ->whereHas('items')->exists();
            if ($otherRestaurantHasItems) {
                throw ValidationException::withMessages([
                    'restaurant_id' => 'Your cart already contains items from another restaurant. Please clear your cart before adding items from this restaurant.',
                ]);
            }
            [$restaurant, $food, $option] = $this->resolveSelection(
                (int) $data['restaurant_id'], (int) $data['item_id'], (int) $data['serving_option_id']
            );
            $attributes = $this->pricing->calculate($food, $option, (int) $data['quantity'], $data['guest_count'] ?? null);
            $cart = $this->ownedCarts($customer, $restaurant->id, $sessionId)->lockForUpdate()->first();
            if (! $cart) {
                $cart = Cart::create([
                    'owner_type' => $customer?->getMorphClass(), 'owner_id' => $customer?->id,
                    'session_id' => $customer === null ? $sessionId : null,
                    'cartable_type' => $restaurant->getMorphClass(), 'cartable_id' => $restaurant->id,
                    'is_tax_inclusive' => false,
                ]);
            }
            $note = $data['note'] ?? null;
            if (array_key_exists('event_guest_count', $data)) {
                $cart->event_guest_count = $data['event_guest_count'];
            }
            $line = $cart->items()->where('item_type', $food->getMorphClass())->where('item_id', $food->id)
                ->where('serving_option_id', $option->id)->where('guest_count', $attributes['guest_count'])
                ->where('note', $note)->whereNull('selected_options')->first();
            if ($line) {
                // A per-person add selects the guests once; retrying must not multiply them.
                $quantity = $option->serve_type === 'per_person' ? 1 : $line->quantity + $data['quantity'];
                if ($quantity > config('cart.max_quantity_per_item', 100)) {
                    $limit = config('cart.max_quantity_per_item', 100);
                    throw ValidationException::withMessages(['quantity' => "You already have {$line->quantity} of this selection in your cart. You can order up to $limit in total. Please reduce the quantity."]);
                }
                $attributes = $this->pricing->calculate($food, $option, $quantity, $data['guest_count'] ?? null);
                $line->update($attributes);
            } else {
                if ($cart->items()->count() >= config('cart.max_items_per_cart', 50)) {
                    $limit = config('cart.max_items_per_cart', 50);
                    throw ValidationException::withMessages(['item_id' => "Your cart can hold up to $limit different selections. Please remove an item before adding another."]);
                }
                $cart->items()->create([
                    ...$attributes, 'item_type' => $food->getMorphClass(), 'item_id' => $food->id,
                    'note' => $note, 'special_instructions' => $note,
                ]);
            }
            $this->updateTotals($cart);
            $this->forgetCustomerCart($customer, $restaurant->id);

            return $cart->load(['items.item', 'items.servingOption']);
        }, 3);

        if ($customer !== null) {
            return $add();
        }
        $this->scopeOwner(Cart::query(), null, $sessionId);
        try {
            // Restaurant row locks alone cannot serialize two different restaurants for one guest.
            return Cache::lock('cart.v2.guest-add.'.hash('sha256', $sessionId), 60)->block(5, $add);
        } catch (LockTimeoutException $e) {
            throw ValidationException::withMessages(['cart' => 'Your cart is being updated. Please try again.']);
        }
    }

    public function clear(?Customer $customer, int $restaurantId, ?string $sessionId = null): void
    {
        DB::transaction(function () use ($customer, $restaurantId, $sessionId) {
            $query = $this->ownedCarts($customer, $restaurantId, $sessionId);
            $this->lockOwner($customer, $restaurantId);
            foreach ($query->lockForUpdate()->get() as $cart) {
                $cart->items()->delete();
                $cart->delete();
            }
            $this->forgetCustomerCart($customer, $restaurantId);
        }, 3);
    }

    public function remove(?Customer $customer, int $lineId, ?string $sessionId = null): ?Cart
    {
        return DB::transaction(function () use ($customer, $lineId, $sessionId) {
            [$cart, $line] = $this->lockedLine($customer, $lineId, $sessionId);
            $line->delete();
            $restaurantId = $cart->cartable_id;
            $this->forgetCustomerCart($customer, $restaurantId);
            if (! $cart->items()->exists()) {
                $cart->delete();

                return null;
            }
            $this->updateTotals($cart);

            return $cart->load(['items.item', 'items.servingOption']);
        }, 3);
    }

    public function updateItem(?Customer $customer, int $lineId, array $data, ?string $sessionId = null): Cart
    {
        return DB::transaction(function () use ($customer, $lineId, $data, $sessionId) {
            [$cart, $line] = $this->lockedLine($customer, $lineId, $sessionId);
            if ($line->item_type !== (new FoodItem)->getMorphClass()
                || $cart->cartable_type !== (new Restaurant)->getMorphClass()) {
                throw ValidationException::withMessages(['item_id' => 'This cart item cannot be updated here. Please remove it and add your selection again.']);
            }
            $optionId = $data['serving_option_id'] ?? $line->serving_option_id;
            if (! $optionId) {
                throw ValidationException::withMessages(['serving_option_id' => 'Please choose a serving option to update this item.']);
            }
            [$restaurant, $food, $option] = $this->resolveSelection($cart->cartable_id, $line->item_id, $optionId);
            $perPerson = $option->serve_type === 'per_person';
            $quantity = array_key_exists('quantity', $data) ? (int) $data['quantity'] : ($perPerson ? 1 : $line->quantity);
            if ($quantity < 1 || $quantity > config('cart.max_quantity_per_item', 100)) {
                $limit = config('cart.max_quantity_per_item', 100);
                throw ValidationException::withMessages(['quantity' => "Please choose a quantity between 1 and $limit, or remove this item from your cart."]);
            }
            $guests = array_key_exists('guest_count', $data) ? $data['guest_count']
                : ($perPerson && $line->serve_type === 'per_person' ? $line->guest_count : null);
            $attributes = $this->pricing->calculate($food, $option, $quantity, $guests);
            $note = array_key_exists('note', $data) ? $data['note'] : ($line->note ?? $line->special_instructions);

            $duplicate = $cart->items()->where('id', '!=', $line->id)
                ->where('item_type', $food->getMorphClass())->where('item_id', $food->id)
                ->where('serving_option_id', $option->id)->where('guest_count', $attributes['guest_count'])
                ->where('note', $note)->whereNull('selected_options')->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['serving_option_id' => 'This selection is already in your cart. Please update that cart item instead.']);
            }
            if (! empty($line->selected_options)) {
                throw ValidationException::withMessages(['selected_options' => 'This item has add-ons that cannot be updated here. Please remove it and add a serving selection again.']);
            }

            $line->update([...$attributes, 'note' => $note, 'special_instructions' => $note, 'selected_options' => null]);
            if (array_key_exists('event_guest_count', $data)) {
                $cart->event_guest_count = $data['event_guest_count'];
            }
            $this->updateTotals($cart);
            $this->forgetCustomerCart($customer, $restaurant->id);

            return $cart->load(['items.item', 'items.servingOption']);
        }, 3);
    }

    private function lockedLine(?Customer $customer, int $lineId, ?string $sessionId): array
    {
        // Find only an owned line before taking a guest restaurant lock.
        $line = $this->getItem($customer, $lineId, $sessionId);
        $this->lockOwner($customer, $line->cart->cartable_id);
        $cart = $this->scopeOwner(Cart::query(), $customer, $sessionId)
            ->whereKey($line->cart_id)->lockForUpdate()->first()
            ?? abort(404, 'This item is no longer in your cart. Please refresh your cart.');
        $line = $cart->items()->whereKey($lineId)->lockForUpdate()->first()
            ?? abort(404, 'This item is no longer in your cart. Please refresh your cart.');

        return [$cart, $line];
    }

    private function resolveSelection(int $restaurantId, int $itemId, int $optionId): array
    {
        $restaurant = Restaurant::find($restaurantId)
            ?? abort(404, 'This restaurant is no longer available. Please choose another restaurant.');
        $validation = $this->legacy->validateRestaurant($restaurant->id);
        if (! $validation['valid']) {
            $message = match ($validation['message']) {
                'Restaurant not found' => 'This restaurant is no longer available. Please choose another restaurant.',
                'Restaurant is closed', 'Restaurant is closed today' => 'This restaurant is closed right now. Please try again during opening hours.',
                'Restaurant is not accepting orders', 'Restaurant hours not configured' => 'This restaurant is not accepting orders right now. Please try again later.',
                default => $validation['message'],
            };
            throw ValidationException::withMessages(['restaurant_id' => $message]);
        }
        $food = FoodItem::where('restaurant_id', $restaurant->id)->lockForUpdate()->find($itemId)
            ?? abort(404, 'This food item is not available from the selected restaurant. Please choose another item.');
        $option = $food->servingOptions()->lockForUpdate()->find($optionId)
            ?? abort(404, 'This serving option is no longer available for this item. Please refresh the menu and choose another option.');
        if (! $food->is_active || ! $food->is_available) {
            throw ValidationException::withMessages(['item_id' => 'This food item is currently unavailable. Please choose another item.']);
        }
        if (! $food->in_stock) {
            throw ValidationException::withMessages(['item_id' => 'This food item is out of stock. Please choose another item.']);
        }
        if (! $option->is_available) {
            throw ValidationException::withMessages(['serving_option_id' => 'This serving option is currently unavailable. Please choose another option.']);
        }

        return [$restaurant, $food, $option];
    }

    private function updateTotals(Cart $cart): void
    {
        $cart->load('items');
        // Coupons must be revalidated after the cart contents change.
        $cart->fill([
            'coupon_code' => null, 'discount_type' => null, 'discount_id' => null,
            'discount_amount' => 0, 'discount_percentage' => null, 'discount_breakdown' => null,
        ]);
        if ((float) $cart->items->sum('total_display_price') > 99999999.99) {
            throw ValidationException::withMessages(['quantity' => 'Your cart total is too large. Please reduce the quantity or remove an item.']);
        }
        $cart->updateTotals();
    }
}
