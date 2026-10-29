<?php

namespace Modules\Checkout\Services;

use Modules\Core\Services\BaseCacheService;

class CheckoutCacheService extends BaseCacheService
{
    // =========================================================
    //  Cache Keys
    // =========================================================

    private function paymentMethodsKey(): string
    {
        return 'checkout.payment_methods';
    }

    private function shippingMethodsKey(): string
    {
        return 'checkout.shipping_methods';
    }

    private function categoriesKey(int $restaurantId): string
    {
        return "checkout_categories.{$restaurantId}";
    }

    private function itemsKey(int $restaurantId, ?int $categoryId, ?int $variantId, int $page, int $perPage): string
    {
        return "checkout_items.{$restaurantId}.{$categoryId}.{$variantId}.{$page}.{$perPage}";
    }

    private function filterTabsKey(int $restaurantId, ?int $categoryId): string
    {
        return "filter_tabs.{$restaurantId}.{$categoryId}";
    }

    private function reviewOrderKey(int $customerId, int $restaurantId): string
    {
        return "review_order.{$customerId}.{$restaurantId}";
    }

    private function customerAddressesKey(int $customerId): string
    {
        return "customer_addresses.{$customerId}";
    }

    // =========================================================
    //  Cache Methods
    // =========================================================

    /**
     * Cache payment methods
     */
    public function rememberPaymentMethods(callable $callback)
    {
        return $this->remember($this->paymentMethodsKey(), self::TTL_DAY, $callback);
    }

    /**
     * Cache shipping methods
     */
    public function rememberShippingMethods(callable $callback)
    {
        return $this->remember($this->shippingMethodsKey(), self::TTL_DAY, $callback);
    }

    /**
     * Cache checkout categories
     */
    public function rememberCategories(int $restaurantId, callable $callback)
    {
        return $this->remember($this->categoriesKey($restaurantId), self::TTL_4_HOURS, $callback);
    }

    /**
     * Cache checkout items with pagination
     */
    public function rememberItems(int $restaurantId, ?int $categoryId, ?int $variantId, int $page, int $perPage, callable $callback)
    {
        return $this->remember(
            $this->itemsKey($restaurantId, $categoryId, $variantId, $page, $perPage),
            self::TTL_2_HOURS,
            $callback
        );
    }

    /**
     * Cache filter tabs
     */
    public function rememberFilterTabs(int $restaurantId, ?int $categoryId, callable $callback)
    {
        return $this->remember($this->filterTabsKey($restaurantId, $categoryId), self::TTL_2_HOURS, $callback);
    }

    /**
     * Cache review order
     */
    public function rememberReviewOrder(int $customerId, int $restaurantId, callable $callback)
    {
        return $this->remember($this->reviewOrderKey($customerId, $restaurantId), self::TTL_SHORT, $callback);
    }

    /**
     * Cache customer addresses
     */
    public function rememberCustomerAddresses(int $customerId, callable $callback)
    {
        return $this->remember($this->customerAddressesKey($customerId), self::TTL_HOUR, $callback);
    }

    // =========================================================
    //  Invalidation Methods
    // =========================================================

    /**
     * Invalidate payment methods cache
     */
    public function forgetPaymentMethods(): void
    {
        $this->forget($this->paymentMethodsKey());
    }

    /**
     * Invalidate shipping methods cache
     */
    public function forgetShippingMethods(): void
    {
        $this->forget($this->shippingMethodsKey());
    }

    /**
     * Invalidate all checkout payment/shipping caches
     */
    public function forgetCheckoutMethods(): void
    {
        $this->forget($this->paymentMethodsKey());
        $this->forget($this->shippingMethodsKey());
    }

    /**
     * Invalidate checkout categories cache
     */
    public function forgetCategories(int $restaurantId): void
    {
        $this->forget($this->categoriesKey($restaurantId));
    }

    /**
     * Invalidate all checkout items for a restaurant
     */
    public function forgetItems(int $restaurantId): void
    {
        $this->forgetPattern("checkout_items.{$restaurantId}.*");
        $this->forgetPattern("filter_tabs.{$restaurantId}.*");
    }

    /**
     * Invalidate review order cache
     */
    public function forgetReviewOrder(int $customerId, int $restaurantId): void
    {
        $this->forget($this->reviewOrderKey($customerId, $restaurantId));
    }

    /**
     * Invalidate all review orders for a customer
     */
    public function forgetAllReviewOrders(int $customerId): void
    {
        $this->forgetPattern("review_order.{$customerId}.*");
    }

    /**
     * Invalidate customer addresses cache
     */
    public function forgetCustomerAddresses(int $customerId): void
    {
        $this->forget($this->customerAddressesKey($customerId));
    }
}
