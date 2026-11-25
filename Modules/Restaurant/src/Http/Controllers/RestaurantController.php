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
use Modules\Restaurant\Services\RestaurantDeliveryPolicy;
use Modules\Restaurant\Services\RestaurantPayoutReport;
use Modules\Restaurant\Services\RestaurantOwnerRoleResolver;
use Modules\Rating\Models\Rating;

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

        return view('restaurant::admin.create', compact(
            'restaurantId',
            'restaurant',
            'hours',
            'holidays'
        ));
    }

    public function edit(int $id)
    {
        try {
            $currentAdmin = auth('admin')->user();
            $restaurant = $this->restaurantService->getRestaurantForEdit($id, $currentAdmin);

            if (request('tab') === 'gallery'
                && app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($currentAdmin)) {
                return redirect()->route('settings.restaurant.gallery.index');
            }

            if (request('tab') === 'settings'
                && app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($currentAdmin)) {
                return redirect()->route('settings.restaurant.preferences.index');
            }

            $hours = $this->restaurantService->getFormattedHours($restaurant);
            $holidays = $this->restaurantService->getFormattedHolidays($restaurant);

            // The old Food Management tab moved to the owner's Menu Management page.
            if (request('tab') === 'food' && $restaurant->canManageFeatures()
                && app(\Modules\Restaurant\Services\RestaurantOwnerRoleResolver::class)->isOwner($currentAdmin)) {
                return redirect()->route('admin.restaurant.menu.index', ['restaurant' => $restaurant->id]);
            }

            if (app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($currentAdmin)) {
                return redirect()->route('settings.restaurant.general-information.index');
            }

            return view('restaurant::admin.create', [
                'restaurantId' => $restaurant->id,
                'restaurant'   => $restaurant,
                'hours'        => $hours,
                'holidays'     => $holidays,
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
        abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($request->user('admin')), 403);
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
        abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($request->user('admin')), 403);
        try {
            $validated = $request->validate([
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'country_id' => 'required|integer|exists:countries,id',
                'state_id' => 'required|integer|exists:country_states,id',
                'city_id' => 'nullable|integer|exists:cities,id',
                'postal_code' => 'required|string|min:5|max:9',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            // Get names for backward compatibility with restaurants table
            $country = \Modules\Countries\Models\Country::find($validated['country_id']);
            $state = \Modules\Countries\Models\CountryState::find($validated['state_id']);
            $city = \Modules\Countries\Models\City::find($validated['city_id'] ?? null);

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
        $admin = $request->user('admin');
        if (app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($admin)) {
            abort_unless($admin->hasPermission('settings.restaurant.working-hours.index')
                && $admin->hasPermission('settings.restaurant.working-hours.update'), 403);
            \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->whereKey($restaurant)->firstOrFail();
        }

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
        $admin = $request->user('admin');
        if (app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($admin)) {
            abort_unless($admin->hasPermission('settings.restaurant.holidays.index')
                && $admin->hasPermission('settings.restaurant.holidays.update'), 403);
            \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->whereKey($restaurant)->firstOrFail();
        }

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
        abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($request->user('admin')), 403);
        $redirect = route('admin.restaurant.edit', ['id' => $restaurant, 'tab' => 'settings']);
        try {
            $data = $request->validate(array_merge([
                'food_type' => 'nullable|in:all,veg,non_veg,vegan',
                'status' => 'nullable|in:draft,active,inactive',
                'order_time_limit' => 'nullable|integer|min:0|max:1440',
                'cancellation_time_limit' => 'nullable|integer|min:0|max:120',
                'cancellable_statuses' => 'nullable|array',
                'cancellable_statuses.*' => 'string',
            ], RestaurantDeliveryPolicy::rules()), RestaurantDeliveryPolicy::messages());
            foreach (['is_takeaway', 'is_delivery', 'is_accepting_orders', 'allow_order_cancellation', 'free_delivery_enabled'] as $field) {
                $data[$field] = $request->boolean($field);
            }
            $data['cancellable_statuses'] = $data['cancellable_statuses'] ?? [];
            $this->restaurantService->updateSettings($restaurant, $data);

            return redirect($redirect)->with('success', 'Restaurant settings updated successfully.')->with('tab', 'settings');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect($redirect)->withErrors($e->errors())->withInput()->with('tab', 'settings');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Restaurant settings update failed.', ['restaurant_id' => $restaurant, 'exception' => $e]);

            return redirect($redirect)->with('error', 'Unable to update restaurant settings. Please try again.')->withInput()->with('tab', 'settings');
        }
    }

    public function view($id)
    {
        $isRestaurantOwner = app(RestaurantOwnerRoleResolver::class)->isOwner(auth('admin')->user());
        if ($isRestaurantOwner) {
            abort_unless(\Modules\Restaurant\Models\Restaurant::whereKey($id)
                ->where('admin_id', auth('admin')->id())->exists(), 403);
        }
        $reviewFilters = [];
        if ($isRestaurantOwner && request('tab') === 'reviews') {
            $reviewFilters = request()->validate([
                'review_rating' => ['nullable', 'integer', 'between:1,5'],
                'review_date_from' => ['nullable', 'date'],
                'review_date_to' => ['nullable', 'date', 'after_or_equal:review_date_from'],
            ]);
        }

        $payoutReport = app(RestaurantPayoutReport::class);
        $payoutFilters = $payoutReport->filters(request());
        $canViewPayouts = $payoutReport->canView((int) $id, auth('admin')->user());
        if (request('tab') === 'payouts') {
            abort_unless($canViewPayouts, 403);
        }
        $payoutData = $canViewPayouts ? $payoutReport->page((int) $id, auth('admin')->user(), $payoutFilters) : null;
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
            $restaurantReviews = null;
            if ($isRestaurantOwner) {
                $reviewsQuery = Rating::query()
                    ->with(['user', 'order.customer'])
                    ->where('ratable_type', \Modules\Restaurant\Models\Restaurant::class)
                    ->where('ratable_id', $id)
                    ->approved()
                    ->active();
                if (!empty($reviewFilters['review_rating'])) {
                    $reviewsQuery->where('rating', $reviewFilters['review_rating']);
                }
                if (!empty($reviewFilters['review_date_from'])) {
                    $reviewsQuery->whereDate('created_at', '>=', $reviewFilters['review_date_from']);
                }
                if (!empty($reviewFilters['review_date_to'])) {
                    $reviewsQuery->whereDate('created_at', '<=', $reviewFilters['review_date_to']);
                }
                $restaurantReviews = $reviewsQuery->latest('created_at')
                    ->paginate(5, ['*'], 'reviews_page')
                    ->withQueryString();
            }

            return view('restaurant::admin.view', array_merge($data, [
                'history' => $history,
                'onboardingTimeline' => $onboardingTimeline,
                'historySteps' => $historySteps,
                'historyFilter' => $historyFilter,
                'restaurant' => $data['restaurant'],
                'canViewPayouts' => $canViewPayouts,
                'payoutData' => $payoutData,
                'isRestaurantOwner' => $isRestaurantOwner,
                'restaurantReviews' => $restaurantReviews,
                'reviewFilters' => $reviewFilters,
            ]));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }


    public function destroy(int $id)
    {
        try {
            if (!$this->restaurantService->canDeleteRestaurants(auth('admin')->user(), [$id])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this restaurant',
                ], 403);
            }

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
