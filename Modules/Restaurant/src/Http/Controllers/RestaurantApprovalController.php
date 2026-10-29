<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Models\OnboardingStatusHistory;
use Modules\Restaurant\DataView\RestaurantApprovalGrid;
use Modules\Notifications\Services\NotificationService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Services\OnboardingStepReviewService;
use Modules\Restaurant\Http\Requests\UpdateRegistrationRequest;
use Modules\Restaurant\Http\Requests\UpdateBankingRequest;
use Modules\Restaurant\Services\RestaurantBankingService;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycType;

class RestaurantApprovalController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $lists = fn_datagrid(RestaurantApprovalGrid::class)->process();
        $applications = Restaurant::query()->whereIn('approval_status', ['pending', 'approved', 'rejected']);
        $access = app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class);
        $access->scope($applications, auth('admin')->user());
        $ownerOnly = $access->isOwner(auth('admin')->user());
        $statusCounts = (clone $applications)->selectRaw('approval_status, COUNT(*) AS total')
            ->groupBy('approval_status')->pluck('total', 'approval_status');
        $draftCount = (clone $applications)->where('approval_status', 'pending')
            ->whereHas('onboardingSteps', fn ($query) => $query
                ->where('step_key', OnboardingStep::REGISTRATION->value)
                ->where('status', OnboardingStatus::DRAFT->value))
            ->count();
        $approvalCounts = [
            'approved' => (int) $statusCounts->get('approved', 0),
            'pending' => max(0, (int) $statusCounts->get('pending', 0) - $draftCount),
            'rejected' => (int) $statusCounts->get('rejected', 0),
            'draft' => $draftCount,
            'resubmitted' => (clone $applications)->whereHas('onboardingStatusHistories', fn ($query) => $query
                ->where('action', 'resubmitted'))->count(),
        ];

        return view('restaurant::admin.approvals.index', compact('lists', 'approvalCounts', 'ownerOnly'));
    }

    public function requestChanges(Request $request, $id)
    {
        $admin = $request->user('admin');
        abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($admin), 403);
        $request->merge(['reason' => is_string($request->input('reason')) ? trim($request->input('reason')) : $request->input('reason')]);
        $data = $request->validate([
            'step_key' => ['required', Rule::enum(OnboardingStep::class)],
            'reason' => ['required', 'string', 'max:500'],
        ]);
        $step = OnboardingStep::from($data['step_key']);
        $permission = match ($step) {
            OnboardingStep::BANKING => 'admin.restaurant.approvals.banking.show',
            OnboardingStep::TAX => 'admin.restaurant.approvals.tax.show',
            OnboardingStep::KYC => 'admin.kyc.submissions.review',
            default => 'admin.restaurant.approvals.show',
        };
        abort_unless($admin->hasPermission($permission), 403);
        app(OnboardingStepReviewService::class)->requestChanges((int) $id, (int) $admin->id, $step, $data['reason']);

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => $step->value])
            ->with('success', 'Changes requested. The owner can update and resubmit this step.');
    }

    public function reviewMenu(Request $request, $id)
    {
        abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($request->user('admin')), 403);
        $data = $request->validate(['action' => ['required', 'in:approve,reject'],
            'reason' => ['nullable', 'required_if:action,reject', 'string', 'max:500']]);
        app(OnboardingStepReviewService::class)->reviewMenu((int) $id, (int) $request->user('admin')->id(), $data['action'] === 'approve', $data['reason'] ?? null);
        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'menu'])->with('success', 'Menu review saved.');
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
        abort_unless($isProfile || $isReview || in_array($activeStep, [OnboardingStep::REGISTRATION, OnboardingStep::MENU, OnboardingStep::BANKING, OnboardingStep::TAX, OnboardingStep::KYC], true), 404);
        if ($isProfile) {
            $restaurant->load(['addresses.city', 'addresses.state', 'addresses.country']);
        }
        $registrationCountries = $registrationStates = $registrationCities = collect();
        $registrationCountryId = $registrationStateId = $registrationCityId = null;
        if ($activeStep === OnboardingStep::REGISTRATION && bouncer('admin')->hasPermission('admin.restaurant.approvals.registration.update')) {
            $address = $restaurant->addresses()->orderByDesc('is_default')->orderBy('id')->first();
            $registrationCountries = \Modules\Countries\Models\Country::orderBy('name')->get(['id', 'name']);
            $registrationCountryId = old('country_id', $address?->country_id ?: $registrationCountries->firstWhere('name', $restaurant->country)?->id);
            $registrationStates = \Modules\Countries\Models\CountryState::where('country_id', is_scalar($registrationCountryId) ? $registrationCountryId : null)->orderBy('name')->get(['id', 'name', 'code']);
            $registrationStateId = old('state_id', $address?->state_id ?: ($registrationStates->firstWhere('name', $restaurant->state)?->id ?? $registrationStates->firstWhere('code', $restaurant->state)?->id));
            $registrationCities = \Modules\Countries\Models\City::where('country_id', is_scalar($registrationCountryId) ? $registrationCountryId : null)->where('state_id', is_scalar($registrationStateId) ? $registrationStateId : null)->orderBy('name')->get(['id', 'name']);
            $registrationCityId = old('city_id', $address?->city_id ?: $registrationCities->firstWhere('name', $restaurant->city)?->id);
        }
        $canViewBanking = bouncer('admin')->hasPermission('admin.restaurant.approvals.banking.show');
        $canViewTax = bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.show');
        $taxType = null;
        $taxRecords = collect();
        $taxCountries = $taxStates = $taxCities = collect();
        if ($activeStep === OnboardingStep::TAX) {
            abort_unless($canViewTax, 403);
            $validated = $request->validate(['type' => ['sometimes', 'required', Rule::in(['individual', 'business'])]]);
            $taxType = $restaurant->tax_type ?? $validated['type'] ?? 'individual';
            $taxRecords = $restaurant->taxInformation()->with(['city', 'state', 'country'])->get()->keyBy('tax_type');
            if (bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.update')) {
                $taxRecord = $taxRecords->get($taxType);
                $countryId = old('country_id', $taxRecord?->country_id);
                $stateId = old('state_id', $taxRecord?->state_id);
                $taxCountries = \Modules\Countries\Models\Country::orderBy('name')->get(['id', 'name']);
                $taxStates = \Modules\Countries\Models\CountryState::where('country_id', is_scalar($countryId) ? $countryId : null)->orderBy('name')->get(['id', 'name']);
                $taxCities = \Modules\Countries\Models\City::where('state_id', is_scalar($stateId) ? $stateId : null)->orderBy('name')->get(['id', 'name']);
            }
        }
        $canViewKyc = bouncer('admin')->hasPermission('admin.kyc.submissions.show');
        $canReviewKyc = bouncer('admin')->hasPermission('admin.kyc.submissions.review');
        $kycSubmissions = collect();
        $kycTypes = collect();
        $bankingCountries = $bankingStates = $bankingCities = collect();
        if ($activeStep === OnboardingStep::BANKING) {
            abort_unless($canViewBanking, 403);
            $restaurant->load(['bankingDetail.city', 'bankingDetail.state', 'bankingDetail.country']);
            if (bouncer('admin')->hasPermission('admin.restaurant.approvals.banking.update')) {
                $countryId = old('country_id', $restaurant->bankingDetail?->country_id);
                $stateId = old('state_id', $restaurant->bankingDetail?->state_id);
                $bankingCountries = \Modules\Countries\Models\Country::orderBy('name')->get(['id', 'name']);
                $bankingStates = \Modules\Countries\Models\CountryState::where('country_id', is_scalar($countryId) ? $countryId : null)->orderBy('name')->get(['id', 'name']);
                $bankingCities = \Modules\Countries\Models\City::where('state_id', is_scalar($stateId) ? $stateId : null)->where('country_id', is_scalar($countryId) ? $countryId : null)->orderBy('name')->get(['id', 'name']);
            }
        }
        if ($activeStep === OnboardingStep::KYC) {
            abort_unless($canViewKyc, 403);
            $kycTypes = KycType::active()->kyc()->orderBy('name')->get(['id', 'name']);
            if ($restaurant->admin_id) {
                $kycSubmissions = KycSubmission::where('user_id', $restaurant->admin_id)
                    ->whereHas('kycType', fn ($query) => $query->kyc())
                    ->with(['kycType', 'data.field', 'files.field', 'reviewer', 'messages'])
                    ->latest('id')->get()->unique('kyc_type_id')->values();
            }
        }
        $tabs = array_filter($steps, fn (OnboardingStep $step) => $step === OnboardingStep::REGISTRATION
            || $step === OnboardingStep::MENU
            || ($step === OnboardingStep::BANKING && $canViewBanking)
            || ($step === OnboardingStep::TAX && $canViewTax)
            || ($step === OnboardingStep::KYC && $canViewKyc));
        $stepHistories = OnboardingStatusHistory::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('step_key', array_map(fn (OnboardingStep $step) => $step->value, $steps))
            ->when(!$canViewBanking, fn ($query) => $query->where('step_key', '!=', OnboardingStep::BANKING->value))
            ->when(!$canViewTax, fn ($query) => $query->where('step_key', '!=', OnboardingStep::TAX->value))
            ->when(!$canViewKyc, fn ($query) => $query->where('step_key', '!=', OnboardingStep::KYC->value))
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
                'label' => $refillRequested ? 'Refill requested' : ($statusEnum?->label() ?? ($status ? \Illuminate\Support\Str::headline($status) : 'Not started')),
                'color' => $refillRequested ? 'bg-orange-100 text-orange-800' : ($statusEnum?->color() ?? 'bg-gray-100 text-gray-600'),
                'refill_requested' => $refillRequested,
                'resubmitted' => !$refillRequested && ($status === 'resubmitted' || $latestResubmission !== null),
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

        return view('restaurant::admin.approvals.show', compact('registrationCountries', 'registrationStates', 'registrationCities', 'registrationCountryId', 'registrationStateId', 'registrationCityId', 'restaurant', 'steps', 'stepRecords', 'tabs', 'activeStep', 'isProfile', 'isReview', 'reviewSummary', 'stepHistories', 'canViewBanking', 'canViewTax', 'taxType', 'taxRecords', 'taxCountries', 'taxStates', 'taxCities', 'bankingCountries', 'bankingStates', 'bankingCities', 'canViewKyc', 'canReviewKyc', 'kycTypes', 'kycSubmissions', 'kycCounts', 'stepTabStates'));
    }

    private function buildReviewSummary(Restaurant $restaurant): array
    {
        $visibleSteps = [OnboardingStep::REGISTRATION, OnboardingStep::MENU];
        foreach ([
            'admin.restaurant.approvals.banking.show' => OnboardingStep::BANKING,
            'admin.restaurant.approvals.tax.show' => OnboardingStep::TAX,
            'admin.kyc.submissions.show' => OnboardingStep::KYC,
        ] as $permission => $step) {
            if (bouncer('admin')->hasPermission($permission)) $visibleSteps[] = $step;
        }

        return app(\Modules\Restaurant\Services\RestaurantOnboardingReviewService::class)->build($restaurant, $visibleSteps);
    }

    public function downloadReviewPdf($id)
    {
        abort_unless(auth('admin')->id()
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.show')
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.review.pdf'), 403);
        $restaurant = Restaurant::findOrFail($id);
        $reviewSummary = $this->buildReviewSummary($restaurant);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('restaurant::admin.approvals.review-pdf', compact('reviewSummary'))
            ->setPaper('a4')->setOption('isRemoteEnabled', false)->setOption('defaultFont', 'DejaVu Sans')
            ->download('restaurant-' . $restaurant->id . '-onboarding-review.pdf');
    }

    public function startReview(Request $request, $id)
    {
        abort_unless(auth('admin')->id()
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.start-review'), 403);
        $validated = $request->validate([
            'step_key' => ['required', Rule::in([OnboardingStep::REGISTRATION->value, OnboardingStep::MENU->value, OnboardingStep::BANKING->value, OnboardingStep::TAX->value])],
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
            ->with('success', $step->label() . ' is now under review.');
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
        app(\Modules\Restaurant\Services\RestaurantTaxService::class)->selectTypeForRestaurant((int) $id, $validated['tax_type']);

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'tax'])
            ->with('success', 'Restaurant tax type updated successfully.');
    }

    public function updateTax(Request $request, $id)
    {
        abort_unless(auth('admin')->id()
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.show')
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.update'), 403);
        $fields = array_diff((new \Modules\Restaurant\Models\TaxInformation())->getFillable(), ['tax_certification', 'tax_certified_at']);
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
            app(\Modules\Restaurant\Services\RestaurantTaxService::class)->updateForRestaurant((int) $id, $input);
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
            && bouncer('admin')->hasPermission('admin.restaurant.approvals.tax.' . ($approved ? 'approve' : 'reject')), 403);
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
        $permission = 'admin.restaurant.approvals.banking.' . ($approved ? 'approve' : 'reject');
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
            $role = fn_get_setting('general.default_role');
            // Activate the admin account
            $restaurant->admin->update([
                'status' => 1, // 1 = active
                'role_id' => $role
            ]);

            DB::commit();

            $createdRequests = [];
            // Create pending KYC requests for the restaurant admin (if any active KYC types exist)
            try {
                if ($restaurant->admin) {
                    $kycRequestService = app(\Modules\KYC\Services\KycRequestService::class);
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
                        'error' => $e->getMessage()
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
                        'error' => $e->getMessage()
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
        $validated = $request->validate(['status' => ['required', Rule::in(['active', 'inactive', 'draft'])]]);

        DB::transaction(function () use ($id, $admin, $validated) {
            $restaurant = Restaurant::query()->lockForUpdate()->findOrFail($id);
            if ((int) $admin->role_id === 2) {
                abort_unless((int) $restaurant->admin_id === (int) $admin->id, 403);
            }
            $restaurant->update(['status' => $validated['status']]);
        });

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'profile'])
            ->with('success', 'Restaurant status updated to '.$validated['status'].'.');
    }

    public function updateOnboardingStatus(Request $request, $id)
    {
        $admin = $request->user('admin');
        abort_unless($admin?->hasPermission('admin.restaurant.approvals.onboarding-status.update'), 403);
        $validated = $request->validate([
            'onboarding_status' => ['required', Rule::in([
                OnboardingStatus::DRAFT->value,
                OnboardingStatus::UNDER_REVIEW->value,
                OnboardingStatus::ACTION_REQUIRED->value,
                OnboardingStatus::APPROVED->value,
            ])],
        ]);

        DB::transaction(function () use ($id, $admin, $validated) {
            $restaurant = Restaurant::query()->lockForUpdate()->findOrFail($id);
            if ((int) $admin->role_id === 2) {
                abort_unless((int) $restaurant->admin_id === (int) $admin->id, 403);
            }
            $restaurant->update(['onboarding_status' => $validated['onboarding_status']]);
        });

        return redirect()->route('admin.restaurant.approvals.show', ['id' => $id, 'step' => 'profile'])
            ->with('success', 'Onboarding status updated to '.OnboardingStatus::from($validated['onboarding_status'])->label().'.');
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
                app(\Modules\Restaurant\Services\OnboardingHistoryService::class)->detailsUpdated(
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
            $country = \Modules\Countries\Models\Country::findOrFail($validated['country_id']);
            $state = \Modules\Countries\Models\CountryState::findOrFail($validated['state_id']);
            $city = \Modules\Countries\Models\City::findOrFail($validated['city_id']);
            $location = array_intersect_key($validated, array_flip(['country_id', 'state_id', 'city_id']));
            unset($validated['country_id'], $validated['state_id'], $validated['city_id']);
            $validated['country'] = $country->name;
            $validated['state'] = $state->name;
            $validated['city'] = $city->name;
            $restaurant->update($validated);
            $addressData = $location + [
                'address_line_1' => $validated['address_line1'] ?? '',
                'address_line_2' => $validated['address_line2'] ?? null,
                'zip_code' => $validated['postal_code'] ?? null,
                'city_name' => $city->name,
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
