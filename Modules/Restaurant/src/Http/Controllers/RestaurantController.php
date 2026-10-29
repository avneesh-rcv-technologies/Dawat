<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Support\Facades\Validator;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Restaurant\DataView\RestaurantGrid;
use Modules\Restaurant\Services\RestaurantService;
use Modules\FoodManagements\DataView\FoodItemsGrid;

class RestaurantController extends Controller
{
    protected RestaurantService $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }
    public function index()
    {
        $lists = fn_datagrid(RestaurantGrid::class)->process();
        return view('restaurant::admin.index', compact('lists'));
    }

    public function create(Request $request)
    {
        $restaurantId = $request->id;

        $restaurant = $restaurantId
            ? $this->restaurantService->findWithRelations($restaurantId)
            : null;

        $hours = $this->restaurantService->getFormattedHours($restaurant);
        $holidays = $this->restaurantService->getFormattedHolidays($restaurant);

        // Fetch food items for the restaurant
        $foodItems = null;
        if ($restaurant) {
            $foodItems = fn_datagrid(FoodItemsGrid::class)->process();
        }

        return view('restaurant::admin.create', compact(
            'restaurantId',
            'restaurant',
            'hours',
            'holidays',
            'foodItems'
        ));
    }

    public function edit(int $id)
    {
        try {
            $currentAdmin = auth('admin')->user();
            $restaurant = $this->restaurantService->getRestaurantForEdit($id, $currentAdmin);

            $hours = $this->restaurantService->getFormattedHours($restaurant);
            $holidays = $this->restaurantService->getFormattedHolidays($restaurant);

            // Fetch food items for the restaurant
            $foodItems = fn_datagrid(FoodItemsGrid::class)->process();

            return view('restaurant::admin.create', [
                'restaurantId' => $restaurant->id,
                'restaurant'   => $restaurant,
                'hours'        => $hours,
                'holidays'     => $holidays,
                'foodItems'    => $foodItems,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.restaurant.index')
                ->with('error', $e->getMessage());
        }
    }





    /* TAB 1 */
    // public function storeGeneral(Request $request)
    // {
    //     $data = $request->validate([
    //         'name'        => 'required|string|max:150',
    //         'email'       => 'required|email|unique:admins,email',
    //         'password'    => 'required|min:8',
    //         'phone'       => 'required|string|max:20',
    //         'slug'        => 'required|string|unique:restaurants,slug',
    //         'description' => 'nullable|string',
    //         'image'       => 'nullable|image|max:2048',
    //     ]);


    //     try {
    //         $restaurantId = $this->restaurantService->createGeneral($data);

    //         // Upload image via Filemanager if provided
    //         if ($request->hasFile('image')) {
    //             fn_upload_image($request->file('image'), 'restaurant', $restaurantId, true);
    //         }

    //         return redirect()
    //             ->route('admin.restaurant.create', ['id' => $restaurantId])
    //             ->with('tab', 'address');
    //     } catch (\Exception $e) {

    //         return back()->withErrors(['error' => $e->getMessage()]);
    //     }
    // }

    /* TAB 1 */
    public function storeGeneral(Request $request)
    {
        // Check if this is an edit 
        $restaurantId = $request->input('restaurant_id');
        $isEdit = !empty($restaurantId);
        $rules = [
            'name'        => 'required|string|max:150',
            'phone'       => 'required|string|max:20',
            'description' => 'nullable|string',
            'image'       => 'nullable|image',
        ];


        if ($isEdit) {
            $rules['password'] = ['nullable', 'string', new StrongPassword()];
        } else {
           
            $rules['email'] = 'required|email|unique:admins,email';
            $rules['password'] = ['required', 'string', new StrongPassword()];
            $rules['slug'] = 'required|string|unique:restaurants,slug';
        }

        $data = $request->validate($rules);

        try {
            if ($isEdit) {
                // Update existing restaurant
                $this->restaurantService->updateGeneral($restaurantId, $data);
                // Upload image
                if ($request->hasFile('image')) {
                    fn_upload_image($request->file('image'), 'restaurant', $restaurantId, true);
                    // CacheService::forgetImage('restaurant', $restaurantId);
                }
                return back()->with([
                    'success' => 'Restaurant information updated successfully',
                    'tab' => 'general'
                ]);
            } else {
                // Create new restaurant
                $restaurantId = $this->restaurantService->createGeneral($data);
                // Upload image via Filemanager if provided
                if ($request->hasFile('image')) {
                    fn_upload_image($request->file('image'), 'restaurant', $restaurantId, true);
                }

                return redirect()
                    ->route('admin.restaurant.create', ['id' => $restaurantId]);
                    // ->with('tab', 'address');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }




    /* TAB 2 */
    /* TAB 2 */
    public function updateAddress(Request $request, $restaurant)
    {
        try {
            $validated = $request->validate([
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'country_id' => 'required|integer|exists:countries,id',
                'state_id' => 'required|integer|exists:country_states,id',
                'city_id' => 'required|integer|exists:cities,id',
                'postal_code' => 'required|string|max:20',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            // Get names for backward compatibility with restaurants table
            $country = \Modules\Countries\Models\Country::find($validated['country_id']);
            $state = \Modules\Countries\Models\CountryState::find($validated['state_id']);
            $city = \Modules\Countries\Models\City::find($validated['city_id']);

            $data = array_merge($validated, [
                'country_name' => $country?->name,
                'state_name' => $state?->name,
                'city_name' => $city?->name,
            ]);

            $this->restaurantService->updateAddress($restaurant, $data);

            return back()->with([
                'success' => 'Address saved successfully',
                // 'tab' => 'hours'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }



    /* TAB 3 */
    public function updateHours(Request $request, $restaurant)
    {
        try {
            $hours = $request->input('hours', []);
            $isOpenWholeDay = $request->boolean('is_open_whole_day');
            $this->restaurantService->updateHours($restaurant, $hours, $isOpenWholeDay);

            return back()->with([
                'success' => 'Working hours saved',
                // 'tab' => 'holidays'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with([
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            return back()->with([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /* TAB 4 */
    public function updateHolidays(Request $request, $restaurant)
    {
        try {
            $validated = Validator::make($request->all(), [
                'holidays' => 'nullable|array',
                'holidays.*.date' => 'required_with:holidays|date',
                'holidays.*.reason' => 'required|string|max:255',
                'holidays.*.is_closed' => 'nullable|boolean',
            ], [
                'holidays.*.reason.required' => 'Please enter a reason for every holiday.',
            ]);

            if ($validated->fails()) {
                return back()->withErrors($validated)->withInput()->with([
                    'error' => 'Please provide valid holiday data.',
                    'tab' => 'general',
                ]);
            }

            $holidays = $request->input('holidays', []);

            $this->restaurantService->updateHolidays($restaurant, $holidays);

            return back()->with([
                'success' => 'Holidays saved successfully',
                'tab' => 'general'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /* TAB 5 */
    public function updateSettings(Request $request, $restaurant)
    {
        $data = $request->validate([
            'food_type' => 'nullable|in:all,veg,non_veg,vegan',
            'status' => 'nullable|in:draft,active,inactive',
            'order_time_limit' => 'nullable|integer|min:0|max:1440',
            'cancellation_time_limit' => 'nullable|integer|min:0|max:120',
            'cancellable_statuses' => 'nullable|array',
            'cancellable_statuses.*' => 'string',
        ]);

        try {
            $data['is_takeaway'] = $request->boolean('is_takeaway');
            $data['is_delivery'] = $request->boolean('is_delivery');
            $data['is_accepting_orders'] = $request->boolean('is_accepting_orders');
            $data['allow_order_cancellation'] = $request->boolean('allow_order_cancellation');

            $this->restaurantService->updateSettings($restaurant, $data);

            return redirect()->route('admin.restaurant.index')
                ->with('success', 'Restaurant Created Successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function view($id)
    {
        $historySteps = \Modules\Restaurant\Enums\OnboardingStep::required();
        $historyFilter = request()->query('history_step', 'all');
        abort_unless(is_string($historyFilter) && in_array($historyFilter, array_merge(
            ['all', 'overall'], array_map(fn ($step) => $step->value, $historySteps)
        ), true), 422);
        try {
            $data = $this->restaurantService->getRestaurantView($id);
            $history = $this->restaurantService->getRestaurantHistory($id);
            $timelineRestaurant = \Modules\Restaurant\Models\Restaurant::with('onboardingSteps.reviewer')->findOrFail($id);
            $timelineRestaurant->setRelation('onboardingStatusHistories', $history);
            $onboardingTimeline = app(\Modules\Restaurant\Services\RestaurantOnboardingDetailsService::class)->build($timelineRestaurant);

            return view('restaurant::admin.view', array_merge($data, [
                'history' => $history,
                'onboardingTimeline' => $onboardingTimeline,
                'historySteps' => $historySteps,
                'historyFilter' => $historyFilter,
                'restaurant' => $data['restaurant'],
            ]));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }


    public function destroy(int $id)
    {
        try {
            $this->restaurantService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Restaurant deleted successfully',
                'redirect_url' => route('admin.restaurant.index'),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete restaurant',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = array_filter((array) $request->input('ids'));
            $currentAdmin = auth('admin')->user();

            // Hard stop if nothing selected
            if (count($ids) === 0) {
                return redirect()->route('admin.restaurant.index')
                    ->with('error', 'Please select at least one restaurant');
            }

            $deletedCount = $this->restaurantService->bulkDelete($ids, $currentAdmin);

            return redirect()->route('admin.restaurant.index')
                ->with('success', $deletedCount . ' restaurant(s) deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.restaurant.index')
                ->with('error', $e->getMessage());
        }
    }


    public function bulkStatus(Request $request)
    {
        try {
            $ids = array_filter((array) $request->input('ids'));
            // Handle both 'status' and 'action' parameter names for compatibility
            $status = $request->input('status') ?: $request->input('action');
            $currentAdmin = auth('admin')->user();

            if (count($ids) === 0) {
                return redirect()->route('admin.restaurant.index')
                    ->with('error', 'Please select at least one restaurant');
            }

            // Check if status is empty or null
            if (empty($status)) {
                return redirect()->route('admin.restaurant.index')
                    ->with('error', 'No status selected');
            }

            $updatedCount = $this->restaurantService->bulkUpdateStatus($ids, $status, $currentAdmin);

            return redirect()->route('admin.restaurant.index')
                ->with('success', $updatedCount . ' restaurant(s) updated to ' . ucfirst($status));
        } catch (\Exception $e) {
            return redirect()->route('admin.restaurant.index')
                ->with('error', $e->getMessage());
        }
    }
}
