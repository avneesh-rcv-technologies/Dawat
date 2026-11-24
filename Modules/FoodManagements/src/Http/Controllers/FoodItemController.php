<?php

namespace Modules\FoodManagements\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\FoodManagements\Http\Requests\SaveFoodItemRequest;
use Illuminate\Routing\Controller;
use Modules\FoodManagements\Services\FoodItemService;
use Modules\FoodManagements\Services\FoodImageUploadPolicy;
use Modules\FoodManagements\DataView\FoodItemsGrid;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodTag;
use Modules\FoodManagements\Models\FoodOptionGroup;
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
            $item->load(['categories', 'tags', 'optionGroups.options', 'featureValues', 'servingOptions']);
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
                $categories = $categories->where('restaurant_id', $item?->restaurant_id ?? $restaurantId ?? $sessionRestaurantId ?? $restaurant->id);
            } elseif ($sessionRestaurantId) {
                // Super admin editing from restaurant context
                $categories = $categories->where('restaurant_id', $sessionRestaurantId);
            }
            // Super admin (no restaurant context): show all categories
        }

        $categories = $categories->get();


        $tags = FoodTag::all();
        $optionGroups = FoodOptionGroup::with('options')->get();
        $certificateRestaurantId = $item?->restaurant_id ?? $restaurantId ?? $sessionRestaurantId ?? ($restaurant->id ?? null);

        // Features are restaurant-owned: only the item's restaurant's features apply.
        $featureGroups = app(\Modules\FoodManagements\Services\FoodFeatureService::class)
            ->itemFeatureSections($certificateRestaurantId ? (int) $certificateRestaurantId : null);
        $labelEligibility = app(\Modules\DietaryManagment\Services\LabelCertificateEligibility::class)->forRestaurant($certificateRestaurantId ? (int) $certificateRestaurantId : null);

        return view('foodmanagements::food_items.create', compact(
            'foodItemId', 'certificateRestaurantId', 'labelEligibility',
            'item',
            'categories',
            'tags',
            'optionGroups',
            'featureGroups'
        ));
    }

    public function storeGeneral(SaveFoodItemRequest $request)
    {
        $restaurantId = $request->input('restaurant_id') ?? session('food_item_restaurant_id');

        $data = $request->validated();

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
        ])->with('tab', 'general')
            ->with('success', 'Food item saved successfully');
    }

    public function updateGeneral(SaveFoodItemRequest $request, int $id)
    {
        $restaurantId = $request->input('restaurant_id') ?? session('food_item_restaurant_id');

        $data = $request->validated();

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
        ])->with('tab', 'general')
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
        $this->service->getById($foodItemId);
        $this->service->syncFeatures($foodItemId, (array) $request->input('features', []));
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
            'food_image' => ['nullable', 'array', 'list', 'max:'.FoodImageUploadPolicy::MAX_IMAGES],
            'food_image.*' => FoodImageUploadPolicy::fileRules(),
        ], FoodImageUploadPolicy::messages('food_image'));

        $foodItem = $this->service->getById($foodItemId);
        if ($request->hasFile('food_image')) {
            $this->service->appendImages($foodItem, $request->file('food_image'));
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

        $item->loadMissing([
            'restaurant', 'servingOptions', 'featureValues.feature.description',
            'featureValues.feature.parent.description', 'featureValues.variant.description',
        ]);
        // The item's actual restaurant is authoritative, regardless of URL or session context.
        $restaurant = $item->restaurant;
        $dietaryLabels = $item->dietary_label_ids
            ? DB::table('dietary_labels')->whereIn('id', $item->dietary_label_ids)->orderBy('display_order')->get()
            : collect();
        $images = collect($item->images)->filter(fn ($image) => !empty($image['url'])
            && !in_array($image['url'], ['/dawat.png', asset('dawat.png')], true))->values();
        $featureGroups = $item->featureValues->filter(fn ($value) => $value->feature)
            ->groupBy(fn ($value) => $value->feature->parent?->description?->name ?: 'Other features')
            ->map(fn ($values) => $values->groupBy('feature_id')->map(function ($entries) {
                $feature = $entries->first()->feature;
                $values = $entries->map(function ($entry) use ($feature) {
                    if ($feature->feature_type === 'C') {
                        return in_array(strtolower((string) $entry->value), ['y', 'yes', '1', 'true'], true) ? 'Yes' : 'No';
                    }
                    return $entry->variant?->description?->description
                        ?? ($entry->value !== '' ? $entry->value : $entry->value_int);
                })->filter(fn ($value) => $value !== null && $value !== '')->unique();
                return ['name' => $feature->description?->name ?: $feature->feature_code, 'value' => $values->implode(', ')];
            })->values());

        return view('foodmanagements::food_items.show', compact('item', 'restaurant', 'dietaryLabels', 'images', 'featureGroups'));
    }
    // public function edit(int $id)
    // {
    //     return redirect()
    //         ->route('admin.foodmanagements.food-items.create', ['id' => $id]);
    // }
    public function edit(Request $request, int $id)
    {
        // Check referer to detect restaurant context (if not already in session)
        $restaurantId = $request->input('restaurant_id') ?? session('food_item_restaurant_id');

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
