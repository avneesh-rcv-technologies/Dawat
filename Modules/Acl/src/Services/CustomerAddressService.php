<?php

namespace Modules\Acl\Services;

use Modules\Acl\Models\Customer;
use Modules\Countries\Models\Address;

class CustomerAddressService
{
    /**
     * Get all addresses for a customer
     */
    public function getCustomerAddresses(Customer $customer)
    {
        return $customer->addresses()
            ->with(['country', 'state', 'city'])
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Create a new address for customer
     */
    public function createAddress(Customer $customer, array $data): Address
    {
        $isDefault = $data['is_default'] ?? false || 
                     $customer->addresses()->count() === 0;

        if ($isDefault) {
            $customer->addresses()->update(['is_default' => false]);
        }

        return $customer->addresses()->create([
            'address_type' => $data['address_type'] ?? 'home',
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'city_id' => $data['city_id'],
            'state_id' => $data['state_id'],
            'country_id' => $data['country_id'],
            'zip_code' => $data['zip_code'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_default' => $isDefault,
        ]);
    }

    /**
     * Update an address
     */
    public function updateAddress(Address $address, array $data): Address
    {
        if (isset($data['is_default']) && $data['is_default'] && !$address->is_default) {
            $address->customer->addresses()->update(['is_default' => false]);
        }

        $address->update($data);
        return $address;
    }

    /**
     * Delete an address
     */
    public function deleteAddress(Address $address): bool
    {
        $customer = $address->addressable;

        if ($customer->addresses()->count() === 1) {
            throw new \Exception('Cannot delete the only address');
        }

        if ($address->is_default) {
            $nextAddress = $customer->addresses()
                ->where('id', '!=', $address->id)
                ->first();
            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        return $address->delete();
    }

    /**
     * Set address as default
     */
    public function setDefaultAddress(Address $address): Address
    {
        $customer = $address->addressable;
        $customer->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return $address;
    }
}
