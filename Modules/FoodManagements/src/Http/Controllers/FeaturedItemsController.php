<?php

namespace Modules\FoodManagements\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\FoodManagements\DataView\FeaturedItemsGrid;
use Modules\FoodManagements\Services\FeaturedItemService;
use Modules\FoodManagements\Services\CategoryService;

class FeaturedItemsController extends Controller
{
    protected FeaturedItemService $service;
    protected CategoryService $categoryService;

    public function __construct(
        FeaturedItemService $service,
        CategoryService $categoryService
    ) {
        $this->service = $service;
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $lists = fn_datagrid(FeaturedItemsGrid::class)->process();

        return view('foodmanagements::featured-items.index', compact('lists'));
    }

    public function create()
    {
        $foodItems = \Modules\FoodManagements\Models\FoodItem::with(['categories'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->get();
        $categories = $this->categoryService->getActiveCategoriesWithSubcategories();

        return view('foodmanagements::featured-items.create', compact('foodItems', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        
        $featuredItem = $this->service->create($data);

        return redirect()
            ->route('admin.foodmanagements.featured-items.index')
            ->with('success', 'Featured item created successfully!');
    }

    public function edit(int $id)
    {
        $featuredItem = $this->service->find($id);
        $foodItems = \Modules\FoodManagements\Models\FoodItem::with(['categories'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->get();
        $categories = $this->categoryService->getActiveCategoriesWithSubcategories();

        return view('foodmanagements::featured-items.edit', compact('featuredItem', 'foodItems', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validateRequest($request, $id);

        $this->service->update($id, $data);

        return redirect()
            ->route('admin.foodmanagements.featured-items.index')
            ->with('success', 'Featured item updated successfully!');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Featured item deleted successfully!',
            'redirect_url' => route('admin.foodmanagements.featured-items.index'),
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:featured_items,id',
        ]);

        $this->service->bulkDelete($request->ids);

        return redirect()
            ->route('admin.foodmanagements.featured-items.index')
            ->with('success', 'Featured items deleted successfully!');
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:featured_items,id',
        ]);

        // DataGrid might send 'value' instead of 'action'
        $status = $request->input('value', $request->input('action'));
        
        if ($status === null) {
            return redirect()
                ->route('admin.foodmanagements.featured-items.index')
                ->with('error', 'Status value is required!');
        }

        $this->service->bulkStatusUpdate(
            $request->ids,
            (bool) $status
        );

        return redirect()
            ->route('admin.foodmanagements.featured-items.index')
            ->with('success', 'Status updated successfully!');
    }

    /**
     * AJAX endpoint to get food items for selection
     */
    public function getFoodItems(Request $request)
    {
        $foodItems = $this->service->getAvailableFoodItems();

        return response()->json([
            'success' => true,
            'data' => $foodItems,
        ]);
    }

    /**
     * AJAX endpoint to get food categories for selection
     */
    public function getFoodCategories(Request $request)
    {
        $foodCategories = $this->service->getAvailableFoodCategories();

        return response()->json([
            'success' => true,
            'data' => $foodCategories,
        ]);
    }

    /* ================= PRIVATE ================= */

    private function validateRequest(Request $request, ?int $id = null): array
    {
        return Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'featured_type' => 'required|in:food_item,category',
            'selection_method' => 'required|in:custom,rating,most_buying,trending,new_arrivals',
            'item_limit' => 'nullable|integer|min:1|max:20',
            'food_items' => 'nullable|array',
            'food_items.*' => 'exists:food_items,id',
            'food_categories' => 'nullable|array',
            'food_categories.*' => 'exists:food_categories,id',
            'status' => 'boolean',
        ])->validate();
    }
}