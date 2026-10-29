<?php

namespace Modules\Countries\Http\Controllers;

use Throwable;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Countries\Services\CountryService;
use Modules\Countries\DataView\CountryView;

class CountryController extends Controller
{
    protected $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(CountryView::class)->process();
        return view('countries::countries.index', compact('lists'));
    }

    public function create()
    {
        return view('countries::countries.form');
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                Rule::unique('countries')->where(function ($query) use ($request) {
                    return $query->where('code', $request->code)
                        ->where('name', $request->name);
                }),
                'max:10',
            ],
            'name' => 'required|string|max:100',
            'iso3' => 'nullable|string|max:3',
            'numeric_code' => 'nullable|string|max:10',
            'phonecode' => 'nullable|string|max:10',
            'capital' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'region' => 'nullable|string|max:100',
            'subregion' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:20',
            'longitude' => 'nullable|string|max:20',
            'flag' => 'nullable|string|max:255',
            'wikiDataId' => 'nullable|string|max:50',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        try {

            $country = $this->countryService->store($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Country created successfully!',
                'redirect_url' => route('settings.countries.index'),
                'data' => $country
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.' . $e
            ]);
        }
    }

    public function show($id)
    {
        $user = $this->countryService->find($id);
        if (!$user) {
            return redirect()->route('settings.countries.index')->with('error', 'User not found.');
        }

        return view('countries::countries.form');
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                Rule::unique('countries')->ignore($id)->where(function ($query) use ($request) {
                    return $query->where('code', $request->code)
                        ->where('name', $request->name);
                }),
                'max:10',
            ],
            'name' => 'required|string|max:100',
            'iso3' => 'nullable|string|max:3',
            'numeric_code' => 'nullable|string|max:10',
            'phonecode' => 'nullable|string|max:10',
            'capital' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'region' => 'nullable|string|max:100',
            'subregion' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:20',
            'longitude' => 'nullable|string|max:20',
            'flag' => 'nullable|string|max:255',
            'wikiDataId' => 'nullable|string|max:50',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $user = $this->countryService->update($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Country updated successfully!',
                'redirect_url' => route('settings.countries.index'),
                'data' => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }
    public function edit($id)
    {
        $country = $this->countryService->find($id);
        return view('countries::countries.form', compact('country'));
    }
    public function destroy($id)
    {
        try {
            $this->countryService->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'Country deleted Successfully!',
                'redirect_url' => route('settings.countries.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:country,id',
        ]);
        try {
            $deletedCount = $this->countryService->deleteMultiple($request->ids);
            return redirect()->route('settings.countries.index')->with('success', 'Bulk Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->route('settings.countries.index')->with('error', 'Something went wrong. Please try again.');
        }
    }

     public function getCountry()
    {
        $countries = $this->countryService->getCountries();

        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }
}
