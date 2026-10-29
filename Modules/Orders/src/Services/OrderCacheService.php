<?php

namespace Modules\Orders\Services;

use Modules\Core\Services\BaseCacheService;

class OrderCacheService extends BaseCacheService
{
    // =========================================================
    //  Cache Keys
    // =========================================================
    
    private function ordersListKey(int $customerId, int $page, array $filters): string
    {
        $filterHash = md5(json_encode($filters));
        return "orders.{$customerId}.{$page}.{$filterHash}";
    }

    private function orderKey(int $orderId, int $customerId): string
    {
        return "order.{$orderId}.customer.{$customerId}";
    }

    private function codOrdersKey(int $page): string
    {
        return "cod_orders.{$page}";
    }

    private function orderStatusesKey(string $type): string
    {
        return "order_statuses.{$type}";
    }

    // =========================================================
    //  Cache Methods
    // =========================================================

    /**
     * Cache customer orders list
     */
    public function rememberOrdersList(int $customerId, int $page, array $filters, callable $callback)
    {
        return $this->remember(
            $this->ordersListKey($customerId, $page, $filters),
            self::TTL_SHORT,
            $callback
        );
    }

    /**
     * Cache single order details
     */
    public function rememberOrder(int $orderId, int $customerId, callable $callback)
    {
        return $this->remember($this->orderKey($orderId, $customerId), self::TTL_MEDIUM, $callback);
    }

    /**
     * Cache COD orders list
     */
    public function rememberCodOrders(int $page, callable $callback)
    {
        return $this->remember($this->codOrdersKey($page), self::TTL_SHORT, $callback);
    }

    /**
     * Cache order statuses
     */
    public function rememberOrderStatuses(string $type, callable $callback)
    {
        return $this->remember($this->orderStatusesKey($type), self::TTL_DAY, $callback);
    }

    // =========================================================
    //  Invalidation Methods
    // =========================================================

    /**
     * Invalidate customer orders list (all pages and filters)
     */
    public function forgetOrdersList(int $customerId): void
    {
        $this->forgetPattern("orders.{$customerId}.*");
    }

    /**
     * Invalidate single order cache
     */
    public function forgetOrder(int $orderId, int $customerId): void
    {
        $this->forget($this->orderKey($orderId, $customerId));
    }

    /**
     * Invalidate COD orders cache (all pages)
     */
    public function forgetCodOrders(): void
    {
        $this->forgetPattern("cod_orders.*");
    }

    /**
     * Invalidate order statuses cache
     */
    public function forgetOrderStatuses(string $type = 'O'): void
    {
        $this->forget($this->orderStatusesKey($type));
    }
}
