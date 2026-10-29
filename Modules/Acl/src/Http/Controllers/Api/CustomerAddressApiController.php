<?php

namespace Modules\Acl\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Countries\Models\Address;
use Modules\Acl\Http\Requests\StoreAddressRequest;
use Modules\Acl\Http\Requests\UpdateAddressRequest;
use Modules\Acl\Http\Resources\AddressResource;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class CustomerAddressApiController extends Controller
{
    /**
     * Get all addresses 
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        // Get all addresses 
        $addresses = $customer->addresses()
            ->with(['country', 'state', 'city'])
            ->orderByDesc('created_at')
            ->get();
        // \Log::info('Addresses fetched for customer ID: ' . $customer->id . ' - Count: ' . $addresses->count());

        return $this->sendResponse(
            AddressResource::collection($addresses),
            'Addresses retrieved successfully'
        );
    }

    /**
     * Store a new address for the customer
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $customer = $request->user();

        // If this is the first address or marked as default, set it as default
        $isDefault = $request->boolean('is_default', false) ||
            $customer->addresses()->count() === 0;

        // If setting as default, unset other defaults
        if ($isDefault) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $address = $customer->addresses()->create([
            'address_type' => $request->input('address_type', 'home'),
            'address_line_1' => $request->input('address_line_1'),
            'address_line_2' => $request->input('address_line_2'),
            'city_id' => $request->input('city_id'),
            'state_id' => $request->input('state_id'),
            'country_id' => $request->input('country_id'),
            'zip_code' => $request->input('zip_code'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'is_default' => $isDefault,
        ]);

        return $this->sendResponse(
            new AddressResource($address->load(['country', 'state', 'city'])),
            'Address created successfully',
            201
        );
    }

    /**
     * Get a specific address
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:addresses,id',
            ]);

            $customer = $request->user();
            $address = Address::find($validated['id']);

            // Verify the address belongs to the customer
            if (
                !$address || $address->addressable_id !== $customer->id ||
                $address->addressable_type !== get_class($customer)
            ) {
                return $this->sendError('Address not found', [], 404);
            }

            return $this->sendResponse(
                new AddressResource($address->load(['country', 'state', 'city'])),
                'Address retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * Update an address
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:addresses,id',
                'address_type' => 'nullable|string|in:home,work,other',
                'address_line_1' => 'sometimes|required|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city_id' => 'sometimes|required|integer|exists:cities,id',
                'state_id' => 'sometimes|required|integer|exists:country_states,id',
                'country_id' => 'sometimes|required|integer|exists:countries,id',
                'zip_code' => 'sometimes|required|string|max:20',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'is_default' => 'nullable|boolean',
            ]);

            $customer = $request->user();
            $address = Address::find($validated['id']);

            // Verify the address belongs to the customer
            if (
                !$address || $address->addressable_id !== $customer->id ||
                $address->addressable_type !== get_class($customer)
            ) {
                return $this->sendError('Address not found', [], 404);
            }

            // Handle default address logic
            if (isset($validated['is_default']) && $validated['is_default'] && !$address->is_default) {
                $customer->addresses()->update(['is_default' => false]);
            }

            // Remove id from validated data before updating
            $updateData = collect($validated)->except('id')->toArray();
            $address->update($updateData);

            return $this->sendResponse(
                new AddressResource($address->load(['country', 'state', 'city'])),
                'Address updated successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * Delete an address
     */
    public function destroy(Request $request, $id): JsonResponse
    {

        try {

            $customer = $request->user();
            // dd($customer);
            $address = Address::where('id', $id)
                ->where('addressable_id', $customer->id)
                ->where('addressable_type', get_class($customer))
                ->first();

            if (
                !$address || $address->addressable_id !== $customer->id ||
                $address->addressable_type !== get_class($customer)
            ) {
                return $this->sendError('Address not found', [], 500);
            }

            if ($customer->addresses()->count() === 1) {
                return $this->sendError(
                    'Cannot delete the only address. Add another address first.',
                    [],
                    500
                );
            }

            // If deleting default address, set another as default
            if ($address->is_default) {
                $nextAddress = $customer->addresses()
                    ->where('id', '!=', $address->id)
                    ->first();
                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }

            $address->delete();

            return $this->sendResponse(
                null,
                'Address deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * Set an address as default
     */
    public function setDefault(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'address_id' => 'required|integer|exists:addresses,id',
            ]);

            $customer = $request->user();
            $address = Address::find($validated['address_id']);

            // Verify the address belongs to the customer
            if (
                !$address || $address->addressable_id !== $customer->id ||
                $address->addressable_type !== get_class($customer)
            ) {
                return $this->sendError('Address not found', [], 404);
            }

            // Unset all other defaults
            $customer->addresses()->update(['is_default' => false]);

            // Set this as default
            $address->update(['is_default' => true]);

            return $this->sendResponse(
                new AddressResource($address->load(['country', 'state', 'city'])),
                'Address set as default successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    public function getDefault(Request $request): JsonResponse
    {
        try {
       
            $customer = $request->user();
            $address = $customer->addresses()->where('is_default', true)->first();
            // dd($address);
            if (!$address) {
                return $this->sendError('No default address found', [], 404);
            }

            return $this->sendResponse(
                new AddressResource($address),
                'Default address retrieved successfully'
            );
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
