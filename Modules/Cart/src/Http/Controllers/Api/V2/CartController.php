<?php

namespace Modules\Cart\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Acl\Models\Customer;
use Modules\Cart\Http\Requests\V2\AddCartItemRequest;
use Modules\Cart\Http\Requests\V2\UpdateCartItemRequest;
use Modules\Cart\Http\Resources\V2\CartItemResource;
use Modules\Cart\Http\Resources\V2\CartResource;
use Modules\Cart\Services\CartV2Service;

class CartController extends Controller
{
    public function __construct(private CartV2Service $cart) {}

    private function customer(Request $request): ?Customer
    {
        return $request->attributes->get('cart_customer');
    }

    private function sessionId(Request $request): ?string
    {
        return $request->attributes->get('cart_session_id');
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['restaurant_id' => ['required', 'integer', 'min:1']], [
            'restaurant_id.required' => 'Please select a restaurant to view your cart.',
            'restaurant_id.integer' => 'Please select a valid restaurant.',
            'restaurant_id.min' => 'Please select a valid restaurant.',
        ]);
        $cart = $this->cart->get($this->customer($request), (int) $data['restaurant_id'], $this->sessionId($request));

        return response()->json([
            'success' => true, 'message' => 'Cart retrieved successfully.',
            'data' => $cart ? (new CartResource($cart))->resolve($request) : CartResource::emptyCart((int) $data['restaurant_id'], $this->sessionId($request)),
        ]);
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $cart = $this->cart->add($this->customer($request), $request->validated(), $this->sessionId($request));

        return response()->json([
            'success' => true, 'message' => 'Item added to cart.',
            'data' => (new CartResource($cart))->resolve($request),
        ], 201);
    }

    public function show(Request $request, int $cartItemId): JsonResponse
    {
        return response()->json([
            'success' => true, 'message' => 'Cart item retrieved successfully.',
            'data' => (new CartItemResource($this->cart->getItem($this->customer($request), $cartItemId, $this->sessionId($request))))->resolve($request),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $data = $request->validate(['restaurant_id' => ['required', 'integer', 'min:1']], [
            'restaurant_id.required' => 'Please select a restaurant to clear your cart.',
            'restaurant_id.integer' => 'Please select a valid restaurant.',
            'restaurant_id.min' => 'Please select a valid restaurant.',
        ]);
        $restaurantId = (int) $data['restaurant_id'];
        $this->cart->clear($this->customer($request), $restaurantId, $this->sessionId($request));

        return response()->json([
            'success' => true, 'message' => 'Cart cleared successfully.',
            'data' => CartResource::emptyCart($restaurantId, $this->sessionId($request)),
        ]);
    }

    public function destroy(Request $request, int $cartItemId): JsonResponse
    {
        $cart = $this->cart->remove($this->customer($request), $cartItemId, $this->sessionId($request));

        return response()->json([
            'success' => true, 'message' => 'Cart item removed.',
            'data' => $cart ? (new CartResource($cart))->resolve($request) : CartResource::emptyCart(null, $this->sessionId($request)),
        ]);
    }

    public function update(UpdateCartItemRequest $request, int $cartItemId): JsonResponse
    {
        $cart = $this->cart->updateItem($this->customer($request), $cartItemId, $request->validated(), $this->sessionId($request));

        return response()->json([
            'success' => true, 'message' => 'Cart item updated successfully.',
            'data' => (new CartResource($cart))->resolve($request),
        ]);
    }
}
