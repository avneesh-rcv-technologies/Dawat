<?php

namespace Modules\Acl\Repositories;

use Modules\Acl\Models\Customer;

class CustomerRepository
{
    public function getAll(array $filters = [])
    {
        $query = Customer::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function findById(int $id)
    {
        return Customer::findOrFail($id);
    }

    public function findByIdWithRelations(int $id)
    {
        return Customer::with(['addresses', 'orders'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Customer::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $customer = $this->findById($id);
        return $customer->update($data);
    }

    public function delete(int $id): bool
    {
        $customer = $this->findById($id);
        return $customer->delete();
    }

    public function bulkDelete(array $ids): array
    {
        $customers = Customer::whereIn('id', $ids)->get();
        Customer::whereIn('id', $ids)->delete();
        return $customers->toArray();
    }

    public function bulkUpdateStatus(array $ids, bool $status): int
    {
        return Customer::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * @param int|null $restaurantId Restaurant owner: only a customer of that restaurant, with
     *                               only that restaurant's orders loaded. Null = platform admin.
     */
    public function getCustomerView(int $id, ?int $restaurantId = null)
    {
        $scope = app(\Modules\Restaurant\Services\RestaurantDataScope::class);

        $query = Customer::with([
            'addresses',
            'orders' => fn ($orders) => $scope->scopeOrders($orders, $restaurantId),
        ])
            ->select('customers.*')
            ->where('customers.id', $id);

        return $scope->scopeCustomers($query, $restaurantId)->firstOrFail();
    }
}
