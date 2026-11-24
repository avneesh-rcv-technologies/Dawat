<?php

namespace Modules\Acl\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Acl\Models\Customer;
use Modules\Cart\Services\CartDeliveryService;
use Modules\Checkout\Services\CheckoutCacheService;
use Modules\Orders\Services\DeliveryCoordinatesService;
use Modules\Countries\Models\Address;

class CustomerAddressService
{
    public function __construct(private CartDeliveryService $delivery, private DeliveryCoordinatesService $coordinates) {}

    public function getCustomerAddresses(Customer $customer)
    {
        return $customer->addresses()->with(['country', 'state', 'city'])->orderByDesc('is_default')->latest()->get();
    }

    public function createAddress(Customer $customer, array $data): Address
    {
        return $this->save($customer, ['address_type' => 'home', ...$data])['address'];
    }

    public function updateAddress(Address $address, array $data): Address
    {
        return $this->save($address->addressable, $data, $address->id)['address'];
    }

    public function deleteAddress(Address $address): bool
    {
        $this->delete($address->addressable, $address->id);
        return true;
    }

    public function setDefaultAddress(Address $address): Address
    {
        return $this->save($address->addressable, ['is_default' => true], $address->id)['address'];
    }

    public function save(Customer $customer, array $data, ?int $id = null): array
    {
        return DB::transaction(function () use ($customer, $data, $id) {
            Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $address = $id === null ? $customer->addresses()->make() : $customer->addresses()->lockForUpdate()->findOrFail($id);
            $address->fill($data);
            // An edit can resubmit unchanged text to refresh an old location.
            $addressFieldsProvided = array_intersect_key($data, array_flip([
                'address_line_1', 'address_line_2', 'city_id', 'state_id', 'country_id', 'zip_code', 'city_name',
            ])) !== [];
            $providedCoordinates = isset($data['latitude'], $data['longitude']);
            $this->coordinates->address($address, $addressFieldsProvided && !$providedCoordinates);
            $address->is_default = $address->is_default || !$customer->addresses()->where('id', '!=', $id ?? 0)->exists();
            if ($address->is_default) {
                $customer->addresses()->where('id', '!=', $id ?? 0)->update(['is_default' => false]);
            }
            $address->save();
            $carts = $this->delivery->refreshCustomer($customer,
                $address->is_default ? $address->id : null, $address->is_default ? null : $address->id);
            $this->forgetAddresses($customer);

            return ['address' => $address->load(['country', 'state', 'city']), 'carts' => $carts];
        });
    }

    public function delete(Customer $customer, int $id): array
    {
        return DB::transaction(function () use ($customer, $id) {
            Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $address = $customer->addresses()->lockForUpdate()->findOrFail($id);
            $wasDefault = $address->is_default;
            $address->delete();
            $next = $customer->addresses()->orderByDesc('is_default')->orderBy('id')->first();
            if ($wasDefault && $next) $next->update(['is_default' => true]);
            // Unbind deleted destinations; recalculation selects the remaining default, if any.
            $customer->ownedCarts()->where('delivery_address_id', $id)->update(['delivery_address_id' => null]);
            $carts = $this->delivery->refreshCustomer($customer);
            $this->forgetAddresses($customer);

            return $carts;
        });
    }

    private function forgetAddresses(Customer $customer): void
    {
        DB::afterCommit(function () use ($customer) {
            try {
                app(CheckoutCacheService::class)->forgetCustomerAddresses($customer->id);
            } catch (\Throwable $e) {
                Log::warning('Customer address cache invalidation failed.', ['customer_id' => $customer->id, 'exception_class' => get_class($e)]);
            }
        });
    }
}
