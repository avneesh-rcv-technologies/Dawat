<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Acl\Models\Admin;
use Modules\Acl\Models\Customer;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;
use Modules\Restaurant\Models\Restaurant;

/**
 * Limits admin-panel data (customers, orders, transactions, revenue) to the restaurant of the
 * signed-in restaurant owner. Platform admins (no restaurant) are not limited.
 *
 * restaurantId() returns:
 *  - null: platform admin, sees everything
 *  - an id: restaurant owner, sees only data of that restaurant
 *  - 0:     restaurant owner without a restaurant yet, sees nothing (no restaurant has id 0)
 */
class RestaurantDataScope
{
    public function __construct(private RestaurantOwnerRoleResolver $owners) {}

    public function restaurantId(?Admin $admin = null): ?int
    {
        $admin ??= auth('admin')->user();
        if (!$admin instanceof Admin) {
            return 0;
        }

        $restaurantId = Restaurant::where('admin_id', $admin->id)->value('id');
        if ($restaurantId) {
            return (int) $restaurantId;
        }

        return $this->owners->isOwner($admin) ? 0 : null;
    }

    public function isScoped(?Admin $admin = null): bool
    {
        return $this->restaurantId($admin) !== null;
    }

    /** Orders query limited to the restaurant (no-op for platform admins). */
    public function scopeOrders(Builder|Relation $orders, ?int $restaurantId): Builder|Relation
    {
        if ($restaurantId === null) {
            return $orders;
        }

        $table = $orders->getModel()->getTable();

        return $orders->where("{$table}.vendor_type", Restaurant::class)
            ->where("{$table}.vendor_id", $restaurantId);
    }

    /** Customers query limited to customers who ordered from the restaurant. */
    public function scopeCustomers(Builder $customers, ?int $restaurantId): Builder
    {
        if ($restaurantId === null) {
            return $customers;
        }

        return $customers->whereHas('orders', fn (Builder $orders) => $this->scopeOrders($orders, $restaurantId));
    }

    public function canAccessCustomer(int|string $customerId, ?Admin $admin = null): bool
    {
        $restaurantId = $this->restaurantId($admin);

        return $this->scopeCustomers(Customer::query(), $restaurantId)->whereKey($customerId)->exists();
    }

    public function canAccessOrder(int|string $orderId, ?Admin $admin = null): bool
    {
        $restaurantId = $this->restaurantId($admin);

        return $this->scopeOrders(Order::query(), $restaurantId)->whereKey($orderId)->exists();
    }

    public function canAccessTransaction(int|string $transactionId, ?Admin $admin = null): bool
    {
        $restaurantId = $this->restaurantId($admin);

        return Transaction::query()->whereKey($transactionId)
            ->when($restaurantId !== null, fn (Builder $q) => $q->whereHas('order', fn (Builder $orders) => $this->scopeOrders($orders, $restaurantId)))
            ->exists();
    }

    public function canAccessRestaurant(int|string $restaurantId, ?Admin $admin = null): bool
    {
        $own = $this->restaurantId($admin);

        return $own === null || ($own > 0 && (int) $restaurantId === $own);
    }

    /** All ids belong to the restaurant's customers (for bulk actions). */
    public function canAccessCustomers(array $customerIds, ?Admin $admin = null): bool
    {
        $customerIds = array_values(array_unique(array_map('intval', $customerIds)));
        if ($customerIds === []) {
            return true;
        }

        $restaurantId = $this->restaurantId($admin);

        return $this->scopeCustomers(Customer::query(), $restaurantId)->whereKey($customerIds)->count() === count($customerIds);
    }
}
