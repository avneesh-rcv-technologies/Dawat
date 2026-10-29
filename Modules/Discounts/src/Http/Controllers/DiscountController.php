<?php

namespace Modules\Discounts\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Customers\Models\User;
use Modules\Orders\Models\Order;
use Modules\Discounts\Models\Discount;
use Modules\Discounts\Models\Coupon;
use Modules\Discounts\DataView\Discounts;
use Modules\Discounts\Services\DiscountService;
use Modules\Discounts\Http\Requests\StoreDiscountRequest;
use Modules\Discounts\Http\Requests\UpdateDiscountRequest;

class DiscountController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(Discounts::class)->process();
        return view('discounts::discount.index', compact('lists'));
    }

    public function create()
    {
        return view('discounts::discount.form');
    }

    public function store(StoreDiscountRequest $request)
    {
        try {
            DB::beginTransaction();

            $discount = $this->discountService->createDiscount([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'amount' => $request->amount,
                'apply_to' => $request->apply_to,
                'is_active' => $request->boolean('is_active'),
                'starts_at' => $request->starts_at,
                'expires_at' => $request->expires_at,
                'user_groups' => $request->user_groups,
                'coupons' => $request->coupons,
                'rules' => $request->rules
            ]);

            if ($request->has('coupons')) {
                $this->handleCouponsUpdate($discount, $request->coupons);
            }

            if ($request->has('rules')) {
                $this->discountService->saveDiscountRules($discount, $request->rules);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Discount created successfully!',
                'redirect_url' => route('discount.index', $discount->id)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    public function edit(Discount $discount)
    {
        $discount->load(['coupons', 'rules']);
        return view('discounts::discount.form', compact('discount'));
    }

    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        try {
            DB::beginTransaction();

            $discount->update([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'amount' => $request->amount,
                'apply_to' => $request->apply_to,
                'is_active' => $request->boolean('is_active'),
                'starts_at' => $request->starts_at,
                'expires_at' => $request->expires_at,
                'user_groups' => $request->user_groups,
            ]);

            if ($request->has('coupons')) {
                $this->handleCouponsUpdate($discount, $request->coupons);
            }

            if ($request->has('rules')) {
                $this->discountService->saveDiscountRules($discount, $request->rules);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Discount updated successfully!',
                'discount_id' => $discount->id,
                'redirect_url' => route('discount.index')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

    protected function handleCouponsUpdate(Discount $discount, array $couponsData)
    {
        $existingCouponIds = $discount->coupons->pluck('id')->toArray();
        $submittedCouponIds = [];

        foreach ($couponsData as $couponData) {
            if (isset($couponData['id'])) {
                $coupon = Coupon::find($couponData['id']);
                if ($coupon) {
                    $coupon->update([
                        'code' => $couponData['code'],
                        'description' => $couponData['description'] ?? null,
                        'starts_at' => $couponData['starts_at'] ?? null,
                        'expires_at' => $couponData['expires_at'] ?? null,
                        'usage_limit' => $couponData['usage_limit'] ?? null,
                        'usage_limit_per_user' => $couponData['usage_limit_per_user'] ?? null,
                        'min_order_amount' => $couponData['min_order_amount'] ?? null,
                        'is_active' => $couponData['is_active'] ?? true,
                    ]);
                    $submittedCouponIds[] = $coupon->id;
                }
            } else {
                $coupon = $this->discountService->createCoupon([
                    'discount_id' => $discount->id,
                    'code' => $couponData['code'],
                    'description' => $couponData['description'] ?? null,
                    'starts_at' => $couponData['starts_at'] ?? null,
                    'expires_at' => $couponData['expires_at'] ?? null,
                    'usage_limit' => $couponData['usage_limit'] ?? null,
                    'usage_limit_per_user' => $couponData['usage_limit_per_user'] ?? null,
                    'min_order_amount' => $couponData['min_order_amount'] ?? null,
                    'is_active' => $couponData['is_active'] ?? true,
                ]);
                $submittedCouponIds[] = $coupon->id;
            }
        }

        $couponsToDelete = array_diff($existingCouponIds, $submittedCouponIds);
        if (!empty($couponsToDelete)) {
            Coupon::whereIn('id', $couponsToDelete)->delete();
        }
    }

    public function destroy(Discount $discount)
    {
        try {
            DB::transaction(function () use ($discount) {
                $discount->coupons()->delete();
                $discount->rules()->delete();
                $discount->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Discount deleted successfully',
                'redirect_url' => route('discount.index')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete discount: ' . $e->getMessage()
            ]);
        }
    }
}
