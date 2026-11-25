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

    private function customer(Request $request): Customer
    {
        return $request->attributes->get('cart_customer');
    }

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cart->get($this->customer($request));

        return response()->json([
            'success' => true, 'message' => 'Cart retrieved successfully.',
            'data' => $cart
                ? (new CartResource($cart))->resolve($request)
                : CartResource::emptyCart(null),
        ]);
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $cart = $this->cart->add($this->customer($request), $request->validated());

        return response()->json([
            'success' => true, 'message' => 'Item added to cart.',
            'data' => (new CartResource($cart))->resolve($request),
        ], 201);
    }

    public function show(Request $request, int $cartItemId): JsonResponse
    {
        return response()->json([
            'success' => true, 'message' => 'Cart item retrieved successfully.',
            'data' => (new CartItemResource($this->cart->getItem($this->customer($request), $cartItemId)))->resolve($request),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cart->clear($this->customer($request));

        return response()->json([
            'success' => true, 'message' => 'Cart cleared successfully.',
            'data' => CartResource::emptyCart(null),
        ]);
    }

    public function destroy(Request $request, int $cartItemId): JsonResponse
    {
        $cart = $this->cart->remove($this->customer($request), $cartItemId);

        return response()->json([
            'success' => true, 'message' => 'Cart item removed.',
            'data' => $cart ? (new CartResource($cart))->resolve($request) : CartResource::emptyCart(null),
        ]);
    }

    public function update(UpdateCartItemRequest $request, int $cartItemId): JsonResponse
    {
        $cart = $this->cart->updateItem($this->customer($request), $cartItemId, $request->validated());

        return response()->json([
            'success' => true, 'message' => 'Cart item updated successfully.',
            'data' => (new CartResource($cart))->resolve($request),
        ]);
    }

    public function items(Request $request): JsonResponse
    {
        $cart = $this->cart->get($this->customer($request));

        return response()->json([
            'success' => true,
            'message' => 'Cart items retrieved successfully.',
            'data' => $cart ? CartItemResource::collection($cart->items)->resolve($request) : [],
            'meta' => [
                'restaurant_id' => $cart?->cartable_id ? (int) $cart->cartable_id : null,
                'line_count' => $cart?->items->count() ?? 0,
            ],
        ]);
    }
}
