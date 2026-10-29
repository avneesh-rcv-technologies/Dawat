<?php

namespace Modules\Countries\Helpers;

use Modules\Countries\Models\Address;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;
use Modules\Countries\Models\City;
use Illuminate\Support\Facades\DB;

class AddressHelper
{
    /**
     * Save or update address for any model.
     *
     * @param string $modelClass Full model class path
     * @param int $modelId Model ID
     * @param array $addressData Address data
     * @param int|null $addressId Existing address ID (for update)
     * @return Address|null
     */
    public static function saveAddress(
        string $modelClass,
        int $modelId,
        array $addressData,
        ?int $addressId = null
    ): ?Address {
        try {
            DB::beginTransaction();

            $model = $modelClass::find($modelId);

            if (!$model) {
                throw new \Exception("Model not found");
            }

            // Prepare address data
            $data = [
                'address_type' => $addressData['address_type'] ?? null,
                'address_line_1' => $addressData['address_line_1'],
                'address_line_2' => $addressData['address_line_2'] ?? null,
                'city_id' => $addressData['city_id'] ?? null,
                'state_id' => $addressData['state_id'] ?? null,
                'country_id' => $addressData['country_id'],
                'zip_code' => $addressData['zip_code'] ?? null,
                'latitude' => $addressData['latitude'] ?? null,
                'longitude' => $addressData['longitude'] ?? null,
                'is_default' => $addressData['is_default'] ?? false,
            ];

            // If updating existing address
            if ($addressId) {
                $address = Address::where('id', $addressId)
                    ->where('addressable_type', $modelClass)
                    ->where('addressable_id', $modelId)
                    ->first();

                if ($address) {
                    // If setting as default, unset others
                    if ($data['is_default']) {
                        Address::where('addressable_type', $modelClass)
                            ->where('addressable_id', $modelId)
                            ->where('id', '!=', $addressId)
                            ->update(['is_default' => false]);
                    }

                    $address->update($data);
                    DB::commit();
                    return $address->fresh();
                }
            }

            // Creating new address
            if ($data['is_default']) {
                Address::where('addressable_type', $modelClass)
                    ->where('addressable_id', $modelId)
                    ->update(['is_default' => false]);
            }

            $address = new Address($data);
            $address->addressable_type = $modelClass;
            $address->addressable_id = $modelId;
            $address->save();

            DB::commit();
            return $address;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get address by ID with relations.
     *
     * @param int $addressId
     * @return Address|null
     */
    public static function getAddress(int $addressId): ?Address
    {
        return Address::with(['country', 'state', 'city'])->find($addressId);
    }

    /**
     * Get all addresses for a model.
     *
     * @param string $modelClass
     * @param int $modelId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAddresses(string $modelClass, int $modelId)
    {
        return Address::with(['country', 'state', 'city'])
            ->where('addressable_type', $modelClass)
            ->where('addressable_id', $modelId)
            ->get();
    }

    /**
     * Delete an address.
     *
     * @param int $addressId
     * @param string|null $modelClass
     * @param int|null $modelId
     * @return bool
     */
    public static function deleteAddress(
        int $addressId,
        ?string $modelClass = null,
        ?int $modelId = null
    ): bool {
        $query = Address::where('id', $addressId);

        if ($modelClass && $modelId) {
            $query->where('addressable_type', $modelClass)
                ->where('addressable_id', $modelId);
        }

        return $query->delete();
    }

    /**
     * Get all countries for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCountries()
    {
        return Country::orderBy('name')->get();
    }

    /**
     * Get states by country ID.
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getStatesByCountry(int $countryId)
    {
        return CountryState::where('country_id', $countryId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get cities by state ID.
     *
     * @param int $stateId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCitiesByState(int $stateId)
    {
        return City::where('state_id', $stateId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get cities by country ID.
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCitiesByCountry(int $countryId)
    {
        return City::where('country_id', $countryId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Format address data for form pre-filling.
     *
     * @param Address|null $address
     * @return array
     */
    public static function formatAddressForForm(?Address $address): array
    {
        if (!$address) {
            return [
                'address_type' => '',
                'address_line_1' => '',
                'address_line_2' => '',
                'city_id' => '',
                'state_id' => '',
                'country_id' => '',
                'zip_code' => '',
                'latitude' => '',
                'longitude' => '',
                'is_default' => false,
            ];
        }

        return [
            'id' => $address->id,
            'address_type' => $address->address_type ?? '',
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2 ?? '',
            'city_id' => $address->city_id ?? '',
            'state_id' => $address->state_id ?? '',
            'country_id' => $address->country_id,
            'zip_code' => $address->zip_code ?? '',
            'latitude' => $address->latitude ?? '',
            'longitude' => $address->longitude ?? '',
            'is_default' => $address->is_default,
        ];
    }
}
