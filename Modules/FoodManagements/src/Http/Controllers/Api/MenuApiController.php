<?php

namespace Modules\FoodManagements\Http\Controllers\Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Models\FoodItemServingOption;
use Modules\FoodManagements\Services\CategoryService;
use Modules\FoodManagements\Services\FoodItemService;
use Modules\FoodManagements\Services\FoodTagService;
use Modules\FoodManagements\Services\FoodOptionGroupService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class MenuApiController extends Controller
{
    protected CategoryService $categoryService;
    protected FoodItemService $foodItemService;
    protected FoodTagService $tagService;
    protected FoodOptionGroupService $optionGroupService;

    public function __construct(
        CategoryService $categoryService,
        FoodItemService $foodItemService,
        FoodTagService $tagService,
        FoodOptionGroupService $optionGroupService
    ) {
        $this->categoryService = $categoryService;
        $this->foodItemService = $foodItemService;
        $this->tagService = $tagService;
        $this->optionGroupService = $optionGroupService;
    }

    /**
     * Get active root categories with subcategories
     * GET /api/menu/categories?restaurant_id=1
     */
    public function getCategories(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
        ]);

        try {
            $categories = $this->categoryService->getActiveCategoriesWithSubcategories(
                $request->integer('restaurant_id') ?: null
            );

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get food items by category (including subcategories)
     * GET /api/menu/categories/{categoryId}/items
     */
    public function getFoodItemsByCategory(Request $request, int $categoryId): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
            'food_type'     => 'nullable|in:all,veg,non_veg,vegan',
            'min_price'     => 'nullable|numeric|min:0',
            'max_price'     => 'nullable|numeric|min:0',
            'tags'          => 'nullable|array',
            'tags.*'        => 'integer|exists:food_tags,id',
            'variant_id'    => 'nullable|integer|exists:food_feature_variants,variant_id',
            'page'          => 'nullable|integer|min:1',
            'per_page'      => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $filters = [
                'restaurant_id' => $request->integer('restaurant_id') ?: null,
                'food_type'     => $request->get('food_type'),
                'min_price'     => $request->get('min_price'),
                'max_price'     => $request->get('max_price'),
                'tags'          => $request->get('tags', []),
                'variant_id'    => $request->integer('variant_id') ?: null,
            ];

            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $result = $this->foodItemService->getFoodItemsByCategory($categoryId, $filters, $perPage, $page);
            // dd($result);

            return response()->json([
                'success' => true,
                'data' => $result['items'],
                'meta' => $result['meta'],
                'message' => 'Food items retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get food item details with all relations
     * GET /api/menu/item/{itemId}
     */
    public function getFoodItemDetails(int $itemId): JsonResponse
    {
        try {
            $item = $this->foodItemService->getFoodItemWithDetails($itemId);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Food item details retrieved successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'This food item is no longer available. Please refresh the menu and choose another item.',
            ], 404);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get food items filtered by one or more dietary label ids.
     * GET /api/menu/dietary-labels/items?dietary_label_ids[]=1&dietary_label_ids[]=2
     */
    public function getFoodItemsByDietaryLabels(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
            'dietary_label_ids' => 'nullable|array',
            'dietary_label_ids.*' => 'integer|exists:dietary_labels,id',
            'food_type' => 'nullable|in:all,veg,non_veg,vegan',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:food_tags,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $dietaryLabelIds = collect($request->input('dietary_label_ids', []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $filters = [
                'restaurant_id' => $request->integer('restaurant_id') ?: null,
                'food_type' => $request->get('food_type'),
                'min_price' => $request->get('min_price'),
                'max_price' => $request->get('max_price'),
                'tags' => $request->get('tags', []),
            ];

            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $result = $this->foodItemService->getFoodItemsByDietaryLabels($dietaryLabelIds, $filters, $perPage, $page);

            return response()->json([
                'success' => true,
                'data' => $result['items'],
                'meta' => $result['meta'],
                'message' => 'Food items retrieved successfully',
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Get food items by tag
     * GET /api/menu/tags/{tagId}/items
     */
    public function getFoodItemsByTag(Request $request, int $tagId): JsonResponse
    {
        $request->validate([
            'food_type' => 'nullable|in:all,veg,non_veg,vegan',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        try {
            $filters = [
                'food_type' => $request->get('food_type'),
                'min_price' => $request->get('min_price'),
                'max_price' => $request->get('max_price')
            ];

            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $result = $this->foodItemService->getFoodItemsByTag($tagId, $filters, $perPage, $page);

            return response()->json([
                'success' => true,
                'data' => $result['items'],
                'meta' => $result['meta'],
                'message' => 'Food items retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Calculate final price based on selected serving option and quantity.
     * POST /api/menu/items/price-preview
     */
    public function pricePreview(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
            'item_id' => 'required|integer|exists:food_items,id',
            'serving_option_id' => 'required|integer|exists:food_item_serving_options,id',
            'quantity' => 'required|numeric|min:1',
            'guest_count' => 'nullable|integer|min:1',
        ]);

        try {
            $item = FoodItem::with('servingOptions')->findOrFail($request->integer('item_id'));
            $option = $item->servingOptions()->findOrFail($request->integer('serving_option_id'));

            if ($request->filled('restaurant_id') && (int) $request->integer('restaurant_id') !== (int) $item->restaurant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected item does not belong to the provided restaurant.',
                ], 422);
            }

            $serveType = $option->serve_type ?? 'package';
            $quantity = (int) $request->input('quantity', 1);
            $guestCount = $request->filled('guest_count') ? (int) $request->input('guest_count') : null;
            $errors = [];

            if ($serveType === 'per_person') {
                if ($guestCount === null) {
                    $errors[] = 'guest_count is required for per person serving options.';
                }

                if ($guestCount !== null && $option->min_guests !== null && $guestCount < (int) $option->min_guests) {
                    $errors[] = 'guest_count is below the minimum allowed for this serving option.';
                }

                if ($guestCount !== null && $option->max_guests !== null && $guestCount > (int) $option->max_guests) {
                    $errors[] = 'guest_count exceeds the maximum allowed for this serving option.';
                }
            }

            $basePrice = (float) ($option->price ?? 0);
            $discountPrice = $option->discount_price !== null && $option->discount_price !== ''
                ? (float) $option->discount_price
                : null;
            $unitPrice = $discountPrice !== null && $discountPrice > 0 ? $discountPrice : $basePrice;
            $perPersonPrice = $this->calculatePerPersonPrice($option, $unitPrice);

            $billableUnits = $serveType === 'per_person'
                ? ($guestCount !== null ? $guestCount * $quantity : 0)
                : $quantity;

            $totalServes = ($option->serves !== null && $option->serves !== '')
                ? (int) $option->serves * $quantity
                : null;

            if ($serveType === 'per_person') {
                $totalServes = $billableUnits;
            }

            $subtotal = round($unitPrice * $billableUnits, 2);
            $taxPercentage = (float) ($item->tax_percentage ?? 0);
            $taxAmount = round(($subtotal * $taxPercentage) / 100, 2);
            $finalPrice = round($subtotal + $taxAmount, 2);

            $validation = [
                'is_valid' => empty($errors),
                'can_add_to_cart' => empty($errors),
                'errors' => $errors,
            ];

            $displayTitle = $option->serving_name ?: ($option->serve_type ? ucfirst(str_replace('_', ' ', $option->serve_type)) : 'Serving option');
            $displaySubtitle = $this->buildServingDisplaySubtitle($option, $serveType);
            $quantityLabel = $this->buildQuantityLabel($option, $quantity, $serveType);
            $totalServesLabel = $totalServes !== null ? 'Serves '.$totalServes : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'item_id' => $item->id,
                    'restaurant_id' => $item->restaurant_id,
                    'item_name' => $item->name,
                    'serving_option' => [
                        'id' => $option->id,
                        'serve_type' => $option->serve_type,
                        'serve_type_label' => $option->serve_type ? ucfirst(str_replace('_', ' ', $option->serve_type)) : null,
                        'serving_name' => $option->serving_name,
                        'serves' => $option->serves !== null ? (int) $option->serves : null,
                        'min_guests' => $option->min_guests !== null ? (int) $option->min_guests : null,
                        'max_guests' => $option->max_guests !== null ? (int) $option->max_guests : null,
                        'is_available' => (bool) $option->is_available,
                    ],
                    'selection' => [
                        'quantity' => $quantity,
                        'guest_count' => $serveType === 'per_person' ? ($guestCount !== null ? $guestCount : null) : null,
                        'total_serves' => $totalServes,
                    ],
                    'pricing' => [
                        'currency' => 'USD',
                        'base_price' => $this->decimalString($basePrice),
                        'discount_price' => $discountPrice !== null ? $this->decimalString($discountPrice) : null,
                        'unit_price_used' => $this->decimalString($unitPrice),
                        'per_person_price' => $this->decimalString($perPersonPrice),
                        'subtotal' => $this->decimalString($subtotal),
                        'tax_percentage' => $this->decimalString($taxPercentage),
                        'tax_amount' => $this->decimalString($taxAmount),
                        'final_price' => $this->decimalString($finalPrice),
                    ],
                    'display' => [
                        'title' => $displayTitle,
                        'subtitle' => $displaySubtitle,
                        'quantity_label' => $quantityLabel,
                        'total_serves_label' => $totalServesLabel,
                        'unit_price_text' => $this->formatCurrency($unitPrice),
                        'subtotal_text' => $this->formatCurrency($subtotal),
                        'total_text' => $this->formatCurrency($finalPrice),
                    ],
                    'validation' => $validation,
                ],
                'message' => 'Price preview calculated successfully',
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'success' => false,
                'errors' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    /**
     * Bulk price preview for multiple item + serving option combinations.
     * POST /api/menu/items/price-preview/bulk
     */
    protected function formatPrice(float $value): string
    {
        $currencySymbol = fn_format_default_currency() ?: '$';

        return $currencySymbol.number_format($value, 2, '.', '');
    }

    protected function decimalString(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    protected function formatCurrency(float $value): string
    {
        $currencySymbol = fn_format_default_currency() ?: '$';

        return $currencySymbol.number_format($value, 2, '.', '');
    }

    protected function calculatePerPersonPrice($option, float $unitPrice): float
    {
        $serves = (float) ($option->serves ?? 0);

        if ($serveType = $option->serve_type ?? null) {
            if ($serveType === 'per_person') {
                return round($unitPrice, 2);
            }
        }

        if ($serves > 0) {
            return round($unitPrice / $serves, 2);
        }

        return round($unitPrice, 2);
    }

    protected function buildServingDisplaySubtitle($option, string $serveType): string
    {
        if ($serveType === 'per_person') {
            return 'Per guest';
        }

        if ($option->serves !== null && $option->serves !== '') {
            $label = $serveType === 'package' ? 'per package' : 'per '.$serveType;
            return 'Serves '.$option->serves.' '.$label;
        }

        return ucfirst($serveType).' pricing';
    }

    protected function buildQuantityLabel($option, int $quantity, string $serveType): string
    {
        $unitName = $option->serving_name ?: $serveType;

        if ($serveType === 'per_person') {
            return $quantity.' '.($quantity === 1 ? 'guest' : 'guests');
        }

        if ($serveType === 'package') {
            return $quantity.' '.($quantity === 1 ? 'package' : 'packages');
        }

        if ($serveType === 'tray') {
            return $quantity.' '.($quantity === 1 ? 'tray' : 'trays');
        }

        return $quantity.' '.($quantity === 1 ? $unitName : $unitName.'s');
    }

    public function bulkPricePreview(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:food_items,id',
            'items.*.serving_option_id' => 'required|integer|exists:food_item_serving_options,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        try {
            $items = [];
            $grandTotal = 0;

            foreach ($request->input('items', []) as $entry) {
                $item = FoodItem::with('servingOptions')->findOrFail($entry['item_id']);
                $option = $item->servingOptions()->findOrFail($entry['serving_option_id']);

                if ($request->filled('restaurant_id') && (int) $request->integer('restaurant_id') !== (int) $item->restaurant_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more items do not belong to the provided restaurant.',
                    ], 422);
                }

                $quantity = (float) $entry['quantity'];
                $basePrice = (float) ($option->price ?? 0);
                $discountPrice = $option->discount_price !== null && $option->discount_price !== ''
                    ? (float) $option->discount_price
                    : null;
                $unitPrice = $discountPrice !== null && $discountPrice > 0 ? $discountPrice : $basePrice;
                $perPersonPrice = $this->calculatePerPersonPrice($option, $unitPrice);
                $subtotal = round($unitPrice * $quantity, 2);
                $taxPercentage = (float) ($item->tax_percentage ?? 0);
                $taxAmount = round(($subtotal * $taxPercentage) / 100, 2);
                $finalPrice = round($subtotal + $taxAmount, 2);
                $grandTotal += $finalPrice;
                $priceMeta = $this->buildServingPriceMeta($option, $unitPrice, $quantity, $perPersonPrice);

                $items[] = [
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $quantity,
                    'serving_option_id' => $option->id,
                    'serving_option_name' => $option->serving_name,
                    'base_price' => $basePrice,
                    'discount_price' => $discountPrice,
                    'unit_price_used' => $unitPrice,
                    'per_person_price' => $perPersonPrice,
                    'subtotal' => $subtotal,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'final_price' => $finalPrice,
                    'price_label' => $priceMeta['price_label'],
                    'unit_label' => $priceMeta['unit_label'],
                    'display_price' => $this->formatPrice($unitPrice),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'grand_total' => round($grandTotal, 2),
                ],
                'message' => 'Bulk price preview calculated successfully',
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'success' => false,
                'errors' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    /**
     * Get option groups for cart screen
     * GET /api/menu/option-groups
     */
    public function getOptionGroups(Request $request): JsonResponse
    {
        $request->validate([
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'integer|exists:food_items,id'
        ]);

        try {
            $itemIds = $request->get('item_ids', []);
            $optionGroups = $this->optionGroupService->getOptionGroupsForCart($itemIds);

            return response()->json([
                'success' => true,
                'data' => $optionGroups,
                'message' => 'Option groups retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Combined menu API optimized for frontend
     * GET /api/menu/complete
     */
    public function getCompleteMenu(Request $request): JsonResponse
    {
        $request->validate([
            'food_type' => 'nullable|in:all,veg,non_veg,vegan',
            'include_items' => 'nullable|boolean'
        ]);

        try {
            $foodType = $request->get('food_type');
            $includeItems = $request->get('include_items', false);

            $menu = $this->categoryService->getCompleteMenu($foodType, $includeItems);

            return response()->json([
                'success' => true,
                'data' => $menu,
                'message' => 'Complete menu retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);

            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }
}
