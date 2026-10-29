<?php

namespace Modules\Tax\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Tax\Services\FoodItemTaxService;

class FoodItemTaxController extends Controller
{
    protected FoodItemTaxService $taxService;

    public function __construct(FoodItemTaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Calculate tax for a specific food item
     */
    public function calculateTax(Request $request, int $foodItemId): JsonResponse
    {
        try {
            $foodItem = FoodItem::findOrFail($foodItemId);
            $customTaxCategoryIds = $request->input('tax_category_ids');
            
            if (is_string($customTaxCategoryIds)) {
                $customTaxCategoryIds = json_decode($customTaxCategoryIds, true) ?: [];
            }
            
            $calculation = $this->taxService->calculateDisplayPrice($foodItem, $customTaxCategoryIds);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'food_item' => [
                        'id' => $foodItem->id,
                        'name' => $foodItem->name,
                        'base_price' => $foodItem->sale_price,
                    ],
                    'tax_calculation' => $calculation,
                    'breakdown' => $this->taxService->getTaxBreakdown($foodItem, $customTaxCategoryIds),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating tax: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate tax for multiple food items (cart simulation)
     */
    public function calculateCartTax(Request $request): JsonResponse
    {
        try {
            $items = $request->input('items', []);
            $cartItems = [];

            foreach ($items as $item) {
                $foodItem = FoodItem::find($item['food_item_id']);
                if ($foodItem) {
                    $taxCategoryIds = $item['tax_category_ids'] ?? null;
                    if (is_string($taxCategoryIds)) {
                        $taxCategoryIds = json_decode($taxCategoryIds, true) ?: [];
                    }
                    
                    $cartItems[] = [
                        'food_item' => $foodItem,
                        'quantity' => $item['quantity'] ?? 1,
                        'tax_category_ids' => $taxCategoryIds,
                    ];
                }
            }

            $cartTaxCalculation = $this->taxService->calculateCartTax($cartItems);

            return response()->json([
                'success' => true,
                'data' => $cartTaxCalculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating cart tax: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current tax configuration
     */
    public function getTaxConfiguration(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'calculation_type' => fn_get_setting('tax.calculation_type', 'exclusive'),
                'application_level' => fn_get_setting('tax.application_level', 'food_item'),
                'default_category_ids' => fn_get_default_tax_category_ids(),
                'is_tax_inclusive' => fn_is_tax_inclusive(),
                'is_item_level_tax' => fn_is_item_level_tax(),
                'is_subtotal_level_tax' => fn_is_subtotal_level_tax(),
                'calculation_type_enum' => fn_get_tax_calculation_type(),
                'application_level_enum' => fn_get_tax_application_level(),
                'available_tax_categories' => fn_get_available_tax_categories(),
                'default_tax_categories' => fn_get_default_tax_categories(),
            ]
        ]);
    }

    /**
     * Calculate subtotal tax
     */
    public function calculateSubtotalTax(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'subtotal' => 'required|numeric|min:0',
                'tax_category_ids' => 'nullable|array',
                'tax_category_ids.*' => 'exists:tax_categories,id',
            ]);

            $taxCategoryIds = $request->tax_category_ids;
            if (is_string($taxCategoryIds)) {
                $taxCategoryIds = json_decode($taxCategoryIds, true) ?: [];
            }

            $calculation = $this->taxService->calculateSubtotalTax(
                $request->subtotal,
                $taxCategoryIds
            );
            
            return response()->json([
                'success' => true,
                'data' => $calculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating subtotal tax: ' . $e->getMessage()
            ], 500);
        }
    }
}