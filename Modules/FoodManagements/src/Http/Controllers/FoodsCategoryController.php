<?php

namespace Modules\FoodManagements\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\FoodManagements\DataView\FoodsCategoryGrid;
use Modules\FoodManagements\Services\CategoryService;
use Modules\Restaurant\Models\Restaurant;

class FoodsCategoryController extends Controller
{
    protected CategoryService $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $restaurantId = $request->restaurant_id;
        $fromRestaurant = $request->from_restaurant;

        // Auto-detect restaurant context from referrer if not explicitly provided
        if (!$restaurantId && !$fromRestaurant) {
          
            $referer = $request->headers->get('referer');
            if ($referer && preg_match('/restaurant\/(\d+)\/edit/', $referer, $matches)) {
                $restaurantId = $matches[1];
                $fromRestaurant = true;
            }
        }
    
        // Store restaurant context in session if coming from restaurant page
        if ($restaurantId && $fromRestaurant) {
            session([
                'category_return_url' => route('admin.restaurant.edit', $restaurantId) . '?tab=food',
                'category_restaurant_id' => $restaurantId
            ]);
        }

        $lists = fn_datagrid(FoodsCategoryGrid::class)->process();

        return view('foodmanagements::category.index', compact('lists'));
    }

    public function create()
    {
        $currentAdmin = auth('admin')->user();
        $isRestaurantAdmin = $currentAdmin->role_id == 2;

        // Check if coming from restaurant context
        $restaurantContext = session('category_restaurant_id');
        $restaurantId = $restaurantContext;

        if ($isRestaurantAdmin) {
            $restaurant = Restaurant::where('admin_id', $currentAdmin->id)->first();
            $restaurantId = $restaurant?->id ?? $restaurantContext;
        }

        return view('foodmanagements::category.form', [
            'parents'            => $this->service->getParentCategories($restaurantId),
            'restaurants'        => $isRestaurantAdmin ? collect() : Restaurant::orderBy('name')->get(['id', 'name']),
            'is_restaurant_admin' => $isRestaurantAdmin,
            'restaurant_context' => $restaurantContext,
            'action'             => route('admin.foodmanagements.category.store'),
            'method'             => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        $category = $this->service->create($data);
        if ($request->hasFile('category_image')) {
            fn_upload_image($request->file('category_image'), 'category_image', $category->id, true);
        }

        return response()->json([
            'success' => true,
            'message' => $data['parent_id']
                ? 'Sub-category created successfully!'
                : 'Category created successfully!',
            'redirect_url' => route('admin.foodmanagements.category.index'),
        ]);
    }

    public function edit(int $id)
    {
        $category = $this->service->find($id);
        $currentAdmin = auth('admin')->user();
        $isRestaurantAdmin = $currentAdmin->role_id == 2;

        // Check if coming from restaurant context
        $restaurantContext = session('category_restaurant_id');
        $restaurantId = $restaurantContext ?? $category->restaurant_id;

        if ($isRestaurantAdmin) {
            $restaurant = Restaurant::where('admin_id', $currentAdmin->id)->first();
            $restaurantId = $restaurant?->id ?? $restaurantId;
        }

        return view('foodmanagements::category.form', [
            'category'            => $category,
            'parents'             => $this->service->getParentCategories($restaurantId)->where('id', '!=', $id),
            'restaurants'         => $isRestaurantAdmin ? collect() : Restaurant::orderBy('name')->get(['id', 'name']),
            'is_restaurant_admin' => $isRestaurantAdmin,
            'restaurant_context' => $restaurantContext,
            'action'              => route('admin.foodmanagements.category.update', $id),
            'method'              => 'PUT',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validateRequest($request, $id);

        $this->service->update($id, $data);

        if ($request->hasFile('category_image')) {
            fn_upload_image($request->file('category_image'), 'category_image', $id, true);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!',
            'redirect_url' => route('admin.foodmanagements.category.index'),
        ]);
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!',
            'redirect_url' => route('admin.foodmanagements.category.index'),
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:food_categories,id',
        ]);

        $this->service->bulkDelete($request->ids);

        return redirect()
            ->route('admin.foodmanagements.category.index')
            ->with('success', 'Categories deleted successfully!');
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:food_categories,id',
            'action' => 'required|boolean',
        ]);

        $this->service->bulkStatusUpdate(
            $request->ids,
            (bool) $request->action
        );

        return redirect()
            ->route('admin.foodmanagements.category.index')
            ->with('success', 'Status updated successfully!');
    }

    /* ================= PRIVATE ================= */

    private function validateRequest(Request $request, ?int $id = null): array
    {
        $input = $request->all();
        $currentAdmin = auth('admin')->user();

        // Resolve the restaurant before checking uniqueness or saving the category.
        if ($currentAdmin->role_id == 2) {
            $input['restaurant_id'] = Restaurant::where('admin_id', $currentAdmin->id)->value('id');
        } else {
            $input['restaurant_id'] = session('category_restaurant_id') ?? ($input['restaurant_id'] ?? null);
        }

        $restaurant = Validator::make($input, [
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
        ])->validate();
        $restaurantId = $restaurant['restaurant_id'] ?? null;

        $data = Validator::make($input, [
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'name'          => [
                'required', 'string', 'max:255',
                Rule::unique('food_categories', 'name')
                    ->where(fn ($query) => $query->where('restaurant_id', $restaurantId))
                    ->ignore($id),
            ],
            'slug'          => [
                'nullable', 'string', 'max:255',
                Rule::unique('food_categories', 'slug')
                    ->where(fn ($query) => $query->where('restaurant_id', $restaurantId))
                    ->ignore($id),
            ],
            'description'   => 'nullable|string|max:255',
            'food_type'     => 'required|in:all,veg,non_veg,vegan',
            'parent_id'     => 'nullable|exists:food_categories,id',
            'status'        => 'nullable|boolean',
            'category_image' => 'nullable|file|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'category_image.file' => 'The category image must be a valid file.',
            'category_image.image' => 'The uploaded file must be an image.',
            'category_image.mimes' => 'The category image must be a JPG, JPEG, or PNG file.',
            'category_image.max' => 'The category image may not be larger than 2 MB.',
        ])->validate();

        // Boolean fields sent as "1"/"0" via a select; default to active (true) if missing
        $data['status'] = isset($data['status']) ? (bool) $data['status'] : true;

        return $data;
    }
}
