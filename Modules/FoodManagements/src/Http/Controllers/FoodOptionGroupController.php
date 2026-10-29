<?php

namespace Modules\FoodManagements\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\FoodManagements\DataView\FoodOptionGroupGrid;
use Modules\FoodManagements\Services\FoodOptionGroupService;

class FoodOptionGroupController extends Controller
{
    protected FoodOptionGroupService $service;

    public function __construct(FoodOptionGroupService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(FoodOptionGroupGrid::class)->process();

        return view('foodmanagements::food_options.index', compact('lists'));
    }

    public function create()
    {
        return view('foodmanagements::food_options.create', [
            'action' => route('admin.foodmanagements.food-option-groups.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'is_required' => 'boolean',
            'min'         => 'nullable|integer|min:0',
            'max'         => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->service->create($validator->validated());

            return response()->json([
                'success'      => true,
                'message'      => 'Option group created successfully!',
                'redirect_url' => route('admin.foodmanagements.food-option-groups.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong.',
            ], 500);
        }
    }

    public function edit($id)
    {
        $optionGroup = $this->service->getById($id);

        return view('foodmanagements::food_options.edit', [
            'optionGroup' => $optionGroup,
            'action'      => route('admin.foodmanagements.food-option-groups.update', $id),
            'method'      => 'PUT',
        ]);
    }

    public function show($id)
    {
        $optionGroup = $this->service->getById($id);

        return view('foodmanagements::food_options.show', compact('optionGroup'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'is_required' => 'boolean',
            'min'         => 'nullable|integer|min:0',
            'max'         => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->service->update($id, $validator->validated());

            return response()->json([
                'success'      => true,
                'message'      => 'Option group updated successfully!',
                'redirect_url' => route('admin.foodmanagements.food-option-groups.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success'      => true,
                'message'      => 'Option group deleted successfully!',
                'redirect_url' => route('admin.foodmanagements.food-option-groups.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong.',
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:food_option_groups,id',
        ]);

        try {
            $this->service->bulkDelete($request->ids);

            return redirect()
                ->route('admin.foodmanagements.food-option-groups.index')
                ->with('success', 'Option groups deleted successfully');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.foodmanagements.food-option-groups.index')
                ->with('error', 'Something went wrong');
        }
    }
}
