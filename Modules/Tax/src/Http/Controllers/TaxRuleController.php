<?php

namespace Modules\Tax\Http\Controllers;

use Modules\Tax\Models\TaxRate;
use Modules\Tax\Models\TaxRule;
use Illuminate\Routing\Controller;
use Modules\Tax\Models\TaxCategory;
use Modules\Tax\Http\Requests\TaxRuleRequest;
use Modules\Tax\DataView\TaxRule as TaxRuleGrid;

class TaxRuleController extends Controller
{
    public function index()
    {
        $lists = fn_datagrid(TaxRuleGrid::class)->process();
        
        return view('tax::rules.index', compact('lists'));
    }

    public function create()
    {
        $taxCategories = TaxCategory::where('status', true)->get();

        $taxRates = TaxRate::where('is_active', true)->get();
        return view('tax::rules.form', compact('taxCategories', 'taxRates'));
    }

    public function store(TaxRuleRequest $request)
    {
  
        try {
            TaxRule::create($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tax Rule Created Successfully!',
                'redirect_url' => route('admin.tax-rules.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    
       
    }

    public function edit(TaxRule $taxRule)
    {

        $taxCategories = TaxCategory::where('status', true)->get();
        $taxRates = TaxRate::where('is_active', true)->get();
        return view('tax::rules.form', compact('taxRule', 'taxCategories', 'taxRates'));
    }

    public function update(TaxRuleRequest $request, TaxRule $taxRule)
    {
       try {
            $taxRule->update($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tax Rule Updated Successfully!',
                'redirect_url' => route('admin.tax-rules.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(TaxRule $taxRule)
    {
        try {
            $taxRule->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tax Rule Deleted Successfully',
                'redirect_url' => route('admin.tax-rules.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }
}