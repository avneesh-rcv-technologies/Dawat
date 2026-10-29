<?php

namespace Modules\Countries\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Countries\Models\City;
use Modules\Countries\Services\CityService;
use Modules\Countries\DataView\CitiesView;

class CityController extends Controller
{
    protected CityService $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(CitiesView::class)->process();
        return view('countries::cities.index', compact('lists'));
    }

    public function show($id)
    {
        return response()->json(City::findOrFail($id));
    }

    public function create()
    {
        return view('countries::cities.form');
    }

    public function edit($id)
    {
        $city = $this->cityService->find($id);
        return view('countries::cities.form', compact('city'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'state_id' => 'nullable|exists:country_states,id',
            'country_id' => 'nullable|exists:countries,id',
            'name' => 'required|string|max:100',
            'latitude' => 'nullable|string|max:20',
            'longitude' => 'nullable|string|max:20',
            'flag' => 'nullable|string|max:255',
            'wikiDataId' => 'nullable|string|max:50',
        ]);

        try {
            $city = $this->cityService->store($data);

            return response()->json([
                'success' => true,
                'message' => 'City created successfully!',
                'redirect_url' => route('settings.cities.index'),
                'data' => $city
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.' . $e
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'state_id' => 'nullable|exists:country_states,id',
            'country_id' => 'nullable|exists:countries,id',
            'name' => 'required|string|max:100',
            'latitude' => 'nullable|string|max:20',
            'longitude' => 'nullable|string|max:20',
            'flag' => 'nullable|string|max:255',
            'wikiDataId' => 'nullable|string|max:50',
        ]);

        try {
            $city = $this->cityService->update($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'City updated successfully!',
                'redirect_url' => route('settings.cities.index'),
                'data' => $city
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.' . $e
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $this->cityService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'City deleted successfully!',
                'redirect_url' => route('settings.cities.index'),
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
            'ids.*' => 'integer|exists:cities,id',
        ]);
        try {
            $deletedCount = $this->cityService->deleteMultiple($request->ids);
            return redirect()->route('settings.cities.index')->with('success', 'Bulk Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->route('settings.cities.index')->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Get cities by state ID (API endpoint for cascading selectors)
     */
    public function getByState($stateId)
    {
        try {
            $cities = City::where('state_id', $stateId)
                ->orderBy('name')
                ->get(['id', 'name', 'latitude', 'longitude']);

            return response()->json([
                'success' => true,
                'cities' => $cities
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load cities'
            ], 500);
        }
    }

    /**
     * Get cities by country ID (API endpoint for cascading selectors)
     */
    public function getByCountry($countryId)
    {
        try {
            $cities = City::where('country_id', $countryId)
                ->orderBy('name')
                ->get(['id', 'name', 'state_id', 'latitude', 'longitude']);

            return response()->json([
                'success' => true,
                'cities' => $cities
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load cities'
            ], 500);
        }
    }
}