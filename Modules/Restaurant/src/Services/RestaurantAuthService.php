<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\Validator;
use App\Rules\StrongPassword;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Modules\Acl\Models\Admin;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Models\RestaurantEmailOtp;

class RestaurantAuthService
{
    protected int $otpExpiryMinutes = 2;
    protected int $maxOtpAttempts = 5;
    protected int $maxResendsPerWindow = 3;
    protected int $resendCooldownSeconds = 60;

    public function __construct(protected NotificationService $notificationService)
    {
    }

    // public function register(array $data): array
    // {
    //     $validated = validator($data, [
    //         'restaurant_name' => [
    //             'required',
    //             'string',
    //             'min:5',
    //             'max:100',
    //         ],

    //         'contact_person' => [
    //             'required',
    //             'string',
    //             'min:2',
    //             'max:100',
    //             'regex:/^[\pL\s.\'-]+$/u',
    //         ],

    //         'email' => [
    //             'required',
    //             'string',
    //             'email:rfc,dns',
    //             'max:255',
    //             'unique:admins,email',
    //         ],

    //         'password' => [
    //             'required',
    //             'string',
    //             new StrongPassword(),
    //             'confirmed',
    //         ],

    //         'phone' => [
    //             'required',
    //             'integer',
    //             'digits_between:8,16',
    //         ],

    //         'terms_accepted' => [
    //             'required',
    //             'accepted',
    //         ],
    //     ], [
    //         'restaurant_name.required' => 'Restaurant name is required.',
    //         'restaurant_name.min' => 'Restaurant name must be at least 5 characters.',
    //         'restaurant_name.max' => 'Restaurant name must not exceed 100 characters.',

    //         'contact_person.required' => 'Contact person name is required.',
    //         'contact_person.regex' => 'Contact person name may contain only letters, spaces, periods, apostrophes, and hyphens.',

    //         'email.required' => 'Email address is required.',
    //         'email.email' => 'Please enter a valid email address.',
    //         'email.unique' => 'This email address is already registered.',

    //         'password.required' => 'Password is required.',
    //         'password.min' => 'Password must be at least 8 characters.',
    //         'password.max' => 'Password must not exceed 16 characters.',
    //         'password.confirmed' => 'Password confirmation does not match.',

    //         'phone.required' => 'Phone number is required.',
    //         'phone.regex' => 'Please enter a valid phone number.',

    //         'terms_accepted.required' => 'You must accept the terms and conditions.',
    //         'terms_accepted.accepted' => 'You must accept the terms and conditions.',
    //     ])->validate();

    //     $validated['email'] = strtolower(trim($validated['email']));

    //     return DB::transaction(function () use ($validated) {
    //         $admin = Admin::create([
    //             'name' => trim($validated['contact_person']),
    //             'email' => $validated['email'],
    //             'password' => Hash::make($validated['password']),
    //             'status' => 0,
    //             'role_id' => null,
    //             'email_verified_at' => null,
    //         ]);

    //         $restaurant = Restaurant::create([
    //             'admin_id' => $admin->id,
    //             'name' => trim($validated['restaurant_name']),
    //             'slug' => Str::slug($validated['restaurant_name'])
    //                 . '-'
    //                 . Str::random(6),
    //             'phone' => trim($validated['phone']),
    //             'status' => 'draft',
    //             'approval_status' => 'pending',
    //             'onboarding_status' => OnboardingStatus::DRAFT->value,
    //         ]);

    //         try {
    //             $this->notificationService->trigger(
    //                 'Restaurant',
    //                 'RestaurantOnboardingConfirmation',
    //                 $admin,
    //                 [
    //                     'restaurant_name' => $restaurant->name,
    //                     'restaurant_owner_name' => $admin->name,
    //                     'restaurant_id' => $restaurant->id,
    //                     'submitted_date' => $restaurant->created_at
    //                             ?->format('Y-m-d H:i:s')
    //                         ?? now()->format('Y-m-d H:i:s'),
    //                 ]
    //             );
    //         } catch (\Throwable $e) {
    //             \Log::warning(
    //                 'Restaurant onboarding confirmation notification failed during registration.',
    //                 [
    //                     'admin_id' => $admin->id,
    //                     'restaurant_id' => $restaurant->id,
    //                     'error' => $e->getMessage(),
    //                 ]
    //             );
    //         }

    //         $this->sendOtp(
    //             $admin->email,
    //             $admin->id,
    //             false,
    //             'registration_email_verification'
    //         );

    //         return [
    //             'success' => true,
    //             'message' => 'Restaurant account created successfully. Please verify your email to continue.',
    //             'status' => $restaurant->status,
    //             'approval_status' => $restaurant->approval_status,
    //             'onboarding_status' => $restaurant->onboarding_status,
    //             'email_verified' => !empty($admin->email_verified_at),
    //             'otp_sent' => true,
    //         ];
    //     });
    // }

    public function register(array $data): array
    {
        $validated = validator($data, [
            'restaurant_name' => [
                'required',
                'string',
                'min:5',
                'max:100',
            ],

            'contact_person' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\s.\'-]+$/u',
            ],

            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:admins,email',
            ],

            'password' => [
                'required',
                'string',
                new StrongPassword(),
                'confirmed',
            ],

            'phone' => [
                'required',
                'integer',
                'digits_between:8,16',
            ],

            'terms_accepted' => [
                'required',
                'accepted',
            ],
        ], [
            'restaurant_name.required' => 'Restaurant name is required.',
            'restaurant_name.min' => 'Restaurant name must be at least 5 characters.',
            'restaurant_name.max' => 'Restaurant name must not exceed 100 characters.',

            'contact_person.required' => 'Contact person name is required.',
            'contact_person.regex' => 'Contact person name may contain only letters, spaces, periods, apostrophes, and hyphens.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',

            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not exceed 16 characters.',
            'password.confirmed' => 'Password confirmation does not match.',

            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid phone number.',

            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
        ])->validate();

        $validated['email'] = strtolower(trim($validated['email']));

        return DB::transaction(function () use ($validated) {
            $admin = Admin::create([
                'name' => trim($validated['contact_person']),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => 0,
                'role_id' => null,
                'email_verified_at' => null,
            ]);

            $submittedAt = now();

            $restaurant = Restaurant::create([
                'admin_id' => $admin->id,
                'name' => trim($validated['restaurant_name']),
                'slug' => Str::slug($validated['restaurant_name'])
                    . '-'
                    . Str::random(6),
                'phone' => trim($validated['phone']),
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

            try {
                $this->notificationService->trigger(
                    'Restaurant',
                    'RestaurantOnboardingConfirmation',
                    $admin,
                    [
                        'restaurant_name' => $restaurant->name,
                        'restaurant_owner_name' => $admin->name,
                        'restaurant_id' => $restaurant->id,
                        'submitted_date' => $restaurant->created_at
                                ?->format('Y-m-d H:i:s')
                            ?? now()->format('Y-m-d H:i:s'),
                    ]
                );
            } catch (\Throwable $e) {
                \Log::warning(
                    'Restaurant onboarding confirmation notification failed during registration.',
                    [
                        'admin_id' => $admin->id,
                        'restaurant_id' => $restaurant->id,
                        'error' => $e->getMessage(),
                    ]
                );
            }

            $this->sendOtp(
                $admin->email,
                $admin->id,
                false,
                'registration_email_verification'
            );

            return [
                'success' => true,
                'message' => 'Restaurant account created successfully. Please verify your email to continue.',
                'status' => $restaurant->status,
                'approval_status' => $restaurant->approval_status,
                'onboarding_status' => $restaurant->onboarding_status,
                'onboarding_step' => $restaurant->onboarding_step,
                'submitted_at' => $restaurant->submitted_at?->toDateTimeString(),
                'email_verified' => !empty($admin->email_verified_at),
                'otp_sent' => true,
            ];
        });
    }

    public function login(array $credentials): array
    {
        $validated = validator($credentials, [
            'email' => ['required', 'email'],
            'password' => ['required','string','min:8', 'max:18'],
        ])->validate();

        $email = strtolower(trim($validated['email']));

        /*
        |--------------------------------------------------------------------------
        | Find Admin
        |--------------------------------------------------------------------------
        */
        $admin = Admin::where('email', $email)->first();

        /*
        |--------------------------------------------------------------------------
        | Invalid Credentials
        |--------------------------------------------------------------------------
        */
        if (!$admin || !Hash::check($validated['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        */
        if (empty($admin->email_verified_at)) {

            $otpData = $this->sendOtp(
                $admin->email,
                $admin->id,
                false,
                'login_email_verification'
            );

            return [
                'success' => false,
                'verification_required' => true,
                'message' => 'Email verification is required before accessing the restaurant dashboard.',
                'email' => $admin->email,
                'otp_sent' => true,
                'expires_in' => $otpData['expires_in'] ?? null,
                'resend_count' => $otpData['resend_count'] ?? null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Find Restaurant
        |--------------------------------------------------------------------------
        */
        $restaurant = Restaurant::where('admin_id', $admin->id)->first();

        /*
        |--------------------------------------------------------------------------
        | Restaurant Not Found
        |--------------------------------------------------------------------------
        */
        if (!$restaurant) {
            return [
                'success' => false,
                'message' => 'Restaurant profile not found. Please complete restaurant registration.',
                'email_verified' => true,

                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'email_verified_at' => $admin->email_verified_at,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Account Inactive
        |--------------------------------------------------------------------------
        */
        if ($restaurant->status === 'inactive') {
            return [
                'success' => false,
                'message' => 'Your restaurant account has been deactivated. Please contact support.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,

                'account_status' => $restaurant->status,
                'approval_status' => $restaurant->approval_status,
                'onboarding_status' => $restaurant->onboarding_status,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Account Rejected
        |--------------------------------------------------------------------------
        */
        if ($restaurant->approval_status === 'rejected') {
            return [
                'success' => false,
                'message' => 'Your restaurant account has been rejected. Please contact support.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,

                'account_status' => $restaurant->status,
                'approval_status' => $restaurant->approval_status,
                'onboarding_status' => $restaurant->onboarding_status,
            ];
        }


        if ($restaurant->approval_status === 'pending') {
            return [
                'success' => false,
                'message' => 'Your restaurant application is still pending approval. Please wait for the admin to review your application.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,
                'account_status' => $restaurant->status,
                'approval_status' => $restaurant->approval_status,
                'onboarding_status' => $restaurant->onboarding_status,
            ];
        }
        /*
        |--------------------------------------------------------------------------
        | Get Onboarding Status
        |--------------------------------------------------------------------------
        */
        $onboardingStatus = $restaurant->onboarding_status?->value;

        /*
        |--------------------------------------------------------------------------
        | DRAFT
        |
        | User can login and continue onboarding.
        |--------------------------------------------------------------------------
        */
        if (
            $restaurant->status === 'draft' &&
            $onboardingStatus === OnboardingStatus::DRAFT->value
        ) {
            $token = $admin
                ->createToken('restaurant-auth')
                ->plainTextToken;

            return [
                'success' => true,
                'message' => 'Please complete your restaurant onboarding.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,
                'token' => $token,
                // 'user' => [
                //     'name' => $admin->name,
                //     'email' => $admin->email,
                //     'email_verified_at' => $admin->email_verified_at,
                // ],
                'restaurant' => [
                    'status' => $restaurant->status,
                    'approval_status' => $restaurant->approval_status,
                    'onboarding_status' => $restaurant->onboarding_status,
                ],

            ];
        }

        if (
            $restaurant->status === 'active' &&
            $onboardingStatus === OnboardingStatus::DRAFT->value
        ) {
            $token = $admin
                ->createToken('restaurant-auth')
                ->plainTextToken;
            return [
                'success' => true,
                'message' => 'Please complete your restaurant onboarding.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,
                'token' => $token,

                // 'user' => [
                //     'id' => $admin->id,
                //     'name' => $admin->name,
                //     'email' => $admin->email,
                //     'email_verified_at' => $admin->email_verified_at,
                // ],
                'restaurant' => [
                    'status' => $restaurant->status,
                    'approval_status' => $restaurant->approval_status,
                    'onboarding_status' => $restaurant->onboarding_status,
                ],

            ];
        }
        /*
        |--------------------------------------------------------------------------
        | ACTION REQUIRED
        |
        | Admin rejected/returned something.
        | User needs token to login and re-upload KYC/documents.
        |--------------------------------------------------------------------------
        */
        if (
            $onboardingStatus === OnboardingStatus::ACTION_REQUIRED->value
        ) {
            $token = $admin
                ->createToken('restaurant-auth')
                ->plainTextToken;

            return [
                'success' => true,
                'message' => 'Action is required on your restaurant onboarding. Please update the requested information or re-upload the required documents.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,
                'token' => $token,

                // 'user' => [
                //     'id' => $admin->id,
                //     'name' => $admin->name,
                //     'email' => $admin->email,
                //     'email_verified_at' => $admin->email_verified_at,
                // ],

                'restaurant' => [
                    'status' => $restaurant->status,
                    'approval_status' => $restaurant->approval_status,
                    'onboarding_status' => $restaurant->onboarding_status,
                ],

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SUBMITTED
        |
        | Onboarding completed and submitted for review.
        | User can login but should not edit submitted data unless allowed.
        |--------------------------------------------------------------------------
        */
        if (
            $onboardingStatus === OnboardingStatus::SUBMITTED->value
        ) {
            $token = $admin
                ->createToken('restaurant-auth')
                ->plainTextToken;

            return [
                'success' => true,
                'message' => 'Your restaurant onboarding has been submitted and is waiting for admin review.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,
                'token' => $token,

                // 'user' => [
                //     'id' => $admin->id,
                //     'name' => $admin->name,
                //     'email' => $admin->email,
                //     'email_verified_at' => $admin->email_verified_at,
                // ],
                'restaurant' => [
                    // 'id' => $restaurant->id,
                    'status' => $restaurant->status,
                    'approval_status' => $restaurant->approval_status,
                    'onboarding_status' => $restaurant->onboarding_status,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | UNDER REVIEW
        |
        | Admin is reviewing restaurant/KYC.
        |--------------------------------------------------------------------------
        */
        if (
            $onboardingStatus === OnboardingStatus::UNDER_REVIEW->value
        ) {
            $token = $admin
                ->createToken('restaurant-auth')
                ->plainTextToken;

            return [
                'success' => true,
                'message' => 'Your restaurant onboarding is currently under review.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,
                'token' => $token,

                // 'user' => [
                //     'id' => $admin->id,
                //     'name' => $admin->name,
                //     'email' => $admin->email,
                //     'email_verified_at' => $admin->email_verified_at,
                // ],

                'restaurant' => [
                    // 'id' => $restaurant->id,
                    'status' => $restaurant->status,
                    'approval_status' => $restaurant->approval_status,
                    'onboarding_status' => $restaurant->onboarding_status,
                ],

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | REJECTED
        |
        | Restaurant application rejected.
        |--------------------------------------------------------------------------
        */
        if (
            $restaurant->approval_status === 'rejected' ||
            $onboardingStatus === OnboardingStatus::REJECTED->value
        ) {
            return [
                'success' => false,
                'message' => 'Your restaurant application has been rejected. Please review the rejection details and contact support if required.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,

                'account_status' => $restaurant->status,
                'approval_status' => $restaurant->approval_status,
                'onboarding_status' => $restaurant->onboarding_status,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | APPROVED + ACTIVE
        |
        | Normal dashboard login.
        |--------------------------------------------------------------------------
        */
        if (
            $restaurant->approval_status === 'approved' &&
            $restaurant->status === 'active'
        ) {
            $token = $admin
                ->createToken('restaurant-auth')
                ->plainTextToken;

            return [
                'success' => true,
                'message' => 'Login successful.',
                'email_verified' => $restaurant->admin->email_verified_at !== null,
                'token' => $token,

                // 'user' => [
                //     'id' => $admin->id,
                //     'name' => $admin->name,
                //     'email' => $admin->email,
                //     'email_verified_at' => $admin->email_verified_at,
                // ],

                'restaurant' => [
                    // 'id' => $restaurant->id,
                    'status' => $restaurant->status,
                    'approval_status' => $restaurant->approval_status,
                    'onboarding_status' => $restaurant->onboarding_status,
                ],

            ];
        }
        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */
        return [
            'success' => false,
            'message' => 'Your restaurant account is not ready for login. Please contact support.',
            'email_verified' => $restaurant->admin->email_verified_at !== null,

            'account_status' => $restaurant->status,
            'approval_status' => $restaurant->approval_status,
            'onboarding_status' => $restaurant->onboarding_status,
        ];
    }

    public function sendOtp(string $email, ?int $adminId = null, bool $isResend = false, string $type = 'email_verification'): array
    {
        if ($type === RestaurantOnboardingOtpService::TYPE) {
            return app(RestaurantOnboardingOtpService::class)->send($email);
        }

        $admin = $adminId
            ? Admin::find($adminId)
            : Admin::where('email', strtolower(trim($email)))->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'email' => ['Restaurant account not found for this email.'],
            ]);
        }

        $email = strtolower(trim($admin->email));
        $latestRecord = RestaurantEmailOtp::where('email', $email)
            ->where('type', $type)
            ->orderByDesc('last_sent_at')
            ->first();

        if ($latestRecord && $latestRecord->last_sent_at && $latestRecord->last_sent_at->diffInSeconds(now()) < $this->resendCooldownSeconds && $isResend) {
            throw ValidationException::withMessages([
                'email' => ['Please wait before requesting a new OTP.'],
            ]);
        }

        if ($latestRecord && $latestRecord->last_sent_at && $latestRecord->last_sent_at->diffInHours(now()) < 24 && $latestRecord->resend_count >= $this->maxResendsPerWindow && $isResend) {
            throw ValidationException::withMessages([
                'email' => ['OTP resend limit reached. Please try again later.'],
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        // $otp = 123456;

        $expiresAt = now()->addMinutes($this->otpExpiryMinutes);

        $otpRecord = RestaurantEmailOtp::updateOrCreate(
            ['email' => $email, 'type' => $type],
            [
                'admin_id' => $admin->id,
                'type' => $type,
                'expires_at' => $expiresAt,
                'attempts' => 0,
                'otp' => $otp,
                'last_sent_at' => now(),
                'resend_count' => $latestRecord ? (int) $latestRecord->resend_count + 1 : 1,
                'verified_at' => null,
            ]
        );

        $restaurant = Restaurant::where('admin_id', $admin->id)->first();

        try {
            $this->notificationService->trigger(
                'Restaurant',
                'RestaurantEmailVerification',
                $admin,
                [
                    'email' => $email,
                    'otp' => $otp,
                    'restaurant_name' => $restaurant?->name ?? $admin->name,
                    'expires_minutes' => $this->otpExpiryMinutes,
                    'purpose' => $type,
                ]
            );
        } catch (\Throwable $e) {
            \Log::warning('Restaurant OTP email notification failed.', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'success' => true,
            'message' => 'Verification code sent to your email.',
            'email' => $email,
            'expires_in' => $this->otpExpiryMinutes * 60,
            'resend_count' => $otpRecord->resend_count,
        ];
    }

    public function resendOtp(string $email, string $type = 'email_verification'): array
    {
        return $this->sendOtp($email, null, true, $type);
    }

    public function verifyOtp(string $email, string $otp, string $type = 'email_verification'): array
    {
        if ($type === RestaurantOnboardingOtpService::TYPE) {
            return app(RestaurantOnboardingOtpService::class)->verify($email, $otp);
        }

        $admin = Admin::where('email', strtolower(trim($email)))->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'email' => ['Restaurant account not found for this email.'],
            ]);
        }

        $record = RestaurantEmailOtp::where('email', strtolower(trim($email)))
            ->where('type', $type)
            ->whereNull('verified_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$record) {
            // Check if any record exists (including already-verified ones) to return a clearer message
            $latestAny = RestaurantEmailOtp::where('email', strtolower(trim($email)))->where('type', $type)->orderByDesc('created_at')->first();
            if ($latestAny) {
                if ($latestAny->verified_at) {
                    throw ValidationException::withMessages([
                        'otp' => ['The verification code has already been used. Please request a new one.'],
                    ]);
                }

                if ($latestAny->expires_at && $latestAny->expires_at->isPast()) {
                    throw ValidationException::withMessages([
                        'otp' => ['The verification code has already been used. Please request a new one.'],
                    ]);
                }
            }

            throw ValidationException::withMessages([
                'otp' => ['No verification code has been sent for this email.'],
            ]);
        }

        if ($record->expires_at && $record->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp' => ['The verification code has already been used. Please request a new one.'],
            ]);
        }

        if ($record->otp !== trim((string) $otp)) {
            $record->attempts = (int) $record->attempts + 1;
            $record->save();

            if ((int) $record->attempts >= $this->maxOtpAttempts) {
                throw ValidationException::withMessages([
                    'otp' => ['Too many invalid attempts. Please request a new verification code.'],
                ]);
            }

            throw ValidationException::withMessages([
                'otp' => ['Invalid verification code. Please try again.'],
            ]);
        }
        // If this is email verification, set admin email_verified_at

        if (!$admin->email_verified_at) {
            $admin->email_verified_at = now();
            $admin->save();
        }

        $record->markAsVerified();
        $restaurant = Restaurant::where('admin_id', $admin->id)->first();
        return [
            'success' => true,
            'message' => $restaurant->approval_status === 'approved' ? 'Verification successful.' : 'Verification successful. Your account is pending approval on Admin.',
            'verified' => true,
        ];
    }
    /**
     * Reset password using OTP. Verifies the OTP and updates admin password.
     * This is intended only for password reset flow.
     */

    public function resetPasswordWithOtp(string $email, string $newPassword): array
    {
        $email = strtolower(trim($email));

        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'email' => ['Restaurant account not found for this email.'],
            ]);
        }

        if (!$admin->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => [
                    'Email is not verified. Please verify your email by logging in to your account.',
                ],
            ]);
        }
        Validator::make(['password' => $newPassword], [
            'password' => ['required', 'string', new StrongPassword()],
        ])->validate();
        $admin->password = Hash::make($newPassword);
        $admin->save();

        app(RestaurantPasswordResetNotificationService::class)->sendForOwner($admin);

        return [
            'success' => true,
            'message' => 'Password updated successfully.',
        ];
    }

}
