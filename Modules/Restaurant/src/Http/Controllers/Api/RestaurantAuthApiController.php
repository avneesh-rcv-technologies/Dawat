<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Rules\StrongPassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantAuthService;
use Modules\Restaurant\Services\RestaurantService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;
use Modules\Countries\Models\City;
use Modules\Filemanager\Services\FileService;
use Modules\Restaurant\Services\RestaurantCacheService;

class RestaurantAuthApiController extends Controller
{
    public function __construct(protected RestaurantAuthService $restaurantAuthService)
    {
    }
    public function register(Request $request): JsonResponse
    {
        try {
            $result = $this->restaurantAuthService->register($request->all());

            return response()->json($result, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }
    public function login(Request $request): JsonResponse
    {
        try {
            $result = $this->restaurantAuthService->login($request->all());

            if (($result['success'] ?? false) === false && ($result['verification_required'] ?? false) === true) {
                return response()->json($result, 403);
            }

            return response()->json($result, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
                'errors' => $e->errors(),
            ], 401);
        }
    }

    public function sendOtp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'type' => ['required', 'string'],
            ]);

            $result = $this->restaurantAuthService->sendOtp($validated['email'], null, false, $validated['type']);

            return response()->json($result, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to send email verification code.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function resendOtp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'type' => ['required', 'string'],
            ]);

            return response()->json($this->restaurantAuthService->sendOtp($validated['email'], null, true, $validated['type']), 200);
        } catch (ValidationException $e) {
            $firstError = $e->errors();
            $message = $firstError['email'][0] ?? 'Unable to resend verification code.';

            return response()->json([
                'success' => false,
                'error' => $message,
                'errors' => $e->errors(),
            ], $message === 'OTP resend limit reached. Please try again later.' ? 429 : 422);
        }
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'otp' => ['required', 'string', 'size:6'],
                'type' => ['required', 'string'],
            ]);

            $result = $this->restaurantAuthService->verifyOtp($validated['email'], $validated['otp'], $validated['type']);

            return response()->json($result, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Reset password for a verified restaurant email.
     * Request body: email, password, password_confirmation
     * Optional legacy fields: otp, type
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string', new StrongPassword(), 'confirmed'],
            ]);

            $service = app(RestaurantAuthService::class);
            $result = $service->resetPasswordWithOtp(
                $validated['email'],
                $validated['password']
            );

            return response()->json($result, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('ResetPassword error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to reset password.'], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $admin = $request->user('sanctum');

        /*
        |--------------------------------------------------------------------------
        | Admin Information
        |--------------------------------------------------------------------------
        */
        $adminInfo = [
            // 'id' => $admin->id,
            'name' => $admin->name ?? $admin->full_name ?? null,
            'email' => $admin->email ?? null,
            'logo' => $admin->logoUrl() ?? null,
            'email_verified' => !is_null($admin->email_verified_at),
        ];

        /*
        |--------------------------------------------------------------------------
        | Get Restaurant
        |--------------------------------------------------------------------------
        */
        $restaurant = Restaurant::where('admin_id', $admin->id)
            ->with([
                'hours',
                'holidays',
                'addresses',
                'onboardingStatusHistories'
            ])
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Restaurant Not Found
        |--------------------------------------------------------------------------
        */
        if (!$restaurant) {
            return response()->json([
                'success' => true,
                'message' => 'Restaurant profile not found.',
                'user' => $adminInfo,
                'restaurant' => null,
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Gallery
        |--------------------------------------------------------------------------
        */
        $fileService = app(FileService::class);

        $galleryRaw = $fileService->getFiles(
            'restaurant_gallery',
            $restaurant->id
        ) ?: [];

        $gallery = collect($galleryRaw)
            ->map(function ($img) {
                return [
                    'file_id' => is_array($img)
                        ? ($img['id'] ?? null)
                        : ($img->id ?? null),

                    'file_link_id' => is_array($img)
                        ? ($img['file_link_id'] ?? null)
                        : ($img->file_link_id ?? null),

                    'url' => is_array($img)
                        ? ($img['url'] ?? null)
                        : ($img->url ?? null),

                    'filename' => is_array($img)
                        ? ($img['original_name'] ?? $img['file_name'] ?? null)
                        : ($img->original_name ?? $img->file_name ?? null),

                    'position' => is_array($img)
                        ? ($img['position'] ?? null)
                        : ($img->position ?? null),

                    'created_at' => is_array($img)
                        ? ($img['created_at'] ?? null)
                        : ($img->created_at ?? null),
                ];
            })
            ->filter(fn($img) => !empty($img['file_link_id']))
            ->unique('file_link_id')
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Final Response
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'success' => true,
            'message' => 'Welcome to your dashboard.',

            'user' => $adminInfo,

            'restaurant' => $restaurant->makeHidden([
                'id',
                'status',
                'onboarding_status',
                'approval_status',
                'status',
                'approved_by',
                'approved_at',
                'admin_id',
                'created_at',
                'updated_at',
            ])->toArray(),

            'status' => [
                'account_status' => $restaurant->status,
                'approval_status' => $restaurant->approval_status,
                'onboarding_status' => $restaurant->onboarding_status?->value
                    ?? $restaurant->onboarding_status,
            ],

            'gallery' => $gallery,
            'logo' => fn_get_image('restaurant', $restaurant->id)['url'] ?? null,
        ], 200);
    }


    public function logout(Request $request): JsonResponse
    {
        $request->user('sanctum')?->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Restaurant logged out successfully.',
        ]);
    }

    /**
     * Get authenticated restaurant's settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        $admin = $request->user('sanctum');
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $restaurant = Restaurant::where('admin_id', $admin->id)->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
        }

        $settings = [
            'food_type' => $restaurant->food_type ?? 'all',
            'is_takeaway' => (bool) ($restaurant->is_takeaway ?? false),
            'is_delivery' => (bool) ($restaurant->is_delivery ?? false),
            'is_accepting_orders' => (bool) ($restaurant->is_accepting_orders ?? false),
            'order_time_limit' => $restaurant->order_time_limit ?? 48,
            'cancellation_time_limit' => $restaurant->cancellation_time_limit ?? 15,
            'allow_order_cancellation' => (bool) ($restaurant->allow_order_cancellation ?? false),
            'cancellable_statuses' => is_array($restaurant->cancellable_statuses)
                ? $restaurant->cancellable_statuses
                : (is_string($restaurant->cancellable_statuses) ? json_decode($restaurant->cancellable_statuses, true) : ($restaurant->cancellable_statuses ?? ['P','O','H'])),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Restaurant settings fetched successfully.',
            'data' => $settings,
        ], 200);
    }

    // Update restaurant details (owner) - name, phone, description, password, image
    public function updateRestaurant(Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'nullable',
                'string',
                'min:2',
                'max:150',
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'integer',
                'digits_between:8,16',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],
            'password' => [
                'sometimes',
                'nullable',
                'string',
                new StrongPassword(),
                'confirmed',
            ],
            'image' => [
                'sometimes',
                'file',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2000',
            ],
        ], [
            'image.uploaded' => 'The image could not be uploaded. Please check the file size and try again.',
            'image.image' => 'Please upload a valid image file.',
            'image.mimes' => 'Please upload an image in PNG, JPG, JPEG, or WEBP format.',
            'image.max' => 'Please upload an image smaller than 2 MB.',
            'image.file' => 'Please select a valid image file.',
            'description.max' => 'The description may not be greater than 150 characters.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.max' => 'The password may not be greater than 16 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'phone.regex' => 'Please enter a valid phone number.',
        ]);

        $admin = $request->user('sanctum');

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $restaurant =Restaurant::where(
            'admin_id',
            $admin->id
        )->first();

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found.',
            ], 404);
        }

        $restaurantService = app(RestaurantService::class);

        /*
         * Build update data.
         *
         * array_key_exists() is important here because
         * it also detects fields whose value is NULL.
         */
        $updateData = [];

        if (array_key_exists('name', $validated)) {
            $updateData['name'] = $validated['name'];
        }

        if (array_key_exists('phone', $validated)) {
            $updateData['phone'] = $validated['phone'];
        }

        if (array_key_exists('description', $validated)) {
            $updateData['description'] = $validated['description'];
        }

        if (array_key_exists('password', $validated)) {
            $updateData['password'] = $validated['password'];
        }

        /*
         * Update restaurant details only when
         * there is something to update.
         */
        if (!empty($updateData)) {
            $restaurantService->updateGeneral(
                $restaurant->id,
                $updateData
            );
        }

        /*
         * Handle restaurant image.
         * Existing image will be removed/replaced.
         */
        if ($request->hasFile('image')) {
            fn_upload_image(
                $request->file('image'),
                'restaurant',
                $restaurant->id,
                true
            );
        }

        /*
         * Get fresh restaurant data.
         */
        $fresh = $restaurantService->findRestaurantById(
            $restaurant->id,
            ['admin']
        );

        return response()->json([
            'success' => true,
            'message' => 'Restaurant updated successfully.',
            'restaurant' => $fresh,
        ], 200);

    } catch (ValidationException $e) {

        Log::warning('UpdateRestaurant validation failed', [
            'errors' => $e->errors(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors(),
        ]);

    } catch (\Throwable $e) {

        Log::error('UpdateRestaurant error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Unable to update restaurant.',
        ]);
    }
}
    /**
     * Update authenticated restaurant owner profile (name, avatar, password)
     */
    // public function updateProfile(Request $request): JsonResponse
    // {
    //     try {
    //         $validated = $request->validate([
    //             'name' => ['sometimes', 'string', 'min:2', 'max:150'],
    //             'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
    //             'image' => ['sometimes', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    //         ]);

    //         $admin = $request->user('sanctum');
    //         if (!$admin) {
    //             return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
    //         }

    //         // Update name
    //         if (isset($validated['name'])) {
    //             $admin->name = $validated['name'];
    //         }

    //         // Update password if provided
    //         if (!empty($validated['password'])) {
    //             $admin->password = Hash::make($validated['password']);
    //         }

    //         // Handle avatar upload
    //         if ($request->hasFile('image')) {
    //             fn_upload_image($request->file('image'), 'avatar', $admin->id, true);
    //         }

    //         $admin->save();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Profile updated successfully.',
    //             'user' => [
    //                 'id' => $admin->id,
    //                 'name' => $admin->name,
    //                 'email' => $admin->email ?? null,
    //             ],
    //         ], 200);
    //     } catch (ValidationException $e) {
    //         Log::warning('UpdateProfile validation failed', ['errors' => $e->errors()]);
    //         return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
    //     } catch (\Throwable $e) {
    //         Log::error('UpdateProfile error', ['error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Unable to update profile.'], 500);
    //     }
    // }

    // Update restaurant address via API for authenticated restaurant owner
    public function updateAddress(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'address_line1' => ['required', 'string', 'max:255'],
                'address_line2' => ['nullable', 'string', 'max:255'],
                'country_id' => ['required', 'integer', 'exists:countries,id'],
                'state_id' => ['required', 'integer', 'exists:country_states,id'],
                'city_id' => ['required', 'integer', 'exists:cities,id'],
                'postal_code' => ['required', 'string', 'max:20'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ]);

            $admin = $request->user('sanctum');
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurant) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
            }

            $country = Country::find($validated['country_id']);
            $state = CountryState::find($validated['state_id']);
            $city = City::find($validated['city_id']);

            $data = array_merge($validated, [
                'country_name' => $country?->name,
                'state_name' => $state?->name,
                'city_name' => $city?->name,
            ]);

            $restaurantService = app(RestaurantService::class);
            $restaurantService->updateAddress($restaurant->id, $data);

            return response()->json(['success' => true, 'message' => 'Address updated successfully.'], 200);
        } catch (ValidationException $e) {
            Log::warning('UpdateAddress validation failed', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('UpdateAddress error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to update address.'], 500);
        }
    }

    // Update working hours via API (mobile)
    public function updateHours(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            $validator = Validator::make($data, [
                'hours' => ['required', 'array'],
                'is_open_whole_day' => ['sometimes', 'boolean'],
            ], [], []);

            // Basic structure validation for nested items (will be further validated below)
            $validator->after(function ($v) use ($data) {
                // If no hours provided or not array, base validator will handle
                $isOpenWholeDay = data_get($data, 'is_open_whole_day', false);

                if ($isOpenWholeDay) {
                    // when whole day is enabled, no further interval validation required
                    return;
                }

                $hours = data_get($data, 'hours', []);
                $timeRegex = '/^([01]\d|2[0-3]):[0-5]\d$/';

                foreach ($hours as $day => $value) {
                    // normalize single associative interval into array
                    if (is_array($value) && count($value) && array_keys($value) !== range(0, count($value) - 1)) {
                        $segments = [$value];
                    } elseif (is_array($value)) {
                        $segments = $value;
                    } else {
                        $v->errors()->add("hours.{$day}", 'Invalid format for day intervals.');
                        continue;
                    }

                    // Validate each segment
                    $normalizedSegments = [];
                    foreach ($segments as $idx => $seg) {
                        if (!is_array($seg)) {
                            $v->errors()->add("hours.{$day}.{$idx}", 'Interval must be an object with open/from/to.');
                            continue;
                        }

                        $open = isset($seg['open']) ? (bool) $seg['open'] : false;
                        $from = $seg['from'] ?? null;
                        $to = $seg['to'] ?? null;

                        if ($open) {
                            if (empty($from) || empty($to)) {
                                if (empty($from))
                                    $v->errors()->add("hours.{$day}.{$idx}.from", 'The from time is required when open is true.');
                                if (empty($to))
                                    $v->errors()->add("hours.{$day}.{$idx}.to", 'The to time is required when open is true.');
                                continue;
                            }

                            if (!preg_match($timeRegex, $from)) {
                                $v->errors()->add("hours.{$day}.{$idx}.from", 'The from time must be in HH:MM 24-hour format.');
                                continue;
                            }
                            if (!preg_match($timeRegex, $to)) {
                                $v->errors()->add("hours.{$day}.{$idx}.to", 'The to time must be in HH:MM 24-hour format.');
                                continue;
                            }

                            $fromMin = $this->minutesFromTime($from);
                            $toMin = $this->minutesFromTime($to);

                            if ($fromMin >= $toMin) {
                                $v->errors()->add("hours.{$day}.{$idx}", 'The from time must be strictly less than the to time.');
                                continue;
                            }

                            $normalizedSegments[] = ['from' => $fromMin, 'to' => $toMin, 'orig_from' => $from, 'orig_to' => $to, 'idx' => $idx];
                        } else {
                            // if not open, we don't require from/to and skip overlap/time checks
                            // but keep a placeholder for ordering
                            $normalizedSegments[] = ['from' => null, 'to' => null, 'orig_from' => $from, 'orig_to' => $to, 'idx' => $idx, 'open' => false];
                        }
                    }

                    // Now check overlaps among only open segments
                    $openSegments = array_values(array_filter($normalizedSegments, fn($s) => isset($s['from']) && $s['from'] !== null));

                    // Sort by 'from'
                    usort($openSegments, function ($a, $b) {
                        return $a['from'] <=> $b['from'];
                    });

                    for ($i = 1; $i < count($openSegments); $i++) {
                        $prev = $openSegments[$i - 1];
                        $cur = $openSegments[$i];

                        // Overlap if prev.to > cur.from (touching endpoints allowed)
                        if ($prev['to'] > $cur['from']) {
                            // map back to original index for error location
                            $v->errors()->add("hours.{$day}.{$cur['idx']}", 'This time slot overlaps with another time slot.');
                        }
                    }
                }
            });

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                Log::warning('UpdateHours validation failed', ['errors' => $errors]);
                return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors], 422);
            }

            $admin = $request->user('sanctum');
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurant) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
            }

            $hours = $data['hours'];
            $isOpenWholeDay = $data['is_open_whole_day'] ?? false;

            $restaurantService = app(RestaurantService::class);
            $restaurantService->updateHours($restaurant->id, $hours, (bool) $isOpenWholeDay);

            return response()->json(['success' => true, 'message' => 'Working hours updated successfully.'], 200);
        } catch (ValidationException $e) {
            Log::warning('UpdateHours validation failed', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('UpdateHours error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to update working hours.'], 500);
        }
    }

    private function minutesFromTime(string $time): int
    {
        [$h, $m] = explode(':', $time);
        return ((int) $h) * 60 + ((int) $m);
    }

    // Update holidays via API (mobile) - replace holiday list for the restaurant
    public function updateHolidays(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'holidays' => ['nullable', 'array'],
                'holidays.*.date' => ['sometimes', 'required', 'date'],
                'holidays.*.reason' => ['nullable', 'string', 'max:255'],
                'holidays.*.is_closed' => ['nullable', 'boolean'],
            ]);

            $admin = $request->user('sanctum');
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurant) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
            }

            $holidays = $validated['holidays'] ?? [];

            $restaurantService = app(RestaurantService::class);
            $restaurantService->updateHolidays($restaurant->id, $holidays);

            return response()->json(['success' => true, 'message' => 'Holidays saved successfully.'], 200);
        } catch (ValidationException $e) {
            Log::warning('UpdateHolidays validation failed', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('UpdateHolidays error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to save holidays.'], 500);
        }
    }

    // Get restaurant holidays for authenticated restaurant owner
    public function getHolidays(Request $request): JsonResponse
    {
        $admin = $request->user('sanctum');

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
        }

        try {
            $restaurantService = app(\Modules\Restaurant\Services\RestaurantService::class);
            $holidays = $restaurantService->getFormattedHolidays($restaurant);

            return response()->json([
                'success' => true,
                'holidays' => $holidays,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('GetHolidays error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to fetch holidays.'], 500);
        }
    }

    // Get restaurant working hours for authenticated restaurant owner
    public function getHours(Request $request): JsonResponse
    {
        $admin = $request->user('sanctum');

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
        }

        try {
            $restaurantService = app(\Modules\Restaurant\Services\RestaurantService::class);
            $formatted = $restaurantService->getFormattedHours($restaurant);

            // Build day-level structure including per-day `open` status and intervals
            $hoursWithStatus = [];
            $orderedDays = $restaurantService->getOrderedDays();
            foreach ($orderedDays as $day) {
                $raw = $formatted[$day] ?? [];

                // Normalize segments to numeric array
                if (is_array($raw) && count($raw) && array_keys($raw) !== range(0, count($raw) - 1)) {
                    $segments = [$raw];
                } elseif (is_array($raw)) {
                    $segments = $raw;
                } else {
                    $segments = [];
                }

                $isOpenDay = false;
                $intervals = [];
                foreach ($segments as $seg) {
                    $segOpen = isset($seg['open']) ? (bool) $seg['open'] : false;
                    if ($segOpen)
                        $isOpenDay = true;
                    $intervals[] = [
                        'open' => $segOpen,
                        'from' => $seg['from'] ?? null,
                        'to' => $seg['to'] ?? null,
                    ];
                }

                $hoursWithStatus[$day] = [
                    'open' => $isOpenDay,
                    'intervals' => $intervals,
                ];
            }

            $hoursData = [
                'id' => $restaurant->hours->id ?? null,
                'is_open_whole_day' => $formatted['is_open_whole_day'] ?? false,
                'hours' => $hoursWithStatus,
            ];

            return response()->json([
                'success' => true,
                'hours' => $hoursData,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('GetHours error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to fetch hours.'], 500);
        }
    }

    // Update restaurant settings via API (mobile)
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'food_type' => ['nullable', 'in:all,veg,non_veg,vegan'],
                'status' => ['nullable', 'in:draft,active,inactive'],
                'order_time_limit' => ['nullable', 'integer', 'min:0', 'max:1440'],
                'cancellation_time_limit' => ['nullable', 'integer', 'min:0', 'max:120'],
                'cancellable_statuses' => ['nullable', 'array'],
                'cancellable_statuses.*' => ['string'],
                'is_takeaway' => ['sometimes', 'boolean'],
                'is_delivery' => ['sometimes', 'boolean'],
                'is_accepting_orders' => ['sometimes', 'boolean'],
                'allow_order_cancellation' => ['sometimes', 'boolean'],
            ]);

            $data['is_takeaway'] = $request->boolean('is_takeaway');
            $data['is_delivery'] = $request->boolean('is_delivery');
            $data['is_accepting_orders'] = $request->boolean('is_accepting_orders');
            $data['allow_order_cancellation'] = $request->boolean('allow_order_cancellation');

            $admin = $request->user('sanctum');
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurant) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
            }

            $restaurantService = app(RestaurantService::class);
            $data['status'] = $data['status'] ?? $restaurant->status;
            $restaurantService->updateSettings($restaurant->id, $data);

            return response()->json(['success' => true, 'message' => 'Settings updated successfully.'], 200);
        } catch (ValidationException $e) {
            Log::warning('UpdateSettings validation failed', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('UpdateSettings error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to update settings.'], 500);
        }
    }

    /**
     * Update restaurant gallery images via API (mobile)
     * Accepts up to 5 images, only jpg/png/jpeg, max size 2MB each.
     */
    public function updateGallery(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'images' => ['nullable', 'array', 'max:5'],
                'images.*' => ['file', 'image', 'mimes:jpeg,jpg,png', 'max:2048'], // 2048 KB = 2 MB
                'remove_ids' => ['nullable', 'array'],
                'remove_ids.*' => ['integer'],
            ]);

            $admin = $request->user('sanctum');
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $restaurant = \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->first();
            if (!$restaurant) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found.'], 404);
            }

            $fileService = app(FileService::class);

            // Handle replace (delete all existing) or selective removals
            $existingFiles = $fileService->getFiles('restaurant_gallery', $restaurant->id);

            // (replace flag removed) — use `remove_ids` to delete specific file_link ids before upload

            // Remove specific file link ids if provided
            if (!empty($validated['remove_ids']) && is_array($validated['remove_ids'])) {
                foreach ($validated['remove_ids'] as $removeId) {
                    // attempt delete; ignore failures
                    try {
                        $fileService->deleteFileWithId($removeId);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to delete gallery file', ['id' => $removeId, 'error' => $e->getMessage()]);
                    }
                }

                // refresh existing files list after deletion
                $existingFiles = $fileService->getFiles('restaurant_gallery', $restaurant->id);
            }

            $existingCount = is_array($existingFiles) ? count($existingFiles) : $existingFiles->count();
            $uploadFiles = $request->hasFile('images') ? $request->file('images') : [];
            $uploadCount = is_array($uploadFiles) ? count($uploadFiles) : ($uploadFiles ? 1 : 0);

            // ensure total after upload does not exceed 5
            if ($existingCount + $uploadCount > 5) {
                return response()->json(['success' => false, 'message' => 'Maximum 5 gallery images are allowed.'], 422);
            }

            // Upload new images (if any)
            if (!empty($uploadFiles)) {
                foreach ($uploadFiles as $index => $image) {
                    // position: append after existing; use position index as existingCount + index
                    $position = $existingCount + $index;
                    $fileService->uploadFile($image, 'restaurant_gallery', $restaurant->id, 'A', $position);
                }
            }

            // Clear restaurant gallery cache so next read returns updated images
            try {
                $cacheService = app(RestaurantCacheService::class);
                $cacheService->forgetGallery($restaurant->id);
            } catch (\Throwable $e) {
                Log::warning('Failed to forget gallery cache', ['error' => $e->getMessage()]);
            }

            // Return formatted gallery list (urls)
            $restaurantGalleryRaw = $fileService->getFiles('restaurant_gallery', $restaurant->id);
            $restaurantGallery = collect($restaurantGalleryRaw)->map(function ($image) {
                return [
                    'id' => $image['id'] ?? ($image->id ?? null),
                    'url' => $image['url'] ?? ($image->url ?? null),
                    'filename' => $image['filename'] ?? ($image->filename ?? null),
                ];
            })->unique('id')->values()->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Gallery updated successfully.',
                'restaurant_gallery' => $restaurantGallery,
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('UpdateGallery validation failed', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('UpdateGallery error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to update gallery.'], 500);
        }
    }
}
