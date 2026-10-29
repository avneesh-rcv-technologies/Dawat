<?php

namespace Modules\FoodManagements\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FoodManagements\Services\FoodItemService;
use Modules\FoodManagements\DataView\FoodItemsGrid;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodTag;
use Modules\FoodManagements\Models\FoodOptionGroup;
use Modules\FoodManagements\Models\FoodFeature;
use App\Services\CacheService;


class FoodItemController extends Controller
{
    protected FoodItemService $service;

    public function __construct(FoodItemService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(FoodItemsGrid::class)->process();
        return view('foodmanagements::food_items.index', compact('lists'));
    }

    public function create(Request $request)
    {
        $foodItemId = $request->id;
        $restaurantId = $request->restaurant_id;
        $fromRestaurant = $request->from_restaurant;

        if (!$restaurantId && !$fromRestaurant) {
            $referer = $request->headers->get('referer');

            if ($referer && preg_match('/restaurant\/(\d+)\/edit/', $referer, $matches)) {
                $restaurantId = $matches[1];
                $fromRestaurant = true;
            }
        }
        if ($restaurantId && $fromRestaurant) {
            session([
                'food_item_return_url' => route('admin.restaurant.edit', $restaurantId) . '?tab=food',
                'food_item_restaurant_id' => $restaurantId
            ]);
        }

        $item = $foodItemId ? $this->service->getById($foodItemId) : null;

        if ($item) {
            $item->load(['categories', 'tags', 'optionGroups.options', 'featureValues']);
        }

        // Filter categories by restaurant ownership
        $currentAdmin = auth('admin')->user();

        $categories = FoodCategory::active()->with('parent');

        // Check session for restaurant context
        $sessionRestaurantId = session('food_item_restaurant_id');

        if ($currentAdmin) {

            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $currentAdmin->id)->first();

            if ($restaurant) {
                // Restaurant admin: show only their categories
                $categories = $categories->where('restaurant_id', $restaurant->id);
            } elseif ($sessionRestaurantId) {
                // Super admin editing from restaurant context
                $categories = $categories->where('restaurant_id', $sessionRestaurantId);
            }
            // Super admin (no restaurant context): show all categories
        }

        $categories = $categories->get();


        $tags = FoodTag::all();
        $optionGroups = FoodOptionGroup::with('options')->get();
        $featureGroups = FoodFeature::with([
            'description',
            'children.description',
            'children.variants.description',
        ])
            ->where('feature_type', 'G')
            ->orderBy('position')
            ->get();

        return view('foodmanagements::food_items.create', compact(
            'foodItemId',
            'item',
            'categories',
            'tags',
            'optionGroups',
            'featureGroups'
        ));
    }

    public function storeGeneral(Request $request)
    {
        $restaurantId = $request->input('restaurant_id') ?? session('food_item_restaurant_id');

        $data = $request->validate([
            'restaurant_id'     => 'nullable|exists:restaurants,id',
            'name'             => 'required|string|max:255',
            'sale_price'       => 'required|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'list_price'       => 'nullable|numeric|min:0',
            'in_stock'         => 'boolean',
            'quantity_type'    => 'required|in:manual,grouping,piece',
            'quantity'         => 'nullable|integer|min:0',
            'description'      => 'nullable|string',
            'long_description' => 'nullable|string',
            'is_active'        => 'boolean',
            'is_available'     => 'boolean',
            'food_type'        => 'required|in:veg,non_veg',
        ]);

        if ($restaurantId) {
            $data['restaurant_id'] = $restaurantId;
            session([
                'food_item_restaurant_id' => $restaurantId,
                'food_item_return_url' => route('admin.restaurant.edit', $restaurantId) . '?tab=food',
            ]);
        }

        $foodItem = $this->service->create($request, $data);

        return redirect()->route('admin.foodmanagements.food-items.create', [
            'id' => $foodItem->id,
            'restaurant_id' => $restaurantId,
            'from_restaurant' => $restaurantId ? 1 : null,
        ])->with('tab', 'categories')
            ->with('success', 'Food item saved successfully');
    }

    public function updateGeneral(Request $request, int $id)
    {
        $restaurantId = $request->input('restaurant_id') ?? session('food_item_restaurant_id');

        $data = $request->validate([
            'restaurant_id'     => 'nullable|exists:restaurants,id',
            'name'             => 'required|string|max:255',
            'sale_price'       => 'required|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'list_price'       => 'nullable|numeric|min:0',
            'in_stock'         => 'boolean',
            'quantity_type'    => 'required|in:manual,grouping,piece',
            'quantity'         => 'nullable|integer|min:0',
            'description'      => 'nullable|string',
            'long_description' => 'nullable|string',
            'is_active'        => 'boolean',
            'is_available'     => 'boolean',
            'food_type'        => 'required|in:veg,non_veg',
        ]);

        if ($restaurantId) {
            $data['restaurant_id'] = $restaurantId;
            session([
                'food_item_restaurant_id' => $restaurantId,
                'food_item_return_url' => route('admin.restaurant.edit', $restaurantId) . '?tab=food',
            ]);
        }

        $foodItem = $this->service->update($id, $request, $data);
        CacheService::forgetFoodItem($id);
        return redirect()->route('admin.foodmanagements.food-items.create', [
            'id' => $foodItem->id,
            'restaurant_id' => $restaurantId,
            'from_restaurant' => $restaurantId ? 1 : null,
        ])->with('tab', 'categories')
            ->with('success', 'Food item updated successfully');
    }

    /* ================= CATEGORIES ================= */

    public function updateCategories(Request $request, int $foodItemId)
    {
        $request->validate([
            'categories' => 'nullable|array',
            'categories.*' => 'exists:food_categories,id',
        ]);

        $foodItem = $this->service->getById($foodItemId);
        $foodItem->categories()->sync($request->categories ?? []);
        CacheService::forgetFoodItem($foodItemId);
        return back()->with([
            'success' => 'Categories updated successfully',
            'tab' => 'tags'
        ]);
    }



    /* ================= TAGS ================= */

    public function updateTags(Request $request, int $foodItemId)
    {
        $request->validate([
            'tags' => 'nullable|array',
            'tags.*' => 'exists:food_tags,id',
        ]);

        $foodItem = $this->service->getById($foodItemId);
        $foodItem->tags()->sync($request->tags ?? []);
        CacheService::forgetFoodItem($foodItemId);

        return back()->with([
            'success' => 'Tags updated successfully',
            'tab' => 'options'
        ]);
    }

    /* ================= OPTIONS ================= */

    public function updateOptions(Request $request, int $foodItemId)
    {
        $request->validate([
            'option_groups' => 'nullable|array',
            'option_groups.*' => 'exists:food_option_groups,id',
        ]);

        $foodItem = $this->service->getById($foodItemId);
        $foodItem->optionGroups()->sync($request->option_groups ?? []);
        CacheService::forgetFoodItem($foodItemId);
        return back()->with([
            'success' => 'Options updated successfully',
            'tab' => 'option_prices'
        ]);
    }

    /* ================= OPTION PRICES ================= */

    public function updateOptionPrices(Request $request, int $foodItemId)
    {
        $request->validate([
            'option_prices' => 'nullable|array',
            'option_prices.*' => 'numeric|min:0',
        ]);

        $foodItem = $this->service->getById($foodItemId);


        if ($request->option_prices) {
            foreach ($request->option_prices as $optionId => $price) {
                $foodItem->setOptionPrice($optionId, $price);
            }
        }
        CacheService::forgetFoodItem($foodItemId);
        return back()->with([
            'success' => 'Option prices updated successfully',
            'tab' => 'images'
        ]);
    }

    /* ================= FEATURES ================= */

    public function updateFeatures(Request $request, int $foodItemId)
    {
        $foodItem = $this->service->getById($foodItemId);

        // Delete all existing feature values for this food item
        \Modules\FoodManagements\Models\FoodFeatureValue::where('food_item_id', $foodItemId)->delete();

        $featureValues = $request->input('features', []);

        foreach ($featureValues as $featureId => $data) {
            if (empty($data)) continue;
            $values = is_array($data) ? $data : [$data];

            foreach ($values as $val) {
                if ($val === null || $val === '') continue;

                // Determine if val is a variant_id (numeric) or a plain value (text/number)
                $isVariant = is_numeric($val) && \Modules\FoodManagements\Models\FoodFeatureVariant::where('variant_id', $val)
                    ->where('feature_id', $featureId)
                    ->exists();

                \Modules\FoodManagements\Models\FoodFeatureValue::create([
                    'feature_id'   => $featureId,
                    'food_item_id' => $foodItemId,
                    'variant_id'   => $isVariant ? $val : null,
                    'value'        => $isVariant ? '' : (string) $val,
                    'lang_code'    => 'en',
                ]);
            }
        }
        CacheService::forgetFoodItem($foodItemId);


        return back()->with([
            'success' => 'Features updated successfully',
            'tab' => 'images',
        ]);
    }

    /* ================= IMAGES ================= */

    public function updateImages(Request $request, int $foodItemId)
    {
        $request->validate([
            'food_image' => 'nullable|array',
            'food_image.*' => 'file|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'food_image.*.file' => 'Each food item image must be a valid file.',
            'food_image.*.image' => 'Each uploaded file must be an image.',
            'food_image.*.mimes' => 'Each food item image must be a JPG, JPEG, or PNG file.',
            'food_image.*.max' => 'Each food item image may not be larger than 2 MB.',
        ]);

        $foodItem = $this->service->getById($foodItemId);
        if ($request->hasFile('food_image')) {
            foreach ($request->file('food_image') as $index => $image) {
                fn_upload_image($image, 'food_image', $foodItem->id, $index === 0);
            }
        }

        // Check if we should redirect back to restaurant page
        $returnUrl = session('food_item_return_url');

        if ($returnUrl) {
            // Clear session after use
            session()->forget(['food_item_return_url', 'food_item_restaurant_id']);
            return redirect($returnUrl)
                ->with('success', 'Food item saved successfully');
        }

        return redirect()
            ->route('admin.foodmanagements.food-items.index')
            ->with('success', 'Food item created successfully');
    }

    // public function show(int $id)
    // {
    //     $item = $this->service->getById($id);
    //     return view('foodmanagements::food_items.show', compact('item'));
    // }

    public function show(Request $request, int $id)
    {
        $item = $this->service->getById($id);

        // Detect restaurant context from referer or session
        $restaurantId = session('food_item_restaurant_id');

        if (!$restaurantId) {
            $referer = $request->headers->get('referer');
            if ($referer && preg_match('/restaurant\/(\d+)\/edit/', $referer, $matches)) {
                $restaurantId = $matches[1];
                // Set session for future use
                session([
                    'food_item_return_url' => route('admin.restaurant.edit', $restaurantId) . '?tab=food',
                    'food_item_restaurant_id' => $restaurantId
                ]);
            }
        }

        return view('foodmanagements::food_items.show', compact('item'));
    }
    // public function edit(int $id)
    // {
    //     return redirect()
    //         ->route('admin.foodmanagements.food-items.create', ['id' => $id]);
    // }
    public function edit(Request $request, int $id)
    {
        // Check referer to detect restaurant context (if not already in session)
        $restaurantId = session('food_item_restaurant_id');

        if (!$restaurantId) {
            $referer = $request->headers->get('referer');
            if ($referer && preg_match('/restaurant\/(\d+)\/edit/', $referer, $matches)) {
                $restaurantId = $matches[1];
                // Set session for edit flow
                session([
                    'food_item_return_url' => route('admin.restaurant.edit', $restaurantId) . '?tab=food',
                    'food_item_restaurant_id' => $restaurantId
                ]);
            }
        }

        // Pass restaurant context to create method
        if ($restaurantId) {
            return redirect()
                ->route('admin.foodmanagements.food-items.create', [
                    'id' => $id,
                    'restaurant_id' => $restaurantId,
                    'from_restaurant' => true
                ]);
        }

        return redirect()
            ->route('admin.foodmanagements.food-items.create', ['id' => $id]);
    }





    // public function destroy(int $id)
    // {
    //     $this->service->delete($id);
    //     CacheService::forgetFoodItem($id);
    //     $redirectUrl = $this->getRedirectUrl();
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Food item deleted successfully!',
    //         // 'redirect_url' => route('admin.foodmanagements.food-items.index'),
    //         'redirect_url' => $redirectUrl,
    //     ]);
    // }

    public function destroy(Request $request, int $id)
    {
        $this->service->delete($id);
        CacheService::forgetFoodItem($id);
        $redirectUrl = $this->getRedirectUrl($request);
        return response()->json([
            'success' => true,
            'message' => 'Food item deleted successfully!',
            'redirect_url' => $redirectUrl,
        ]);
    }

    // public function bulkDelete(Request $request)
    // {
    //     $request->validate([
    //         'ids'   => 'required|array|min:1',
    //         'ids.*' => 'integer|exists:food_items,id',
    //     ]);

    //     $this->service->bulkDelete($request->ids);
    //     foreach ($request->ids as $id) {
    //         CacheService::forgetFoodItem($id);
    //     }
    //     $redirectUrl = $this->getRedirectUrl();
    //     // return redirect()
    //     //     ->route('admin.foodmanagements.food-items.index')
    //     //     ->with('success', 'Food items deleted successfully!');
    //     return redirect($redirectUrl)
    //         ->with('success', 'Food items deleted successfully!');
    // }
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:food_items,id',
        ]);

        $this->service->bulkDelete($request->ids);
        foreach ($request->ids as $id) {
            CacheService::forgetFoodItem($id);
        }
        $redirectUrl = $this->getRedirectUrl($request);
        return redirect($redirectUrl)
            ->with('success', 'Food items deleted successfully!');
    }

    // public function toggleStatus(Request $request)
    // {
    //     $request->validate([
    //         'ids' => 'required|array|min:1',
    //         'ids.*' => 'integer|exists:food_items,id',
    //         'action' => 'required|boolean',
    //     ]);

    //     $this->service->bulkStatusUpdate(
    //         $request->ids,
    //         (bool) $request->action
    //     );


    //     foreach ($request->ids as $id) {
    //         CacheService::forgetFoodItem($id);
    //     }

    //     $redirectUrl = $this->getRedirectUrl();
    //     // return redirect()
    //     //     ->route('admin.foodmanagements.food-items.index')
    //     //     ->with('success', 'Status updated successfully!');
    //     return redirect($redirectUrl)
    //         ->with('success', 'Status updated successfully!');
    // }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:food_items,id',
            'action' => 'required|boolean',
        ]);

        $this->service->bulkStatusUpdate(
            $request->ids,
            (bool) $request->action
        );

        foreach ($request->ids as $id) {
            CacheService::forgetFoodItem($id);
        }

        $redirectUrl = $this->getRedirectUrl($request);

        return redirect($redirectUrl)
            ->with('success', 'Food items deleted successfully!');
    }



    // protected function getRedirectUrl()
    // {
    //     $restaurantId = session('food_item_restaurant_id');
    //     if ($restaurantId) {
    //         return route('admin.restaurant.edit', $restaurantId) . '?tab=food';
    //     }

    //     return route('admin.foodmanagements.food-items.index');
    // }


    protected function getRedirectUrl(Request $request = null)
    {
        // First check session
        $restaurantId = session('food_item_restaurant_id');

        // If no session, check referer header
        if (!$restaurantId && $request) {
            $referer = $request->headers->get('referer');
            if ($referer && preg_match('/restaurant\/(\d+)\/edit/', $referer, $matches)) {
                $restaurantId = $matches[1];
            }
        }

        if ($restaurantId) {
            return route('admin.restaurant.edit', $restaurantId) . '?tab=food';
        }

        return route('admin.foodmanagements.food-items.index');
    }


    protected function clearRestaurantContext()
    {
        session()->forget(['food_item_return_url', 'food_item_restaurant_id']);
    }
}
