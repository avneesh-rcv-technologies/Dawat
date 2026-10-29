<?php

namespace Modules\Checkout\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Countries\Models\Address;
use Modules\Acl\Models\Customer;

class DeliveryAddressService
{
    /**
     * Create delivery address for customer
     *
     * @param Customer $customer
     * @param array $addressData
     * @return Address
     * @throws Exception
     */
    public function createDeliveryAddress(Customer $customer, array $addressData): Address
    {
        return DB::transaction(function () use ($customer, $addressData) {
            return $customer->addresses()->create([
                'address_type' => 'delivery',
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
     * Get customer's delivery addresses
     *
     * @param Customer $customer
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDeliveryAddresses(Customer $customer)
    {
        return $customer->addresses()
            ->where('address_type', 'delivery')
            ->with(['city', 'state', 'country'])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Update delivery address
     *
     * @param Address $address
     * @param array $addressData
     * @return Address
     * @throws Exception
     */
    public function updateDeliveryAddress(Address $address, array $addressData): Address
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
     * Delete delivery address
     *
     * @param Address $address
     * @return bool
     * @throws Exception
     */
    public function deleteDeliveryAddress(Address $address): bool
    {
        return DB::transaction(function () use ($address) {
            if ($address->address_type !== Customer::class . '_delivery') {
                throw new Exception('Address is not a delivery address');
            }

            return $address->delete();
        });
    }
}