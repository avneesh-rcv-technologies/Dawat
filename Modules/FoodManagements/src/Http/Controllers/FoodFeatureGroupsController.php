<?php

namespace Modules\FoodManagements\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\FoodManagements\DataView\FoodFeatureGroupGrid;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Services\FoodFeatureService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Restaurant\Models\Restaurant;
use Modules\FoodManagements\Http\Controllers\Concerns\ScopesFeaturesToOwner;

class FoodFeatureGroupsController extends Controller
{
    use ScopesFeaturesToOwner;

    public function __construct(
        protected FoodFeatureService $service
    ) {}

    public function index()
    {
        $lists = fn_datagrid(FoodFeatureGroupGrid::class)->process();

        return view('foodmanagements::food_features.groups.index', compact('lists'));
    }

    public function create()
    {
        return view('foodmanagements::food_features.groups.form', $this->formLookups());
    }

    public function store(Request $request)
    {
        try {
            $ownerRestaurantId = $this->ownerRestaurantId();
            $rules = $this->rules();
            if (!$ownerRestaurantId) {
                $rules['restaurant_id'] = 'required|integer|exists:restaurants,id';
            }
            $validator = Validator::make($request->all(), $rules, ['restaurant_id.required' => 'Choose the restaurant this feature group belongs to.']);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->create(array_merge($request->all(), [
                'feature_type' => 'G',
                'parent_id' => 0,
                'restaurant_id' => $ownerRestaurantId ?: (int) $request->input('restaurant_id'),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Feature group created successfully!',
                'redirect_url' => route('admin.foodmanagements.features.group_index'),
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(FoodFeature $group)
    {
        if ($group->feature_type !== 'G') abort(404);
        $this->authorizeOwner($group);

        $group->load(['description', 'categories', 'restaurant']);

        return view('foodmanagements::food_features.groups.form', array_merge(
            $this->formLookups(),
            ['group' => $group]
        ));
    }

    public function update(Request $request, FoodFeature $group)
    {
        try {
            if ($group->feature_type !== 'G') abort(404);
            $this->authorizeOwner($group);

            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->update($group, array_merge($request->all(), ['feature_type' => 'G', 'parent_id' => 0]));

            return response()->json([
                'success' => true,
                'message' => 'Feature group updated successfully!',
                'redirect_url' => route('admin.foodmanagements.features.group_index'),
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(FoodFeature $group)
    {
        try {
            if ($group->feature_type !== 'G') abort(404);
            $this->authorizeOwner($group);

            $this->service->delete($group);

            return response()->json([
                'success' => true,
                'message' => 'Feature group deleted successfully!',
                'redirect_url' => route('admin.foodmanagements.features.group_index'),
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids'   => 'required|array|min:1',
                'ids.*' => 'exists:food_features,feature_id',
            ]);

            $this->service->bulkDelete($this->ownedIds($request->ids));

            if ($request->input('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Selected feature groups deleted successfully!',
                    'redirect_url' => route('admin.foodmanagements.features.group_index'),
                ]);
            }

            return redirect()->route('admin.foodmanagements.features.group_index')->with('success', 'Selected feature groups deleted successfully!');
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    private function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'feature_code'       => 'nullable|string|max:32',
            'display_on_product' => 'nullable',
            'display_on_catalog' => 'nullable',
            'display_on_header'  => 'nullable',
            'status'             => 'nullable|string|max:1',
            'position'           => 'nullable|integer',
            'comparison'         => 'nullable',
            'category_ids'       => 'nullable|array',
            'category_ids.*'     => 'exists:food_categories,id',
        ];
    }

    private function formLookups(): array
    {
        $ownerRestaurantId = $this->ownerRestaurantId();

        return [
            'group'             => null,
            'statusOptions'     => ['A' => 'Active', 'H' => 'Hidden', 'D' => 'Disabled'],
            'categories'        => FoodCategory::when($ownerRestaurantId, fn ($q) => $q->where('restaurant_id', $ownerRestaurantId))->orderBy('name')->get(),
            'ownerRestaurantId' => $ownerRestaurantId,
            'restaurants'       => $ownerRestaurantId ? collect() : Restaurant::orderBy('name')->get(['id', 'name']),
        ];
    }
}
