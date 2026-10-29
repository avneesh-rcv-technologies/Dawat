<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Services\RestaurantOnboardingAccessService;
use Modules\Restaurant\Services\RestaurantOnboardingDetailsService;
use Modules\Restaurant\Services\RestaurantOnboardingStepDetailsService;

class RestaurantOnboardingStatusController extends Controller
{
    public function __invoke(
        Request $request,
        RestaurantOnboardingAccessService $access,
        RestaurantOnboardingDetailsService $details,
        RestaurantOnboardingStepDetailsService $stepDetails
    ) {
        $admin = $request->user('admin');
        if (!$admin) {
            return redirect()->route('admin.login.form');
        }

        if (!$admin->status || !$admin->email_verified_at) {
            $message = !$admin->status
                ? 'Your account is inactive. Please contact the administrator.'
                : 'Please verify your email address before continuing with restaurant onboarding.';

            \Illuminate\Support\Facades\Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login.form')->with('error', $message)
                ->header('Cache-Control', 'no-store, private');
        }

        $restaurant = $access->forAdmin($admin);
        if (!$restaurant) {
            return redirect()->route('dashboard.index');
        }

        $key = $request->query('step');
        $selectedStep = is_string($key) ? OnboardingStep::tryFrom($key) : null;
        abort_if($key !== null && !$selectedStep, 404);

        $correctionData = [];
        if ($selectedStep === OnboardingStep::REGISTRATION) {
            $countries = \Modules\Countries\Models\Country::orderBy('name')->get(['id', 'name']);
            $address = $restaurant->addresses()->orderByDesc('is_default')->orderBy('id')->first();
            $countryId = old('country_id', $address?->country_id ?? $countries->firstWhere('name', $restaurant->country)?->id);
            $states = \Modules\Countries\Models\CountryState::where('country_id', is_scalar($countryId) ? $countryId : null)->orderBy('name')->get(['id', 'name']);
            $stateId = old('state_id', $address?->state_id ?? $states->firstWhere('name', $restaurant->state)?->id);
            $cities = \Modules\Countries\Models\City::where('state_id', is_scalar($stateId) ? $stateId : null)->orderBy('name')->get(['id', 'name']);
            $cityId = old('city_id', $address?->city_id ?? $cities->firstWhere('name', $restaurant->city)?->id);
            $correctionData = compact('countries', 'countryId', 'states', 'stateId', 'cities', 'cityId');
        }
        if ($selectedStep === OnboardingStep::MENU) {
            $correctionData['items'] = \Modules\FoodManagements\Models\FoodItem::where('restaurant_id', $restaurant->id)->orderBy('id')->get();
        }

        return response()->view('restaurant::onboarding.status', [
            'timeline' => $details->build($restaurant),
            'selectedStep' => $selectedStep,
            'stepDetails' => $selectedStep ? $stepDetails->build($restaurant, $selectedStep) : null,
            'correctionRestaurant' => $restaurant,
            'correctionData' => $correctionData,
        ])->header('Cache-Control', 'no-store, private');
    }
}
