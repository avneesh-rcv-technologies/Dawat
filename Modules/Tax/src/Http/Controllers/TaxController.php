<?php

namespace Modules\Tax\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Tax\DataView\Tax;
use Modules\Tax\Models\TaxRate;
use Modules\Countries\Models\Country;
use Illuminate\Routing\Controller;
use Modules\Tax\Http\Requests\TaxRateRequest;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $lists = fn_datagrid(Tax::class)->process();
        return view('tax::rates.index', compact('lists'));
    }

    public function create()
    {
        $countries = Country::all();
        return view('tax::rates.form', compact('countries'));
    }

    public function store(TaxRateRequest $request)
    {
        try {
            TaxRate::create($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tax Rate Created Successfully!',
                'redirect_url' => route('admin.tax.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function edit(TaxRate $tax)
    {
        $countries = Country::all();
        return view('tax::rates.form', compact('tax', 'countries'));
    }

    public function update(TaxRateRequest $request, TaxRate $tax)
    {
        try {
            $tax->update($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tax Rate Updated Successfully!',
                'redirect_url' => route('admin.tax.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(TaxRate $tax)
    {

        try {
            $tax->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tax Rate Deleted Successfully',
                'redirect_url' => route('admin.tax.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'.$e->getMessage()
            ]);
        }
    }
}
