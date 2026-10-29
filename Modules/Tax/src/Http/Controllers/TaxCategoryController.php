<?php

namespace Modules\Tax\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Tax\Models\TaxCategory;
use Modules\Tax\DataView\TaxCategory as TaxCateoryGrid;
use Modules\Tax\Http\Requests\TaxCategoryRequest;

class TaxCategoryController extends Controller
{
    public function index()
    {
        $lists = fn_datagrid(TaxCateoryGrid::class)->process();
        return view('tax::categories.index', compact('lists'));
    }

    public function create()
    {
        return view('tax::categories.form');
    }

    public function store(TaxCategoryRequest $request)
    {
        try {
            TaxCategory::create($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tax Category Created Successfully!',
                'redirect_url' => route('admin.tax-category.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function edit(TaxCategory $taxCategory)
    {
        return view('tax::categories.form', compact('taxCategory'));
    }

    public function update(TaxCategoryRequest $request, TaxCategory $taxCategory)
    {

        try {
            $taxCategory->update($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tax Category Updated Successfully!',
                'redirect_url' => route('admin.tax-category.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(TaxCategory $taxCategory)
    {
        try {
            $taxCategory->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tax Category Deleted Successfully',
                'redirect_url' => route('admin.tax-category.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }
}