<?php

namespace Modules\FoodManagements\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\FoodManagements\DataView\FoodEventsGrid;
use Modules\FoodManagements\Services\FoodEventService;
use Modules\FoodManagements\Services\CategoryService;

class FoodEventsController extends Controller
{
    protected FoodEventService $service;
    protected CategoryService $categoryService;

    public function __construct(
        FoodEventService $service,
        CategoryService $categoryService
    ) {
        $this->service = $service;
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $lists = fn_datagrid(FoodEventsGrid::class)->process();

        return view('foodmanagements::events.index', compact('lists'));
    }

    public function create(Request $request)
    {
        $eventId = $request->get('id');
        
        if ($eventId) {
            // Editing existing event
            $event = $this->service->find($eventId);
            $foodItems = \Modules\FoodManagements\Models\FoodItem::with(['categories'])->get();
            $categories = $this->categoryService->getActiveCategoriesWithSubcategories();
            
            return view('foodmanagements::events.create', [
                'eventId' => $eventId,
                'event' => $event,
                'foodItems' => $foodItems,
                'categories' => $categories,
            ]);
        }
        
        // Creating new event
        return view('foodmanagements::events.create', [
            'eventId' => null,
            'event' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        
        $event = $this->service->create($data);

        return redirect()
            ->route('admin.foodmanagements.events.create', ['id' => $event->id])
            ->with('tab', 'items')
            ->with('success', 'Event created successfully! Now select categories and items.');
    }

    public function edit(int $id)
    {
        return redirect()->route('admin.foodmanagements.events.create', ['id' => $id]);
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validateRequest($request, $id);

        $this->service->update($id, $data);

        return redirect()
            ->route('admin.foodmanagements.events.create', ['id' => $id])
            ->with('tab', 'items')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Food event deleted successfully!',
            'redirect_url' => route('admin.foodmanagements.events.index'),
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:food_events,id',
        ]);

        $this->service->bulkDelete($request->ids);

        return redirect()
            ->route('admin.foodmanagements.events.index')
            ->with('success', 'Food events deleted successfully!');
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:food_events,id',
            'action' => 'required|boolean',
        ]);

        $this->service->bulkStatusUpdate(
            $request->ids,
            (bool) $request->action
        );

        return redirect()
            ->route('admin.foodmanagements.events.index')
            ->with('success', 'Status updated successfully!');
    }

    /* ================= MANAGE ITEMS & CATEGORIES ================= */

    public function updateItems(Request $request, int $id)
    {
        $request->validate([
            'food_items' => 'nullable|array',
            'food_items.*' => 'exists:food_items,id',
        ]);

        $this->service->syncFoodItems($id, $request->food_items ?? []);

        return redirect()
            ->route('admin.foodmanagements.events.create', ['id' => $id])
            ->with('tab', 'items')
            ->with('success', 'Food items updated successfully!');
    }

    public function updateCategories(Request $request, int $id)
    {
        $request->validate([
            'food_categories' => 'nullable|array',
            'food_categories.*' => 'exists:food_categories,id',
        ]);

        $this->service->syncFoodCategories($id, $request->food_categories ?? []);

        return redirect()
            ->route('admin.foodmanagements.events.create', ['id' => $id])
            ->with('tab', 'items')
            ->with('success', 'Food categories updated successfully!');
    }

    /* ================= PRIVATE ================= */

    private function validateRequest(Request $request, ?int $id = null): array
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:food_events,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:food_events,slug,' . $id,
            'description' => 'nullable|string|max:1000',
            'status' => 'boolean',
        ])->validate();
    }
}