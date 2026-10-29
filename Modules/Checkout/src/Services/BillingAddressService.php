<?php

namespace Modules\Checkout\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Countries\Models\Address;
use Modules\Acl\Models\Customer;

class BillingAddressService
{
    /**
     * Create billing address for customer
     *
     * @param Customer $customer
     * @param array $addressData
     * @return Address
     * @throws Exception
     */
    public function createBillingAddress(Customer $customer, array $addressData): Address
    {
        return DB::transaction(function () use ($customer, $addressData) {
            return $customer->addresses()->create([
                'address_type' => 'billing',
                'address_line_1' => $addressData['address_line_1'],
                'address_line_2' => $addressData['address_line_2'] ?? null,
                'city_id' => $addressData['city_id'],
                'state_id' => $addressData['state_id'],
                'country_id' => $addressData['country_id'],
                'zip_code' => $addressData['zip_code'],
                'latitude' => $addressData['latitude'] ?? null,
                'longitude' => $addressData['longitude'] ?? null,
                'is_default' => $addressData['is_default'] ?? false,
            ]);
        });
    }

    /**
     * Get customer's billing addresses
     *
     * @param Customer $customer
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBillingAddresses(Customer $customer)
    {
        return $customer->addresses()
            ->where('address_type', 'billing')
            ->with(['city', 'state', 'country'])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Update billing address
     *
     * @param Address $address
     * @param array $addressData
     * @return Address
     * @throws Exception
     */
    public function updateBillingAddress(Address $address, array $addressData): Address
    {
        return DB::transaction(function () use ($address, $addressData) {
            $address->update([
                'address_line_1' => $addressData['address_line_1'],
                'address_line_2' => $addressData['address_line_2'] ?? null,
                'city_id' => $addressData['city_id'],
                'state_id' => $addressData['state_id'],
                'country_id' => $addressData['country_id'],
                'zip_code' => $addressData['zip_code'],
                'latitude' => $addressData['latitude'] ?? null,
                'longitude' => $addressData['longitude'] ?? null,
                'is_default' => $addressData['is_default'] ?? $address->is_default,
            ]);

            return $address->fresh(['city', 'state', 'country']);
        });
    }

    /**
     * Delete billing address
     *
     * @param Address $address
     * @return bool
     * @throws Exception
     */
    public function deleteBillingAddress(Address $address): bool
    {
        return DB::transaction(function () use ($address) {
            if ($address->address_type !== Customer::class . '_billing') {
                throw new Exception('Address is not a billing address');
            }

            return $address->delete();
        });
    }
}