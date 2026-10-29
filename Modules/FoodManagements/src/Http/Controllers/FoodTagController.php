<?php

namespace Modules\FoodManagements\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\FoodManagements\DataView\FoodTagGrid;
use Modules\FoodManagements\Services\FoodTagService;

class FoodTagController extends Controller
{
    protected FoodTagService $service;

    public function __construct(FoodTagService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(FoodTagGrid::class)->process();

        return view('foodmanagements::food_tags.index', compact('lists'));
    }

    public function create()
    {
        return view('foodmanagements::food_tags.form', [
            'action' => route('admin.foodmanagements.food-tags.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:food_tags,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        try {
            $this->service->create($validator->validated());


            return response()->json([
                'success' => true,
                'message' => 'Food tag created successfully!',
                'redirect_url' => route('admin.foodmanagements.food-tags.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['errors' => 'Something went wrong.']);
        }
    }

    public function edit($id)
    {
        $tag = $this->service->getById($id);

        return view('foodmanagements::food_tags.form', [
            'tag'    => $tag,
            'action' => route('admin.foodmanagements.food-tags.update', $id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:food_tags,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        try {
            $this->service->update($id, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Food tag updated successfully!',
                'redirect_url' => route('admin.foodmanagements.food-tags.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['errors' => 'Something went wrong.']);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Food tag deleted successfully!',
                'redirect_url' => route('admin.foodmanagements.food-tags.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['errors' => 'Something went wrong.']);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:food_tags,id',
        ]);

        $this->service->bulkDelete($request->ids);

        return redirect()
            ->route('admin.foodmanagements.food-tags.index')
            ->with('success', 'Food tags deleted successfully');
    }
}
