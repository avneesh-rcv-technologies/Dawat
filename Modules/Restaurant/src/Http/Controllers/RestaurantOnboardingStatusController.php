<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Acl\Services\LoginRedirector;
use Modules\Countries\Models\City;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;
use Modules\DietaryManagment\Services\OnboardingCertificateService;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
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
        if (! $admin) {
            return redirect()->guest(app(LoginRedirector::class)->url($request))
                ->header('Cache-Control', 'no-store, private');
        }

        $isOwner = app(RestaurantApprovalAccess::class)->isOwner($admin);
        if (! $admin->status || ($isOwner && ! $admin->email_verified_at)) {
            $message = ! $admin->status
                ? 'Your account is inactive. Please contact the administrator.'
                : 'Please verify your email address before continuing with restaurant onboarding.';

            $loginUrl = app(LoginRedirector::class)->url($request, $admin);
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->to($loginUrl)->with('error', $message)
                ->header('Cache-Control', 'no-store, private');
        }

        if (! $isOwner) {
            abort_unless($admin->hasPermission('admin.restaurant.approvals.index'), 403);

            return redirect()->route('admin.restaurant.approvals.index')
                ->header('Cache-Control', 'no-store, private');
        }

        $restaurant = $access->forAdmin($admin);
        if (! $restaurant) {
            $restaurant = Restaurant::where('admin_id', $admin->id)->first();
        }
        if (! $restaurant) {
            return redirect()->route('dashboard.index');
        }

        $key = $request->query('step');
        $timeline = $details->build($restaurant);
        $isOverallApproved = ($restaurant->onboarding_status?->value ?? $restaurant->onboarding_status) === 'approved';

        // Approval is the terminal onboarding state. A browser can retain an old
        // `?step=submitted` URL after an administrator approves the last document;
        // always canonicalize that URL instead of showing a stale review screen.
        if ($isOverallApproved && $key !== null) {
            return redirect()->route('restaurant.onboarding.kyc');
        }

        // Do not let a query parameter present a verified screen before the
        // restaurant has actually been approved.
        if (in_array($key, ['verified', 'approved'], true) && ! $isOverallApproved) {
            return redirect()->route('restaurant.onboarding.kyc')
                ->with('error', 'Your onboarding is not approved yet.');
        }

        $isSubmitted = ! $isOverallApproved && ($key === 'submitted' || $request->boolean('submitted'));

        if ($isSubmitted) {
            $requiredSteps = collect($timeline['steps'] ?? [])->filter(fn ($s) => ! ($s['can_skip'] ?? false));
            $hasIncompleteRequired = $requiredSteps->contains(
                fn ($s) => ! in_array($s['status'] ?? '', ['approved', 'submitted', 'under_review'], true)
            );
            if ($hasIncompleteRequired) {
                return redirect()->route('restaurant.onboarding.kyc')
                    ->with('error', 'Please complete all required onboarding sections before viewing submission status.');
            }
        }

        $isVerified = $isOverallApproved;
        $isSpecialView = $isSubmitted || $isVerified || $key === 'overview';
        $selectedStep = is_string($key) && ! $isSpecialView ? OnboardingStep::tryFrom($key) : null;
        abort_if($key !== null && ! $isSpecialView && ! $selectedStep, 404);
        $correctionData = [];
        if ($selectedStep === OnboardingStep::DIETARY_CERTIFICATES) {
            $correctionData = app(OnboardingCertificateService::class)->screen($restaurant);
        }
        if ($selectedStep === OnboardingStep::REGISTRATION) {
            $countries = Country::orderBy('name')->get(['id', 'name']);
            $address = $restaurant->addresses()->orderByDesc('is_default')->orderBy('id')->first();
            $countryId = old('country_id', $address?->country_id ?? $countries->firstWhere('name', $restaurant->country)?->id);
            $states = CountryState::where('country_id', is_scalar($countryId) ? $countryId : null)->orderBy('name')->get(['id', 'name']);
            $stateId = old('state_id', $address?->state_id ?? $states->firstWhere('name', $restaurant->state)?->id);
            $cities = City::where('state_id', is_scalar($stateId) ? $stateId : null)->orderBy('name')->get(['id', 'name']);
            $cityId = old('city_id', $address?->city_id ?? $cities->firstWhere('name', $restaurant->city)?->id);
            $correctionData = compact('countries', 'countryId', 'states', 'stateId', 'cities', 'cityId');
        }

        return response()->view('restaurant::onboarding.status', [
            'timeline' => $timeline,
            'selectedStep' => $selectedStep,
            'stepDetails' => $selectedStep ? $stepDetails->build($restaurant, $selectedStep) : null,
            'correctionRestaurant' => $restaurant,
            'correctionData' => $correctionData,
            'isSubmitted' => $isSubmitted,
            'isVerified' => $isVerified,
        ])->header('Cache-Control', 'no-store, private');
    }
}
