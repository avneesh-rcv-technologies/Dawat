<?php

namespace Modules\Countries\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Countries\Helpers\AddressHelper;
use Modules\Countries\Models\Address;

class AddressController extends Controller
{
    /**
     * Get states by country ID (AJAX endpoint).
     */
    public function getStatesByCountry($countryId)
    {
        $states = AddressHelper::getStatesByCountry($countryId);
        return response()->json($states);
    }

    /**
     * Save address (can be used as API endpoint).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'model_class' => 'required|string',
            'model_id' => 'required|integer',
            'address_type' => 'nullable|string',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state_id' => 'nullable|integer|exists:country_states,id',
            'country_id' => 'required|integer|exists:countries,id',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            $address = AddressHelper::saveAddress(
                $validated['model_class'],
                $validated['model_id'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully',
                'data' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update address (can be used as API endpoint).
     */
    public function update(Request $request, $addressId)
    {
        $validated = $request->validate([
            'model_class' => 'required|string',
            'model_id' => 'required|integer',
            'address_type' => 'nullable|string',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state_id' => 'nullable|integer|exists:country_states,id',
            'country_id' => 'required|integer|exists:countries,id',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            $address = AddressHelper::saveAddress(
                $validated['model_class'],
                $validated['model_id'],
                $validated,
                $addressId
            );

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete address.
     */
    public function destroy($addressId)
    {
        try {
            AddressHelper::deleteAddress($addressId);

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get addresses for a model.
     */
    public function getAddresses(Request $request)
    {
        $validated = $request->validate([
            'model_class' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $addresses = AddressHelper::getAddresses(
            $validated['model_class'],
            $validated['model_id']
        );

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }
}
