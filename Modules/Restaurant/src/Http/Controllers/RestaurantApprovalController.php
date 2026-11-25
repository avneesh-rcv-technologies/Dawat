<?php

namespace Modules\Restaurant\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Countries\Models\City;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycType;
use Modules\KYC\Services\KycRequestService;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\DataView\RestaurantApprovalGrid;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Http\Requests\UpdateBankingRequest;
use Modules\Restaurant\Http\Requests\UpdateRegistrationRequest;
use Modules\Restaurant\Models\OnboardingStatusHistory;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Models\TaxInformation;
use Modules\Restaurant\Services\OnboardingHistoryService;
use Modules\Restaurant\Services\OnboardingStepReviewService;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantBankingService;
use Modules\Restaurant\Services\RestaurantOnboardingReviewService;
use Modules\Restaurant\Services\RestaurantOwnerRoleResolver;
use Modules\Restaurant\Services\RestaurantTaxService;

class RestaurantApprovalController
{
    protected $notificationService;

    public function __construct(?NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $access = app(RestaurantApprovalAccess::class);
        $admin = auth('admin')->user();
        $ownerOnly = $access->isOwner($admin);

        if ($ownerOnly) {
            $restaurant = $access->scope(Restaurant::query(), $admin)->orderBy('id')->firstOrFail();

            return redirect()->route('admin.restaurant.approvals.show', ['id' => $restaurant->id]);
        }

        $lists = fn_datagrid(RestaurantApprovalGrid::class)->process();
        $applications = Restaurant::query()->whereIn('approval_status', ['pending', 'approved', 'rejected']);
        $access->scope($applications, $admin);
        $applicationIds = (clone $applications)->select('restaurants.id');
        $stepCounts = DB::table('restaurant_onboarding_steps')
            ->whereIn('restaurant_id', $applicationIds)
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $resubmittedSteps = DB::table('restaurant_onboarding_status_histories')
            ->whereIn('restaurant_id', (clone $applications)->select('restaurants.id'))
            ->where('action', 'resubmitted')
            ->whereNotNull('step_key')
            ->select('restaurant_id', 'step_key')
            ->groupBy('restaurant_id', 'step_key')
            ->get()
            ->count();
        $approvalCounts = [
            'approved' => (int) $stepCounts->get(OnboardingStatus::APPROVED->value, 0),
            'pending' => (int) $stepCounts->get(OnboardingStatus::SUBMITTED->value, 0)
                + (int) $stepCounts->get(OnboardingStatus::UNDER_REVIEW->value, 0)
                + (int) $stepCounts->get('pending', 0)
                + (int) $stepCounts->get('resubmitted', 0),
            'rejected' => (int) $stepCounts->get(OnboardingStatus::REJECTED->value, 0),
            'draft' => (int) $stepCounts->get(OnboardingStatus::DRAFT->value, 0),
            'resubmitted' => $resubmittedSteps,
        ];

        return view('restaurant::admin.approvals.index', compact('lists', 'approvalCounts', 'ownerOnly'));
    }

    public function requestChanges(Request $request, $id)
    {
        $admin = $request->user('admin');
        abort_if(app(RestaurantApprovalAccess::class)->isOwner($admin), 403);
        $request->merge(['reason' => is_string($request->input('reason')) ? trim($request->input('reason')) : $request->input('reason')]);
        $data = $request->validate([
            'step_key' => ['required', Rule::enum(OnboardingStep::class)],
            'reason' => ['required', 'string', 'max:500'],
            'submission_id' => ['nullable', 'integer', 'min:1', 'prohibited_unless:step_key,kyc'],
        ]);
        $step = OnboardingStep::from($data['step_key']);
        $permission = match ($step) {
            OnboardingStep::BANKING => 'admin.restaurant.approvals.banking.show',
            OnboardingStep::TAX => 'admin.restaurant.approvals.tax.show',
            OnboardingStep::KYC => 'admin.kyc.submissions.review',
            default => 'admin.restaurant.approvals.show',
        };
        abort_unless($admin->hasPermission($permission), 403);
        app(OnboardingStepReviewService::class)->requestChanges((int) $id, (int) $admin->id, $step, $data['reason'], isset($data['submission_id']) ? (int) $data['submission_id'] : null);

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => $step->value])
            ->with('success', 'Changes requested. The owner can update and resubmit this step.');
    }

    public function show(Request $request, $id)
    {
        $restaurant = Restaurant::with(['admin', 'onboardingSteps.reviewer'])->findOrFail($id);
        $steps = OnboardingStep::ordered();
        $stepRecords = $restaurant->onboardingSteps->keyBy('step_key');
        $requestedStep = $request->query('step', 'profile');
        abort_unless(is_string($requestedStep), 404);
        $isProfile = $requestedStep === 'profile';
        $isReview = $requestedStep === 'review';
        $activeStep = OnboardingStep::tryFrom($requestedStep);
        abort_unless($isProfile || $isReview || in_array($activeStep, [OnboardingStep::REGISTRATION, OnboardingStep::BANKING, OnboardingStep::TAX, OnboardingStep::DIETARY_CERTIFICATES, OnboardingStep::KYC], true), 404);
        if ($isProfile) {
            $restaurant->load(['addresses.city', 'addresses.state', 'addresses.country']);
        }
        $canViewBanking = bouncer('admin')->hasPermission('admin.restaurant.approvals.banking.show');
        $canViewTax = bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.show');
        $taxType = null;
        $taxRecords = collect();
        if ($activeStep === OnboardingStep::TAX) {
            abort_unless($canViewTax, 403);
            $validated = $request->validate(['type' => ['sometimes', 'required', Rule::in(['individual', 'business'])]]);
            $taxType = $restaurant->tax_type ?? $validated['type'] ?? 'individual';
            $taxRecords = $restaurant->taxInformation()->with(['city', 'state', 'country'])->get()->keyBy('tax_type');
        }
        $canViewDietary = bouncer('admin')->hasPermission('admin.dietarymanagment.certifications.show');
        if ($activeStep === OnboardingStep::DIETARY_CERTIFICATES) {
            abort_unless($canViewDietary, 403);
        }
        $dietaryCertificates = $activeStep === OnboardingStep::DIETARY_CERTIFICATES
            ? $restaurant->certifications()->with(['certificationType.dietaryLabel', 'reviewer'])->latest('id')->get() : collect();
        $canViewKyc = bouncer('admin')->hasPermission('admin.kyc.submissions.show');
        $canReviewKyc = bouncer('admin')->hasPermission('admin.kyc.submissions.review');
        $kycSubmissions = collect();
        $kycTypes = collect();
        if ($activeStep === OnboardingStep::BANKING) {
            abort_unless($canViewBanking, 403);
            $restaurant->load(['bankingDetail.city', 'bankingDetail.state', 'bankingDetail.country']);
        }
        if ($activeStep === OnboardingStep::KYC) {
            abort_unless($canViewKyc, 403);
        }
        if ($canViewKyc) {
            $kycTypes = KycType::active()->kyc()->orderBy('id')->get(['id', 'name']);
            if ($restaurant->admin_id) {
                $kycSubmissions = KycSubmission::where('user_id', $restaurant->admin_id)
                    ->whereHas('kycType', fn ($query) => $query->kyc())
                    ->with(['kycType', 'data.field', 'files.field', 'reviewer', 'messages'])
                    ->latest('id')->get()->unique('kyc_type_id')->values();
            }
        }
        $tabs = array_filter($steps, fn (OnboardingStep $step) => $step === OnboardingStep::REGISTRATION
            || ($step === OnboardingStep::BANKING && $canViewBanking)
            || ($step === OnboardingStep::TAX && $canViewTax)
            || ($step === OnboardingStep::DIETARY_CERTIFICATES && $canViewDietary)
            || ($step === OnboardingStep::KYC && $canViewKyc));
        $stepHistories = OnboardingStatusHistory::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('step_key', array_map(fn (OnboardingStep $step) => $step->value, $steps))
            ->when(! $canViewBanking, fn ($query) => $query->where('step_key', '!=', OnboardingStep::BANKING->value))
            ->when(! $canViewTax, fn ($query) => $query->where('step_key', '!=', OnboardingStep::TAX->value))
            ->when(! $canViewDietary, fn ($query) => $query->where('step_key', '!=', OnboardingStep::DIETARY_CERTIFICATES->value))
            ->when(! $canViewKyc, fn ($query) => $query->where('step_key', '!=', OnboardingStep::KYC->value))
            ->with('changer')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('step_key');

        $stepTabStates = [];
        foreach ($steps as $step) {
            $status = $stepRecords->get($step->value)?->status;
            $statusEnum = OnboardingStatus::tryFrom($status ?? '');
            $latestResubmission = $stepHistories->get($step->value, collect())
                ->first(fn (OnboardingStatusHistory $history) => $history->isResubmission());
            $refillRequested = $status === 'action_required' || ($status === 'rejected'
                && $stepHistories->get($step->value, collect())->first()?->action === 'changes_requested');
            $stepTabStates[$step->value] = [
                'label' => $refillRequested ? 'Refill requested' : ($statusEnum?->label() ?? ($status ? Str::headline($status) : 'Not started')),
                'color' => $refillRequested ? 'bg-orange-100 text-orange-800' : ($statusEnum?->color() ?? 'bg-gray-100 text-gray-600'),
                'refill_requested' => $refillRequested,
                'resubmitted_at' => $latestResubmission?->created_at,
            ];
        }

        $kycCounts = [];
        if ($activeStep === OnboardingStep::KYC) {
            $requiredTypeIds = $kycTypes->pluck('id');
            $uploadedTypeIds = $kycSubmissions->where('status', '!=', 'draft')->pluck('kyc_type_id');
            $kycCounts = [
                'pending' => $kycSubmissions->whereIn('status', ['pending', 'submitted', 'under_review'])->count(),
                'approved' => $kycSubmissions->where('status', 'approved')->count(),
                'rejected' => $kycSubmissions->where('status', 'rejected')->count(),
                'resubmitted' => $stepHistories->get(OnboardingStep::KYC->value, collect())->where('action', 'resubmitted')->count(),
                'draft' => $requiredTypeIds->diff($uploadedTypeIds)->count(),
            ];
        }

        $reviewSummary = $isReview ? $this->buildReviewSummary($restaurant) : null;

        return view('restaurant::admin.approvals.show', compact('restaurant', 'steps', 'stepRecords', 'tabs', 'activeStep', 'isProfile', 'isReview', 'reviewSummary', 'stepHistories', 'canViewBanking', 'canViewTax', 'taxType', 'taxRecords', 'canViewKyc', 'canReviewKyc', 'kycTypes', 'kycSubmissions', 'kycCounts', 'stepTabStates', 'canViewDietary', 'dietaryCertificates'));
    }

    private function buildReviewSummary(Restaurant $restaurant): array
    {
        $visibleSteps = [OnboardingStep::REGISTRATION];
        foreach ([
            'admin.restaurant.approvals.banking.show' => OnboardingStep::BANKING,
            'admin.restaurant.approvals.tax.show' => OnboardingStep::TAX,
            'admin.dietarymanagment.certifications.show' => OnboardingStep::DIETARY_CERTIFICATES,
            'admin.kyc.submissions.show' => OnboardingStep::KYC,
        ] as $permission => $step) {
            if (bouncer('admin')->hasPermission($permission)) {
                $visibleSteps[] = $step;
            }
        }

        return app(RestaurantOnboardingReviewService::class)->build($restaurant, $visibleSteps);
    }

    public function downloadReviewPdf($id)
    {
        abort_unless(auth('admin')->id()
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.show')
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.review.pdf'), 403);
        $restaurant = Restaurant::findOrFail($id);
        $reviewSummary = $this->buildReviewSummary($restaurant);

        return Pdf::loadView('restaurant::admin.approvals.review-pdf', compact('reviewSummary'))
            ->setPaper('a4')->setOption('isRemoteEnabled', false)->setOption('defaultFont', 'DejaVu Sans')
            ->download('restaurant-'.$restaurant->id.'-onboarding-review.pdf');
    }

    public function startReview(Request $request, $id)
    {
        abort_unless(auth('admin')->id()
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.start-review'), 403);
        $validated = $request->validate([
            'step_key' => ['required', Rule::in([OnboardingStep::REGISTRATION->value, OnboardingStep::BANKING->value, OnboardingStep::TAX->value])],
            'type' => ['sometimes', 'required', Rule::in(['individual', 'business'])],
        ]);
        $step = OnboardingStep::from($validated['step_key']);
        if ($step === OnboardingStep::BANKING) {
            abort_unless(bouncer('admin')->hasPermission('admin.restaurant.approvals.banking.show'), 403);
        }
        if ($step === OnboardingStep::TAX) {
            abort_unless(bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.show'), 403);
        }
        app(OnboardingStepReviewService::class)->startReview((int) $id, (int) auth('admin')->id(), $step);

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => $step->value] + ($step === OnboardingStep::TAX ? ['type' => $validated['type'] ?? 'individual'] : []))
            ->with('success', $step->label().' is now under review.');
    }

    public function approveTax(Request $request, $id)
    {
        return $this->reviewTax($request, $id, true);
    }

    public function updateTaxType(Request $request, $id)
    {
        abort_unless(auth('admin')->id()
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.show')
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.type'), 403);
        $validated = $request->validate(['tax_type' => ['required', Rule::in(['individual', 'business'])]]);
        app(RestaurantTaxService::class)->selectTypeForRestaurant((int) $id, $validated['tax_type']);

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'tax'])
            ->with('success', 'Restaurant tax type updated successfully.');
    }

    public function updateTax(Request $request, $id)
    {
        abort_unless(auth('admin')->id()
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.show')
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.update'), 403);
        $fields = array_diff((new TaxInformation)->getFillable(), ['tax_certification', 'tax_certified_at']);
        $input = $request->only($fields);
        foreach ($input as $field => $value) {
            if (is_string($value)) {
                $input[$field] = trim($value);
            }
        }
        foreach (['ssn', 'ein'] as $field) {
            if (blank($input[$field] ?? null)) {
                unset($input[$field]);
            }
        }
        $type = in_array($input['tax_type'] ?? null, ['individual', 'business'], true) ? $input['tax_type'] : 'individual';
        $redirect = redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'tax', 'type' => $type]);
        try {
            app(RestaurantTaxService::class)->updateForRestaurant((int) $id, $input);
        } catch (ValidationException $exception) {
            return $redirect->withErrors($exception->errors())->withInput($request->except('ssn', 'ein'));
        }

        return $redirect->with('success', 'Tax information updated successfully.');
    }

    public function rejectTax(Request $request, $id)
    {
        return $this->reviewTax($request, $id, false);
    }

    private function reviewTax(Request $request, $id, bool $approved)
    {
        abort_unless(auth('admin')->id()
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.show')
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.'.($approved ? 'approve' : 'reject')), 403);
        if (is_string($request->input('rejection_reason'))) {
            $request->merge(['rejection_reason' => trim($request->input('rejection_reason'))]);
        }
        $validated = $request->validate([
            'step_key' => ['required', Rule::in([OnboardingStep::TAX->value])],
            'type' => ['required', Rule::in(['individual', 'business'])],
            'rejection_reason' => [$approved ? 'nullable' : 'required', 'string', 'max:500'],
        ]);
        app(OnboardingStepReviewService::class)->reviewTax((int) $id, (int) auth('admin')->id(), $approved, $validated['rejection_reason'] ?? null);

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'tax', 'type' => $validated['type']])
            ->with('success', $approved ? 'Tax information approved successfully.' : 'Tax information rejected.');
    }

    public function updateBanking(UpdateBankingRequest $request, $id)
    {
        app(RestaurantBankingService::class)->updateForRestaurant((int) $id, $request->validated());

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'banking'])
            ->with('success', 'Banking information updated.');
    }

    public function approveBanking(Request $request, $id)
    {
        return $this->reviewBanking($request, $id, true);
    }

    public function rejectBanking(Request $request, $id)
    {
        return $this->reviewBanking($request, $id, false);
    }

    private function reviewBanking(Request $request, $id, bool $approved)
    {
        $permission = 'admin.restaurant.approvals.banking.'.($approved ? 'approve' : 'reject');
        abort_unless(auth('admin')->user()?->hasPermission($permission)
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.banking.show'), 403);
        if (is_string($request->input('rejection_reason'))) {
            $request->merge(['rejection_reason' => trim($request->input('rejection_reason'))]);
        }
        $validated = $request->validate([
            'step_key' => ['required', Rule::in([OnboardingStep::BANKING->value])],
            'rejection_reason' => [$approved ? 'nullable' : 'required', 'string', 'max:500'],
        ]);
        app(OnboardingStepReviewService::class)->reviewBanking(
            (int) $id, (int) auth('admin')->id(), $approved, $validated['rejection_reason'] ?? null
        );

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'banking'])
            ->with('success', $approved ? 'Banking details approved successfully.' : 'Banking details rejected.');
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['step_key' => ['required', Rule::in([OnboardingStep::REGISTRATION->value])]]);
        abort_unless(auth('admin')->id(), 403);

        try {
            DB::beginTransaction();

            $restaurant = app(OnboardingStepReviewService::class)->reviewRegistration((int) $id, (int) auth('admin')->id(), true);
            // Restaurant Owner role from general.default_role (falls back to role named "Restaurant Owner").
            $role = app(RestaurantOwnerRoleResolver::class)->resolve()->getKey();
            // Activate the admin account
            $restaurant->admin->update([
                'status' => 1, // 1 = active
                'role_id' => $role,
            ]);

            DB::commit();

            $createdRequests = [];
            // Create pending KYC requests for the restaurant admin (if any active KYC types exist)
            try {
                if ($restaurant->admin) {
                    $kycRequestService = app(KycRequestService::class);
                    $createdRequests = $kycRequestService->createPendingRequestsForAdmin($restaurant->admin, auth('admin')->id() ?? $restaurant->admin->id);
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to create pending KYC requests after approval', ['restaurant_id' => $restaurant->id, 'error' => $e->getMessage()]);
            }

            // Send one email containing approval, all onboarding steps, and pending KYC requests.
            if ($this->notificationService && $restaurant->admin) {
                try {
                    $this->notificationService->trigger(
                        'Restaurant',
                        'RestaurantApproved',
                        $restaurant->admin,
                        ['restaurant' => $restaurant, 'pending_kyc_requests' => $createdRequests]
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Failed to send approval notification', [
                        'restaurant_id' => $restaurant->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return redirect()->route('admin.restaurant.approvals.show', $id)
                ->with('success', 'Restaurant registration approved successfully!');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to approve restaurant.']);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'step_key' => ['required', Rule::in([OnboardingStep::REGISTRATION->value])],
            'rejection_reason' => 'required|string|max:500',
        ]);
        abort_unless(auth('admin')->id(), 403);

        try {
            $restaurant = app(OnboardingStepReviewService::class)->reviewRegistration(
                (int) $id, (int) auth('admin')->id(), false, $request->input('rejection_reason')
            );

            // Send notification to restaurant owner about rejection
            if ($this->notificationService && $restaurant->admin) {
                try {
                    $this->notificationService->trigger(
                        'Restaurant',
                        'RestaurantRejected',
                        $restaurant->admin,
                        $restaurant
                    );
                } catch (\Exception $e) {
                    \Log::warning('Failed to send rejection notification', [
                        'restaurant_id' => $restaurant->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return redirect()->route('admin.restaurant.approvals.show', $id)
                ->with('success', 'Restaurant registration rejected.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reject restaurant.']);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $admin = $request->user('admin');
        abort_unless($admin?->hasPermission('admin.restaurant.approvals.status.update'), 403);
        $validated = $request->validate(['status' => ['required', Rule::in(['active', 'inactive'])]]);

        DB::transaction(function () use ($id, $admin, $validated) {
            $restaurant = Restaurant::query()->lockForUpdate()->findOrFail($id);
            if (app(RestaurantOwnerRoleResolver::class)->isOwner($admin)) {
                abort_unless((int) $restaurant->admin_id === (int) $admin->id, 403);
            }
            $restaurant->update(['status' => $validated['status']]);
        });

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'profile'])
            ->with('success', 'Restaurant status updated to '.$validated['status'].'.');
    }

    public function updateEmailVerification(Request $request, $id)
    {
        abort_unless(auth('admin')->user()?->hasPermission('admin.restaurant.approvals.registration.email-verification'), 403);
        $validated = $request->validate(['verified' => ['required', 'boolean']]);

        DB::transaction(function () use ($id, $validated) {
            $restaurant = Restaurant::query()->lockForUpdate()->findOrFail($id);
            $owner = $restaurant->admin()->lockForUpdate()->firstOrFail();
            abort_unless(filled($owner->email), 422, 'The restaurant owner has no email address to verify.');

            $owner->update([
                'email_verified_at' => $validated['verified'] ? ($owner->email_verified_at ?? now()) : null,
            ]);
            if ($owner->wasChanged('email_verified_at')) {
                app(OnboardingHistoryService::class)->detailsUpdated(
                    $restaurant, 'registration', $validated['verified'] ? 'email_verified' : 'email_unverified'
                );
            }
        });

        return redirect()->route('admin.restaurant.approvals.show', $id)
            ->with('success', $validated['verified'] ? 'Owner email marked as verified.' : 'Owner email verification removed.');
    }

    public function updateRegistration(UpdateRegistrationRequest $request, $id)
    {
        $validated = $request->validated();
        unset($validated['step_key']);

        DB::transaction(function () use ($id, $validated) {
            $restaurant = Restaurant::query()->lockForUpdate()->findOrFail($id);
            abort_unless($restaurant->onboardingSteps()->where('step_key', OnboardingStep::REGISTRATION->value)->exists(), 422, 'No registration step exists for this restaurant.');
            $country = Country::findOrFail($validated['country_id']);
            $state = CountryState::findOrFail($validated['state_id']);
            $city = City::find($validated['city_id'] ?? null);
            $location = array_intersect_key($validated, array_flip(['country_id', 'state_id', 'city_id'])) + ['city_id' => null];
            unset($validated['country_id'], $validated['state_id'], $validated['city_id']);
            $validated['country'] = $country->name;
            $validated['state'] = $state->name;
            $validated['city'] = $city?->name;
            $restaurant->update($validated + $location);
            $addressData = $location + [
                'address_line_1' => $validated['address_line1'] ?? '',
                'address_line_2' => $validated['address_line2'] ?? null,
                'zip_code' => $validated['postal_code'] ?? null,
                'city_name' => $city?->name,
            ];
            $address = $restaurant->addresses()->orderByDesc('is_default')->orderBy('id')->first();
            if ($address) {
                $address->update($addressData);
            } else {
                $restaurant->addresses()->create($addressData + ['address_type' => 'primary', 'is_default' => true]);
            }
        });

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'registration'])->with('success', 'Restaurant information updated.');
    }
}
