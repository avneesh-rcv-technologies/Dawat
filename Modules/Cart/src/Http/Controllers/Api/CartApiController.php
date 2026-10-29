<?php

namespace Modules\Cart\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Cart\Services\CartService;
use Modules\Discounts\Services\DiscountService;
use Modules\Cart\Http\Requests\AddToCartRequest;
use Modules\Cart\Http\Requests\UpdateCartItemRequest;
use Modules\Cart\Http\Requests\ApplyCouponRequest;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class CartApiController extends Controller
{
    protected CartService $cartService;
    protected DiscountService $discountService;

    public function __construct(
        CartService $cartService,
        DiscountService $discountService
    ) {
        $this->cartService = $cartService;
        $this->discountService = $discountService;
    }

    /**
     * Get cart details
     * GET /api/cart?restaurant_id={restaurantId}
     */
    // public function getCart(Request $request): JsonResponse
    // {
    //     try {
    //         $restaurantId = $request->input('restaurant_id');

    //         if (!$restaurantId) {
    //             return response()->json([
    //                 'success' => false,
    //                 'errors' => 'Restaurant ID is required'
    //             ], 422);
    //         }


    //         $customer = Auth::guard('customer')->user();
    //         $customerId = $customer?->id;
    //         $sessionId = $request->header('X-Session-ID') ?: session()->getId();
    //         $result = $this->cartService->getCart($restaurantId, $customerId, $sessionId);

    //         return response()->json($result);
    //     } catch (\Throwable $e) {
    //         return ApiExceptionHandler::handle($e);
    //     }
    // }



    public function getCart(Request $request): JsonResponse
    {
        try {
            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            $result = $this->cartService->getCart($customerId, null);

            return response()->json($result);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Add item to cart
     * POST /api/cart/add-items
     */
    public function addToCart(AddToCartRequest $request): JsonResponse
    {
        try {
            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            $validation = $this->cartService->validateRestaurant($request->restaurant_id);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message']
                ], 500);
            }


            $result = $this->cartService->addToCart(
                $request->restaurant_id,
                $request->item_id,
                $request->quantity ?? 1,
                $request->selected_options,
                $request->special_instructions,
                $customerId,
                null
            );

            return response()->json($result, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     * PUT /api/cart/update-item
     */
    public function updateCartItem(UpdateCartItemRequest $request): JsonResponse
    {
        try {

            $cartItemId = $request->input('cart_item_id');

            if (!$cartItemId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item ID is required'
                ], 422);
            }

            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            $result = $this->cartService->updateCartItemQuantity(
                $cartItemId,
                $request->quantity,
                $customerId,
                null
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     * DELETE /api/cart/remove-item
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        try {
            
            $cartItemId = $request->input('cart_item_id');

            if (!$cartItemId) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Cart item ID is required'
                ], 422);
            }

            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;
            $result = $this->cartService->removeFromCart($cartItemId, $customerId, null);

            return response()->json($result);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear entire cart
     * DELETE /api/cart/clear
     */
    public function clearCart(Request $request): JsonResponse
    {
        try {
            $restaurantId = $request->input('restaurant_id');

            if (!$restaurantId) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Restaurant ID is required'
                ], 422);
            }

            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            $result = $this->cartService->clearCart($restaurantId, $customerId, null);

            return response()->json($result);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Get cart count
     * GET /api/cart/count?restaurant_id={restaurantId}
     */
    public function getCartCount(Request $request): JsonResponse
    {
        try {
            $restaurantId = $request->input('restaurant_id');

            if (!$restaurantId) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Restaurant ID is required'
                ], 422);
            }

            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            $count = $this->cartService->getCartCount($restaurantId, $customerId, null);

            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => 'Cart count retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Apply coupon to cart
     * POST /api/cart/apply-coupon
     */
    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        try {
            $restaurantId = $request->input('restaurant_id');

            if (!$restaurantId) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Restaurant ID is required'
                ], 422);
            }

            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            // Get existing cart (don't create if doesn't exist)
            $cart = $customerId
                ? $this->cartService->getOrCreateCart($restaurantId, $customerId, null)
                : $this->cartService->getOrCreateCart($restaurantId, null, null);

            // Ensure cart has items loaded and totals are up to date
            $cart->load('items.item');
            $cart->updateTotals();

            // Check if cart has items
            if ($cart->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Cannot apply coupon to empty cart. Please add items to your cart first.'
                ], 422);
            }

            // Prepare cart items for discount rules validation
            $cartItems = $cart->items->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->total_price,
                ];
            })->toArray();

            // Apply coupon using Discount service with rules validation
            $discountResult = $this->discountService->applyCoupon(
                $request->coupon_code,
                $cart->subtotal,
                $customer,
                $cartItems
            );

            // Update cart with discount information
            $cart->update([
                'coupon_code' => $request->coupon_code,
                'discount_id' => $discountResult['discount']->id,
                'discount_amount' => $discountResult['discount_amount'],
                'discount_type' => $discountResult['discount_type']->value,
                'discount_percentage' => $discountResult['discount_type']->value === 'P' ? $discountResult['discount']->amount : null,
                'discount_breakdown' => [
                    'coupon_code' => $request->coupon_code,
                    'discount_name' => $discountResult['discount']->name,
                    'discount_type' => $discountResult['discount_type']->label(),
                    'discount_amount' => $discountResult['discount_amount'],
                    'apply_to' => $discountResult['apply_to']->label(),
                    'original_amount' => $discountResult['discount']->amount,
                ],
            ]);

            // Recalculate cart totals
            $cart->updateTotals();

            // Get updated cart details
            $cartResult = $this->cartService->getCart($restaurantId, $customerId, null);

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully',
                // 'discount' => [
                //     'coupon_code' => $request->coupon_code,
                //     'discount_amount' => $discountResult['discount_amount'],
                //     'discount_type' => $discountResult['discount_type']->value,
                //     'discount_percentage' => $discountResult['discount_type']->value === 'P' ? $discountResult['discount']->amount : null,
                // ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Remove coupon from cart
     * DELETE /api/cart/remove-coupon
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        try {
            $restaurantId = $request->input('restaurant_id');

            if (!$restaurantId) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Restaurant ID is required'
                ], 422);
            }

            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            // Find existing cart
            $cart = $customerId
                ? $this->cartService->getOrCreateCart($restaurantId, $customerId, null)
                : $this->cartService->getOrCreateCart($restaurantId, null, null);

            if (!$cart->coupon_code) {
                return response()->json([
                    'success' => false,
                    'errors' => 'No coupon applied to cart'
                ], 422);
            }

            // Remove coupon from cart
            $cart->update([
                'coupon_code' => null,
                'discount_id' => null,
                'discount_amount' => 0,
                'discount_type' => null,
                'discount_percentage' => null,
                'discount_breakdown' => null,
            ]);

            // Recalculate cart totals
            $cart->updateTotals();

            // Get updated cart details
            $cartResult = $this->cartService->getCart($restaurantId, $customerId, null);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully',
                // 'cart' => $cartResult['cart']
            ]);
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
