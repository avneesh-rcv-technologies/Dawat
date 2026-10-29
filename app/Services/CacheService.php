<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{

    const TTL_SHORT  = 300;   //  5 min
    const TTL_MEDIUM = 900;   // 15 min
    const TTL_LONG   = 1800;  // 30 min
    const TTL_DAY    = 3600;  // 60 min

    // =========================================================
    //  Cache Keys 
    // =========================================================
    public static function restaurantListKey(): string
    {
        return 'restaurants.list';
    }
    public static function restaurantKey(int $id): string
    {
        return "restaurant.{$id}";
    }
    public static function restaurantValidKey(int $id): string
    {
        return "restaurant.{$id}.valid";
    }
    public static function foodItemKey(int $id): string
    {
        return "food_item.{$id}.options";
    }
    public static function paymentMethodsKey(): string
    {
        return 'checkout.payment_methods';
    }
    public static function shippingMethodsKey(): string
    {
        return 'checkout.shipping_methods';
    }
    public static function orderStatusesKey(string $type): string
    {
        return "order_statuses.{$type}";
    }
    public static function orderKey(int $orderId, int $customerId): string
    {
        return "order.{$orderId}.customer.{$customerId}";
    }

    // =========================================================
    //  Restaurant — List
    // =========================================================

    public static function rememberRestaurantList(callable $callback)
    {
        return Cache::remember(self::restaurantListKey(), self::TTL_MEDIUM, $callback);
    }

    /**
     * Immediately deletes the list cache.
     */
    public static function forgetRestaurantList(): void
    {
        Cache::forget(self::restaurantListKey());
    }

    // =========================================================
    //  Restaurant — Single Record
    // =========================================================

    /** Cache a single restaurant with its relations  */
    public static function rememberRestaurant(int $id, callable $callback)
    {
        return Cache::remember(self::restaurantKey($id), self::TTL_LONG, $callback);
    }

    /** Cache restaurant open/close validation */
    public static function rememberRestaurantValid(int $id, callable $callback)
    {
        return Cache::remember(self::restaurantValidKey($id), self::TTL_SHORT, $callback);
    }

    /**
     * delete restaurant from cache.
     */
    public static function forgetRestaurant(int $id): void
    {
        Cache::forget(self::restaurantKey($id));
        Cache::forget(self::restaurantValidKey($id));
    }

    // =========================================================
    //  Food Items
    // =========================================================

    /** Cache food item with option groups */
    public static function rememberFoodItem(int $id, callable $callback)
    {
        return Cache::remember(self::foodItemKey($id), self::TTL_MEDIUM, $callback);
    }

    /** Immediately deletes this food item from cache. */
    public static function forgetFoodItem(int $id): void
    {
        Cache::forget(self::foodItemKey($id));
    }

    // =========================================================
    //  Checkout
    // =========================================================

    /** Cache enabled payment methods */
    public static function rememberPaymentMethods(callable $callback)
    {
        return Cache::remember(self::paymentMethodsKey(), self::TTL_LONG, $callback);
    }

    /** Cache shipping methods (60 min) */
    public static function rememberShippingMethods(callable $callback)
    {
        return Cache::remember(self::shippingMethodsKey(), self::TTL_DAY, $callback);
    }

    /** Call when payment or shipping settings change in admin */
    public static function forgetCheckout(): void
    {
        Cache::forget(self::paymentMethodsKey());
        Cache::forget(self::shippingMethodsKey());
    }

    // =========================================================
    //  Orders
    // =========================================================

    /** Cache a single order detail for a customer  */
    public static function rememberOrder(int $orderId, int $customerId, callable $callback)
    {
        return Cache::remember(self::orderKey($orderId, $customerId), self::TTL_SHORT, $callback);
    }

    /** Call after order status update or cancellation */
    public static function forgetOrder(int $orderId, int $customerId): void
    {
        Cache::forget(self::orderKey($orderId, $customerId));
    }

    /** Cache order statuses list  */
    public static function rememberOrderStatuses(string $type, callable $callback)
    {
        return Cache::remember(self::orderStatusesKey($type), self::TTL_DAY, $callback);
    }

    /** Call if order statuses change in admin */
    public static function forgetOrderStatuses(string $type = 'O'): void
    {
        Cache::forget(self::orderStatusesKey($type));
    }



    // =========================================================
    //  Images
    // =========================================================

    public static function imageKey(string $object_type, int $object_id): string
    {
        return "image.{$object_type}.{$object_id}";
    }

    /** Cache an image by object type and ID */
    public static function rememberImage(string $object_type, int $object_id, callable $callback)
    {
        return Cache::remember(self::imageKey($object_type, $object_id), self::TTL_MEDIUM, $callback);
    }

    /**
     * Forget a cached image.
     */
    public static function forgetImage(string $object_type, int $object_id): void
    {
        Cache::forget(self::imageKey($object_type, $object_id));
    }
}
