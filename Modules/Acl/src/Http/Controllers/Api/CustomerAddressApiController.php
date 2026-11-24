<?php

namespace Modules\Acl\Http\Controllers\Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Http\Requests\StoreAddressRequest;
use Modules\Acl\Http\Requests\UpdateAddressRequest;
use Modules\Acl\Http\Resources\AddressResource;
use Modules\Acl\Services\CustomerAddressService;
use Throwable;

class CustomerAddressApiController extends Controller
{
    public function __construct(private CustomerAddressService $addresses) {}

    private function respond(callable $action): JsonResponse
    {
        try {
            return $action();
        } catch (ValidationException $e) {
            return $this->sendError($e->getMessage(), $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', [], 404);
        } catch (Throwable $e) {
            Log::error('Customer address operation failed.', ['exception_class' => get_class($e)]);
            return $this->sendError('Unable to process your address. Please try again.', [], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        return $this->respond(fn () => $this->sendResponse(AddressResource::collection(
            $request->user('customer')->addresses()->with(['country', 'state', 'city'])->orderByDesc('is_default')->latest()->get()
        ), 'Addresses retrieved successfully.'));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        return $this->respond(function () use ($request) {
            $result = $this->addresses->save($request->user('customer'), ['address_type' => 'home', ...$request->validated()]);
            return $this->sendResponse([
                ...(new AddressResource($result['address']))->resolve($request), 'delivery_carts' => $result['carts'],
            ], 'Address created successfully.', 201);
        });
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return $this->respond(fn () => $this->sendResponse(new AddressResource(
            $request->user('customer')->addresses()->with(['country', 'state', 'city'])->findOrFail($id)
        ), 'Address retrieved successfully.'));
    }

    public function update(UpdateAddressRequest $request, int $id): JsonResponse
    {
        return $this->respond(function () use ($request, $id) {
            $result = $this->addresses->save($request->user('customer'), $request->validated(), $id);
            return $this->sendResponse([
                ...(new AddressResource($result['address']))->resolve($request), 'delivery_carts' => $result['carts'],
            ], 'Address updated successfully.');
        });
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->respond(fn () => $this->sendResponse([
            'delivery_carts' => $this->addresses->delete($request->user('customer'), $id),
        ], 'Address deleted successfully.'));
    }

    public function setDefault(Request $request): JsonResponse
    {
        return $this->respond(function () use ($request) {
            $data = $request->validate(['address_id' => ['required', 'integer', 'min:1']]);
            $result = $this->addresses->save($request->user('customer'), ['is_default' => true], $data['address_id']);
            return $this->sendResponse([
                ...(new AddressResource($result['address']))->resolve($request), 'delivery_carts' => $result['carts'],
            ], 'Default address and delivery charges updated.');
        });
    }

    public function getDefault(Request $request): JsonResponse
    {
        return $this->respond(fn () => $this->sendResponse(new AddressResource(
            $request->user('customer')->addresses()->with(['country', 'state', 'city'])->where('is_default', true)->firstOrFail()
        ), 'Default address retrieved successfully.'));
    }
}
