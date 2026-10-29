<?php

namespace Modules\Countries\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Countries\Models\Country;
use App\Http\Controllers\Controller;
use Modules\Countries\Services\StateService;
use Illuminate\Support\Facades\Validator;
use Modules\Countries\DataView\StatesView;

class StateController extends Controller
{
    protected StateService $stateService;

    public function __construct(StateService $stateService)
    {
        $this->stateService = $stateService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(StatesView::class)->process();
        return view('countries::states.index', compact('lists'));
    }

    public function create()
    {

        $countries = Country::all();

        return view('countries::states.form', compact('countries'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'country_code' => 'required|string|max:10',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('country_states')->where(function ($query) use ($request) {
                    return $query->where('code', $request->code);
                }),
            ],
            'name' => 'required|string|max:100',
            'latitude' => 'nullable|string|max:20',
            'longitude' => 'nullable|string|max:20',
            'flag' => 'nullable|string|max:255',
            'wikiDataId' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        try {
            $state = $this->stateService->store($request->all());
            return response()->json([
                'success' => true,
                'message' => 'State created successfully!',
                'redirect_url' => route('settings.states.index'),
                'data' => $state
            ]);
        } catch (\Throwable $e) {
            return response()->json(['errors' => 'Something went wrong. Please try again.' . $e]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'country_code' => 'required|string|max:10',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('country_states')
                    ->ignore($id)
                    ->where(function ($query) use ($request) {
                        return $query->where('code', $request->code);
                    }),
            ],
            'name' => 'required|string|max:100',
            'latitude' => 'nullable|string|max:20',
            'longitude' => 'nullable|string|max:20',
            'flag' => 'nullable|string|max:255',
            'wikiDataId' => 'nullable|string|max:50',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        try {
            $state = $this->stateService->update($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'State updated successfully!',
                'redirect_url' => route('settings.states.index'),
                'data' => $state
            ]);
        } catch (\Throwable $e) {
            return response()->json(['errors' => 'Something went wrong. Please try again.' . $e]);
        }
    }


    public function edit($id)
    {
        $countries = Country::all();
        $state = $this->stateService->find($id);
        return view('countries::states.form', compact('state', 'countries'));
    }
    public function destroy($id)
    {
        try {
            $this->stateService->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'State deleted successfully!',
                'redirect_url' => route('settings.states.index'),
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
            'ids.*' => 'integer|exists:country_states,id',
        ]);
        try {
            $deletedCount = $this->stateService->deleteMultiple($request->ids);
            return redirect()->route('settings.states.index')->with('success', 'Bulk Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->route('settings.states.index')->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Get states by country ID (API endpoint for cascading selectors)
     */
    public function getByCountry($countryId)
    {
        try {
            $states = \Modules\Countries\Models\CountryState::where('country_id', $countryId)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);

            return response()->json([
                'success' => true,
                'states' => $states
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load states'
            ], 500);
        }
    }
}
