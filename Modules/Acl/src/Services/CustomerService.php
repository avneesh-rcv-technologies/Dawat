<?php

namespace Modules\Acl\Services;

use Modules\Acl\Repositories\CustomerRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getAllCustomers(array $filters = [])
    {
        return $this->customerRepository->getAll($filters);
    }

    public function find(int $id)
    {
        return $this->customerRepository->findById($id);
    }

    public function findWithRelations(int $id)
    {
        return $this->customerRepository->findByIdWithRelations($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $data['status'] = $data['status'] ?? true;
            $data['email_verified'] = (bool) ($data['email_verified'] ?? false);
            $data['email_verified_at'] = $data['email_verified'] ? ($data['email_verified_at'] ?? now()) : null;

            return $this->customerRepository->create($data);
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            if (array_key_exists('email_verified', $data)) {
                $data['email_verified'] = (bool) $data['email_verified'];
                $data['email_verified_at'] = $data['email_verified'] ? ($data['email_verified_at'] ?? now()) : null;
            }

            return $this->customerRepository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->customerRepository->delete($id);
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $customers = $this->customerRepository->bulkDelete($ids);
            return count($customers);
        });
    }

    public function bulkUpdateStatus(array $ids, bool $status): int
    {
        return $this->customerRepository->bulkUpdateStatus($ids, $status);
    }

    public function getCustomerView(int $id)
    {
        $customer = $this->customerRepository->getCustomerView($id);
        
        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        // Get analytics data
        $analyticsService = app(\Modules\Admin\Services\AnalyticsService::class);
        $dateFrom = request()->input('date_from');
        $dateTo = request()->input('date_to');
        $analytics = $analyticsService->getCustomerAnalytics($id, $dateFrom, $dateTo);

        return compact('customer', 'analytics', 'dateFrom', 'dateTo');
    }
}
