<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;
use Modules\Countries\Models\City;
use Modules\Acl\Models\Admin;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Restaurant\Models\Restaurant;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Services\RestaurantOnboardingOtpService;
use Modules\Restaurant\Services\GeocodingService;


class RestaurantOnboardingController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService;
    }

    public function showForm(Request $request)
    {
        $countryId = $request->old('country_id');
        $stateId = $request->old('state_id');
        $countries = Country::orderBy('name')->get(['id', 'name']);
        $states = is_scalar($countryId)
            ? CountryState::where('country_id', $countryId)->orderBy('name')->get(['id', 'name']) : collect();
        $cities = is_scalar($stateId) && $states->contains('id', $stateId)
            ? City::where('state_id', $stateId)->where('country_id', $countryId)->orderBy('name')->get(['id', 'name']) : collect();

        return view('frontend.restaurant-onboarding', compact('countries', 'states', 'cities'));
    }

    public function checkEmail(Request $request)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $request->validate([
            'email' => ['bail', 'required', 'string', 'max:255', 'email:rfc,dns'],
        ], [
            'email.email' => 'Enter a valid email address with a domain that can receive email.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email format and domain are valid.',
        ]);
    }

    public function submit(Request $request, RestaurantOnboardingOtpService $otpService)
    {
        if (app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled()
            && ! app(\Modules\Website\Support\PartnerSignupCaptcha::class)->valid(
                $request->input('captcha_verification_token'), $request->input('email')
            )) {
            if ($request->filled('g-recaptcha-response')) {
                $request->validate(['g-recaptcha-response' => ['required', new \Modules\Website\Rules\Recaptcha]]);
            } else {
                throw ValidationException::withMessages([
                    'captcha_verification_token' => 'Please complete the reCAPTCHA challenge again before creating your account.',
                ]);
            }
        }

        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        if (is_string($request->input('restaurant_name'))) {
            $request->merge(['restaurant_name' => trim($request->input('restaurant_name'))]);
        }
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'email' => 'bail|required|string|max:255|email:rfc,dns|unique:admins,email',
            'restaurant_name' => 'required|string|min:2|max:150',
            'phone' => 'required|string|max:20',
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'state_id' => ['required', 'integer', Rule::exists('country_states', 'id')
                ->where('country_id', is_scalar($request->input('country_id')) ? $request->input('country_id') : null)],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')
                ->where('country_id', is_scalar($request->input('country_id')) ? $request->input('country_id') : null)
                ->where('state_id', is_scalar($request->input('state_id')) ? $request->input('state_id') : null)],
            'address_line1' => 'required|string|max:100',
            'postal_code' => 'required|string|min:5|max:9',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
            'email_verification_token' => ['nullable', 'string', 'size:64'],
        ], [
            'address_line1.max' => 'Please keep your full address to 100 characters or fewer.',
            'address_line1.required' => 'Please enter your full address.',
            'postal_code.required' => 'Please enter your ZIP / postal code.',
        ]);

        $verificationToken = $validated['email_verification_token'] ?? '';
        if (!$otpService->isVerified($validated['email'], $verificationToken)) {
            $request->merge(['email_verification_token' => null]);
            throw ValidationException::withMessages(['email' => ['Please verify your email before submitting your application.']]);
        }

        try {
            DB::beginTransaction();
            // Store the validated location IDs alongside their display names.
            $validated['country'] = Country::findOrFail($validated['country_id'])->name;
            $validated['state'] = CountryState::findOrFail($validated['state_id'])->name;
            $validated['city'] = City::find($validated['city_id'] ?? null)?->name;
            $coordinates = app(GeocodingService::class)->geocodeAddress([
                'address_line1' => $validated['address_line1'],
                'city_name' => $validated['city'],
                'state_name' => $validated['state'],
                'country_name' => $validated['country'],
                'postal_code' => trim($validated['postal_code']),
            ]);
            // Use provided password from onboarding form
            $plainPassword = $validated['password'];

            // Create Admin (Restaurant Owner)
            $admin = Admin::create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
                'status' => 1,
                'role_id' => app(\Modules\Restaurant\Services\RestaurantOwnerRoleResolver::class)->resolve()->getKey(),
                'email_verified_at' => now(),
            ]);

            $submittedAt = now();

            // Create Restaurant
            $restaurant = Restaurant::create([
                'admin_id' => $admin->id,
                'name' => $validated['restaurant_name'],
                'slug' => Str::slug($validated['restaurant_name']) . '-' . Str::random(6),
                'phone' => $validated['phone'],
                'city' => $validated['city'],
                'country_id' => $validated['country_id'],
                'state_id' => $validated['state_id'],
                'city_id' => $validated['city_id'] ?? null,
                'state' => $validated['state'],
                'country' => $validated['country'],
                'address_line1' => $validated['address_line1'],
                'postal_code' => trim($validated['postal_code']),
                'latitude' => $coordinates['latitude'] ?? null,
                'longitude' => $coordinates['longitude'] ?? null,
                'is_accepting_orders' => false,
                'status' => 'active',
                'approval_status' => 'approved',
                'approved_at' => $submittedAt,
                'onboarding_status' => OnboardingStatus::DRAFT->value,
                'onboarding_step' => OnboardingStep::BANKING->order(),
                'submitted_at' => $submittedAt,
            ]);

            $restaurant->onboardingSteps()->createMany(array_map(
                fn (OnboardingStep $step) => [
                    'step_key' => $step->value,
                    'status' => $step === OnboardingStep::REGISTRATION
                        ? OnboardingStatus::APPROVED->value
                        : OnboardingStatus::DRAFT->value,
                    'submitted_at' => $step === OnboardingStep::REGISTRATION
                        ? $submittedAt
                        : null,
                    'reviewed_at' => $step === OnboardingStep::REGISTRATION
                        ? $submittedAt
                        : null,
                ],
                OnboardingStep::ordered()
            ));

            DB::commit();
            $otpService->forgetVerification($verificationToken);

            // Prepare notification data with real email and password
            $data = [
                'restaurant_name' => $validated['restaurant_name'],
                'restaurant_owner_name' => $validated['owner_name'],
                'restaurant_email' => $validated['email'],
                'restaurant_password' => $plainPassword,
                'restaurant_phone' => $validated['phone'],
                'restaurant_city' => $validated['city'],
                'restaurant_address' => $validated['address_line1'],
                'restaurant_id' => $restaurant->id,
                'submitted_date' => $restaurant->created_at->format('Y-m-d H:i:s'),
            ];

            // Send notification to admin about new restaurant onboarding
            if ($this->notificationService) {
                try {
                
                    // Notify admin with restaurant details and credentials
                    $this->notificationService->trigger(
                        'Restaurant',
                        'RestaurantOnboarding',
                        null,
                        $data
                    );
                } catch (\Exception $e) {
                    ApiExceptionHandler::handle($e);
                }
            }

            // Match manual registration approval: request KYC and send approval details.
            $createdRequests = [];
            try {
                $createdRequests = app(\Modules\KYC\Services\KycRequestService::class)
                    ->createPendingRequestsForAdmin($admin, $admin->id);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to create KYC requests after automatic registration approval', [
                    'restaurant_id' => $restaurant->id, 'error' => $e->getMessage(),
                ]);
            }

            if ($this->notificationService) {
                try {
                    $this->notificationService->trigger(
                        'Restaurant',
                        'RestaurantApproved',
                        $admin,
                        ['restaurant' => $restaurant->fresh(), 'pending_kyc_requests' => $createdRequests]
                    );
                } catch (\Exception $e) {
                    ApiExceptionHandler::handle($e);
                }
            }

            $message = 'Your account has been created and activated. Please log in to complete your restaurant setup.';
            if ($request->expectsJson()) {
                $request->session()->flash('success', $message);
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect_url' => route('website.partner-login'),
                ], 201);
            }

            return redirect()->route('website.partner-login')
                ->withInput([])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            ApiExceptionHandler::handle($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
