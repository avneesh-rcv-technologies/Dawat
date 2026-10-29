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
            'email' => ['bail', 'required', 'string', 'max:255', 'email:rfc,dns', 'unique:admins,email'],
        ], [
            'email.email' => 'Enter a valid email address with a domain that can receive email.',
            'email.unique' => 'This email address is already registered. Please use a different email address or sign in.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email is valid and can be used for registration.',
        ]);
    }

    public function submit(Request $request, RestaurantOnboardingOtpService $otpService)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'email' => 'bail|required|string|max:255|email:rfc,dns|unique:admins,email',
            'restaurant_name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'state_id' => ['required', 'integer', Rule::exists('country_states', 'id')
                ->where('country_id', is_scalar($request->input('country_id')) ? $request->input('country_id') : null)],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')
                ->where('country_id', is_scalar($request->input('country_id')) ? $request->input('country_id') : null)
                ->where('state_id', is_scalar($request->input('state_id')) ? $request->input('state_id') : null)],
            'address_line1' => 'required|string|max:500',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
            'email_verification_token' => ['nullable', 'string', 'size:64'],
        ]);

        $verificationToken = $validated['email_verification_token'] ?? '';
        if (!$otpService->isVerified($validated['email'], $verificationToken)) {
            $request->merge(['email_verification_token' => null]);
            throw ValidationException::withMessages(['email' => ['Please verify your email before submitting your application.']]);
        }

        try {
            DB::beginTransaction();
            // Restaurant address fields store names; IDs validate the selected hierarchy.
            $validated['country'] = Country::findOrFail($validated['country_id'])->name;
            $validated['state'] = CountryState::findOrFail($validated['state_id'])->name;
            $validated['city'] = City::findOrFail($validated['city_id'])->name;
            // Use provided password from onboarding form
            $plainPassword = $validated['password'];

            // Create Admin (Restaurant Owner)
            $admin = Admin::create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
                'status' => 0,
                'role_id' => null,
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
                'state' => $validated['state'],
                'country' => $validated['country'],
                'address_line1' => $validated['address_line1'],
                'is_accepting_orders' => false,
                'status' => 'draft',
                'approval_status' => 'pending',
                'onboarding_status' => OnboardingStatus::DRAFT->value,
                'onboarding_step' => OnboardingStep::REGISTRATION->order(),
                'submitted_at' => $submittedAt,
            ]);

            $restaurant->onboardingSteps()->createMany(array_map(
                fn (OnboardingStep $step) => [
                    'step_key' => $step->value,
                    'status' => $step === OnboardingStep::REGISTRATION
                        ? OnboardingStatus::SUBMITTED->value
                        : OnboardingStatus::DRAFT->value,
                    'submitted_at' => $step === OnboardingStep::REGISTRATION
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

            // Send confirmation notification to restaurant owner
            if ($this->notificationService) {
                try {
                    $this->notificationService->trigger(
                        'Restaurant',
                        'RestaurantOnboardingConfirmation',
                        $admin->email,
                        [
                            'restaurant_name' => $validated['restaurant_name'],
                            'restaurant_owner_name' => $validated['owner_name'],
                            'restaurant_id' => $restaurant->id,
                            'submitted_date' => $restaurant->created_at->format('Y-m-d H:i:s'),
                        ]
                    );
                } catch (\Exception $e) {
                    ApiExceptionHandler::handle($e);
                }
            }

            return redirect()->route('restaurant.onboarding.form')
                ->withInput([])
                ->with('success', 'Your application has been submitted successfully! We will review it and get back to you soon.');
        } catch (\Exception $e) {
            DB::rollBack();

            ApiExceptionHandler::handle($e);

            return back()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
