@extends('backend::layouts.app')

@php
    $ownerOnly = app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user());
@endphp

@section('title', 'Restaurant Application Details')
@section('content-class', 'approval-page-content space-y-6')

@section('breadcrumbs')
    @php
        $title = 'Application Details';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Restaurant', 'url' => route('admin.restaurant.index')],
            $ownerOnly
                ? ['label' => 'Onboarding Details']
                : ['label' => 'Approvals', 'url' => route('admin.restaurant.approvals.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
@once('onboarding-status-markers')
<link rel="stylesheet" href="{{ asset('css/onboarding-timeline-markers.css') }}">
@endonce
@php
    $canRequestChanges = bouncer('admin')->hasPermission('admin.restaurant.approvals.request-changes')
        && !app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user());
    $registration = \Modules\Restaurant\Enums\OnboardingStep::REGISTRATION;
    $record = $stepRecords->get($registration->value);
    $canEdit = $record && bouncer('admin')->hasPermission('admin.restaurant.approvals.registration.update');
    $fields = [
        'name' => 'Restaurant Name', 'phone' => 'Phone',
        'address_line1' => 'Address Line 1', 'address_line2' => 'Address Line 2',
        'city' => 'City', 'state' => 'State', 'country' => 'Country', 'postal_code' => 'Postal Code',
    ];
@endphp
@php
    $restaurantStatusTone = match ($restaurant->status) {
        'active' => 'ar-pill--success',
        'inactive', 'closed' => 'ar-pill--danger',
        'draft' => 'ar-pill--warning',
        default => 'ar-pill--neutral',
    };
    $stepCount = count($steps);
    $approvedStepCount = collect($steps)->filter(fn ($step) => $stepRecords->get($step->value)?->status === \Modules\Restaurant\Enums\OnboardingStatus::APPROVED->value)->count();
    $approvedStepPercent = $stepCount ? (int) round($approvedStepCount / $stepCount * 100) : 0;
@endphp
<div class="approval-review">
    <header class="ar-hero ar-card">
        <div class="ar-hero-main">
            <div class="ar-hero-identity">
                <span class="ar-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($restaurant->name ?: 'R', 0, 1)) }}</span>
                <div class="min-w-0">
                    @unless($ownerOnly)
                        <a href="{{ route('admin.restaurant.approvals.index') }}" class="ar-back">
                            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            All applications
                        </a>
                    @endunless
                    <p class="ar-eyebrow">Restaurant application review</p>
                    <h2 class="ar-hero-title">{{ $restaurant->name }}</h2>
                    <ul class="ar-hero-meta" aria-label="Application summary">
                        <li>
                            <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            <span>Applied <strong>{{ $restaurant->created_at?->format('M d, Y · h:i A') ?? 'N/A' }}</strong></span>
                        </li>
                        <li>
                            <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <strong>{{ $restaurant->city ?: 'Location not set' }}</strong>
                        </li>
                        <li>
                            <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0 1 16 0v1"/></svg>
                            <span>Owner <strong>{{ $restaurant->admin?->name ?? 'N/A' }}</strong></span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="ar-hero-side">
                <div class="ar-hero-statuses">
                    <div>
                        <p>Restaurant</p>
                        <span class="ar-pill {{ $restaurantStatusTone }}">{{ \Illuminate\Support\Str::headline($restaurant->status ?? 'Not set') }}</span>
                    </div>
                    <div>
                        <p>Onboarding</p>
                        <span class="inline-flex items-center rounded-full font-semibold {{ $restaurant->onboarding_status?->color() ?? 'bg-gray-100 text-gray-600' }}">{{ $restaurant->onboarding_status?->label() ?? 'Not started' }}</span>
                    </div>
                </div>
                <div class="ar-progress">
                    <div class="ar-progress-row">
                        <span>Steps approved</span>
                        <strong>{{ $approvedStepCount }} / {{ $stepCount }}</strong>
                    </div>
                    <div class="ar-progress-track" role="progressbar" aria-label="Onboarding steps approved" aria-valuemin="0" aria-valuemax="{{ $stepCount }}" aria-valuenow="{{ $approvedStepCount }}">
                        <div class="ar-progress-bar" style="width: {{ $approvedStepPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="review-layout">
        <section class="review-main ar-card">
            <nav aria-label="Onboarding steps" class="review-tabs" style="--review-tab-count: {{ count($tabs) + 2 }}">
                <a href="{{ route('admin.restaurant.approvals.show', ['id' => $restaurant->id, 'step' => 'profile']) }}" @if($isProfile) aria-current="page" @endif class="review-tab">
                    <span aria-hidden="true" class="review-tab-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>
                    </span>
                    <span class="review-tab-copy">
                        <span class="review-tab-number">Overview</span>
                        <span class="review-tab-label">Profile Details</span>
                    </span>
                </a>
                @foreach($tabs as $tab)
                    @php
                        $tabState = $stepTabStates[$tab->value];
                        $correctionPermission = match ($tab) {
                            \Modules\Restaurant\Enums\OnboardingStep::BANKING => 'admin.restaurant.approvals.banking.show',
                            \Modules\Restaurant\Enums\OnboardingStep::TAX => 'admin.restaurant.approvals.tax.show',
                            \Modules\Restaurant\Enums\OnboardingStep::KYC => 'admin.kyc.submissions.review',
                            default => 'admin.restaurant.approvals.show',
                        };
                        $canRequestTabChanges = $tab !== \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES && $canRequestChanges && bouncer('admin')->hasPermission($correctionPermission);
                        $tabChangeRequestOpen = in_array($stepRecords->get($tab->value)?->status, ['action_required', 'rejected'], true);

                    @endphp
                    <div class="review-tab-slot">
                    <a href="{{ route('admin.restaurant.approvals.show', ['id' => $restaurant->id, 'step' => $tab->value]) }}" @if($tab === $activeStep) aria-current="page" @endif
                       class="review-tab {{ $tabState['resubmitted'] ? 'is-resubmitted' : '' }}">
                        <span aria-hidden="true" class="review-tab-icon">{!! $tab->icon() !!}</span>
                        @if($stepRecords->get($tab->value)?->status !== \Modules\Restaurant\Enums\OnboardingStatus::APPROVED->value)
                            <span class="review-tab-alert" role="img" aria-label="{{ $tab->label() }} is not approved: {{ $tabState['label'] }}" title="{{ $tab->label() }} is not approved: {{ $tabState['label'] }}">
                                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 2.1 18a2 2 0 0 0 1.7 3h16.4a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 4h.01"/></svg>
                            </span>
                        @endif
                        <span class="review-tab-copy">
                            <span class="review-tab-number">Step {{ $tab->order() }}</span>
                            <span class="review-tab-label">{{ $tab->label() }}</span>
                            <span class="review-tab-badges">
                                <span class="review-tab-status {{ $tabState['color'] }}">@if($tabState['refill_requested'])<span aria-hidden="true">! </span>@endif{{ $tabState['label'] }}</span>
                                @if($tabState['resubmitted'])
                                    <span class="review-resubmitted-badge"><span aria-hidden="true">↻</span> Resubmitted</span>
                                @endif
                            </span>
                        </span>
                    </a>
                    @if($canRequestTabChanges)
                        @include('backend::partials.correction-menu-button', [
                            'correctionTitle' => $tab->label(),
                            'correctionUrl' => route('admin.restaurant.approvals.request-changes', $restaurant->id),
                            'correctionStep' => $tab->value,
                            'correctionMode' => 'form',
                            'correctionAllowed' => true,
                            'correctionDisabled' => !$stepRecords->get($tab->value) || in_array($stepRecords->get($tab->value)->status, ['draft', 'not_started'], true) || $tabChangeRequestOpen,
                            'correctionDisabledNote' => $tabChangeRequestOpen ? 'A change request is already open. Waiting for the owner to resubmit.' : 'Available after this step is submitted.',
                            'correctionView' => null,
                        ])
                    @endif
                    </div>
                @endforeach
                <a href="{{ route('admin.restaurant.approvals.show', ['id' => $restaurant->id, 'step' => 'review']) }}" @if($isReview) aria-current="page" @endif class="review-tab">
                    <span aria-hidden="true" class="review-tab-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h4"/></svg>
                    </span>
                    <span class="review-tab-copy">
                        <span class="review-tab-number">Summary</span>
                        <span class="review-tab-label">Review</span>
                    </span>
                </a>
            </nav>
            @if(!$isProfile && !$isReview && $stepTabStates[$activeStep->value]['refill_requested'])
                @php $refillRecord = $stepRecords->get($activeStep->value); @endphp
                <section class="review-refill-alert" role="status" aria-labelledby="refill-step-alert-title">
                    <span class="review-refill-alert-icon" aria-hidden="true">!</span>
                    <div>
                        <p class="review-refill-alert-eyebrow">ONBOARDING STEP ALERT</p>
                        <h3 id="refill-step-alert-title">{{ $activeStep->label() }} — Refill requested</h3>
                        <p>The owner must refill the requested details or reupload the document and submit this step for a new review.</p>
                        @if($refillRecord?->rejection_reason)<p class="review-refill-alert-reason"><strong>Reason:</strong> {{ $refillRecord->rejection_reason }}</p>@endif
                        @if($refillRecord?->reviewed_at)<p class="review-refill-alert-meta">Requested {{ $refillRecord->reviewed_at->format('M d, Y · h:i A') }}@if($refillRecord->reviewer) by {{ $refillRecord->reviewer->name }}@endif</p>@endif
                    </div>
                </section>
            @endif
            @if($isProfile)
                @include('restaurant::admin.approvals.partials.profile')
            @elseif($isReview)
                @include('restaurant::admin.approvals.partials.review')
            @elseif($activeStep === $registration)
            <div id="registration-panel">
                <div class="ar-panel-head">
                    <div>
                        <p class="ar-eyebrow">Step {{ $registration->order() }}</p>
                        <h3>Restaurant Information</h3>
                        <p class="review-help">Review the business and owner details before making a decision.</p>
                    </div>
                    @if($canEdit)
                        <button type="button" id="edit-registration" class="ar-btn" aria-expanded="false" aria-controls="registration-edit">Edit information</button>
                    @endif
                </div>
                <div id="registration-details">
                    <dl class="ar-fields">
                        @foreach($fields as $field => $label)
                            <div class="ar-field"><dt>{{ $label }}</dt><dd @class(['is-empty' => blank($restaurant->{$field})])>{{ $restaurant->{$field} ?: 'Not provided' }}</dd></div>
                        @endforeach
                        <div class="ar-field ar-field--wide"><dt>Description</dt><dd @class(['is-empty' => blank($restaurant->description)])>{{ $restaurant->description ?: 'Not provided' }}</dd></div>
                    </dl>
                </div>
                @if($canEdit)
                    <form id="registration-edit" data-location-selects="registration"
                        data-states-url="{{ route('api.countries.states-by-country', ['countryId' => '__ID__']) }}"
                        data-cities-url="{{ route('api.countries.cities-by-state', ['stateId' => '__ID__']) }}"
                        class="hidden space-y-4" action="{{ route('admin.restaurant.approvals.registration.update', $restaurant->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="step_key" value="{{ $registration->value }}">
                        <h4 class="review-edit-title">Edit restaurant information</h4>
                        <p class="review-help">Fields marked * are required. Restaurant names need at least 5 characters.</p>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach(array_diff_key($fields, array_flip(['country', 'state', 'city'])) as $field => $label)
                                <div>
                                    <label for="edit-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }}@if(in_array($field, ['name', 'phone'])) <span aria-hidden="true">*</span>@endif</label>
                                    <input id="edit-{{ $field }}" name="{{ $field }}" type="{{ $field === 'phone' ? 'tel' : 'text' }}" value="{{ old($field, $restaurant->{$field}) }}" maxlength="{{ $field === 'name' ? 150 : ($field === 'phone' ? 20 : ($field === 'postal_code' ? 9 : 255)) }}" @if(in_array($field, ['name', 'postal_code'], true)) minlength="5" @endif @if(in_array($field, ['name', 'phone'])) required @endif aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                                </div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            @foreach(['country_id' => ['Country', $registrationCountries, $registrationCountryId], 'state_id' => ['State', $registrationStates, $registrationStateId], 'city_id' => ['City', $registrationCities, $registrationCityId]] as $field => [$label, $locations, $selected])
                                <div>
                                    <label for="registration-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }} <span aria-hidden="true">*</span></label>
                                    <select id="registration-{{ $field }}" name="{{ $field }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}">
                                        <option value="">Select {{ strtolower($label) }}</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" @selected(is_scalar($selected) && (string) $selected === (string) $location->id)>{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                    @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </div>
                        <p id="registration-location-error" class="hidden text-sm text-red-600" role="status"></p>
                        <div><label for="edit-description" class="mb-1 block text-sm font-medium">Description</label><textarea id="edit-description" name="description" rows="4" maxlength="10000" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" class="w-full rounded-lg border border-gray-300 p-3">{{ old('description', $restaurant->description) }}</textarea></div>
                        <div class="ar-form-actions"><button type="submit" class="ar-btn ar-btn--primary">Save changes</button><button id="cancel-edit" type="button" class="ar-btn">Cancel</button></div>
                    </form>
                @endif

                <section class="ar-section" aria-labelledby="registration-owner-heading">
                    <h4 id="registration-owner-heading" class="ar-section-title">Owner Information</h4>
                    <dl class="ar-fields">
                        <div class="ar-field"><dt>Contact person</dt><dd @class(['is-empty' => blank($restaurant->admin?->name)])>{{ $restaurant->admin?->name ?? 'Not provided' }}</dd></div>
                        <div class="ar-field"><dt>Email</dt><dd @class(['is-empty' => blank($restaurant->admin?->email)])>{{ $restaurant->admin?->email ?? 'Not provided' }}</dd></div>
                    </dl>
                </section>
                @if($record?->rejection_reason && $record->status !== 'action_required')
                    <div class="ar-callout ar-callout--danger" role="note">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                        <div><h4>Rejection reason</h4><p>{{ $record->rejection_reason }}</p></div>
                    </div>
                @endif
            </div>
            @elseif($activeStep === \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES)
                <section id="dietary-certificates-panel" aria-labelledby="dietary-certificates-heading">
                    <div class="ar-panel-head">
                        <div>
                            <p class="ar-eyebrow">Step {{ \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES->order() }} · Optional</p>
                            <h3 id="dietary-certificates-heading">Dietary Certificates</h3>
                            <p class="review-help">Optional until a document is uploaded. Once uploaded, every certificate must be approved before the restaurant can complete onboarding.</p>
                        </div>
                    </div>
                    @forelse($dietaryCertificates as $certificate)
                        @include('dietarymanagment::certifications.partials.review-card', ['returnToOnboarding' => true])
                    @empty
                        <div class="ar-empty">
                            <span class="ar-empty-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg></span>
                            <h4>No certificates uploaded</h4>
                            <p>Uploaded dietary certificates will appear here for review.</p>
                        </div>
                    @endforelse
                </section>
            @elseif($activeStep === \Modules\Restaurant\Enums\OnboardingStep::KYC)
                @include('restaurant::admin.approvals.partials.kyc')
            @elseif($activeStep === \Modules\Restaurant\Enums\OnboardingStep::TAX)
                @include('restaurant::admin.approvals.partials.tax')
            @else
                @include('restaurant::admin.approvals.partials.banking')
            @endif
            @if(!$isProfile && !$isReview && $activeStep !== \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES)
                @include('restaurant::admin.approvals.partials.step-activity')
            @endif
            @if(!$isProfile && !$isReview && !in_array($activeStep, [\Modules\Restaurant\Enums\OnboardingStep::KYC, \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES], true))
                @include('restaurant::admin.approvals.partials.review-actions')
            @endif
        </section>
        <aside class="review-sidebar">
            <section class="review-timeline ar-card" aria-labelledby="review-timeline-title">
                <p class="review-eyebrow">Application steps</p>
                <div class="review-timeline-head">
                    <h3 id="review-timeline-title" class="review-timeline-title">Onboarding Timeline</h3>
                    <span class="review-timeline-count">{{ $approvedStepCount }} of {{ $stepCount }} approved</span>
                </div>
                <ol class="review-timeline-list" tabindex="0" aria-label="Onboarding steps and current status">
                    @foreach($steps as $step)
                        @php
                            $timelineRecord = $stepRecords->get($step->value);
                            $timelineState = $stepTabStates[$step->value];
                            $canOpenStep = in_array($step, $tabs, true);
                            $timelineMarkerStatus = $timelineState['refill_requested'] ? 'refill_requested' : ($timelineRecord?->status ?? 'draft');
                            $canViewHistory = match ($step) {
                                \Modules\Restaurant\Enums\OnboardingStep::BANKING => $canViewBanking,
                                \Modules\Restaurant\Enums\OnboardingStep::TAX => $canViewTax,
                                \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES => $canViewDietary,
                                \Modules\Restaurant\Enums\OnboardingStep::KYC => $canViewKyc,
                                default => true,
                            };
                        @endphp
                        <li class="review-step {{ $step === $activeStep ? 'review-step-current' : '' }} {{ $timelineState['resubmitted'] ? 'review-step-resubmitted' : '' }} {{ $timelineState['refill_requested'] ? 'review-step-refill-requested' : '' }}" @if($step === $activeStep) aria-current="step" @endif>
                            <span aria-hidden="true" class="review-step-marker onboarding-status-marker" data-timeline-status="{{ $timelineMarkerStatus }}">
                                @include('restaurant::admin.partials.onboarding-status-icon', ['markerStatus' => $timelineMarkerStatus])
                            </span>
                            <div class="review-step-card">
                            @if($canOpenStep)
                                <a href="{{ route('admin.restaurant.approvals.show', ['id' => $restaurant->id, 'step' => $step->value]) }}" class="review-step-link" @if($step === $activeStep) aria-current="page" @endif>
                            @else
                                <div class="review-step-link">
                            @endif
                                    <p class="review-step-number">Step {{ $step->order() }}@if($step === $activeStep)<span class="review-step-viewing">Viewing</span>@endif</p>
                                    <h4 class="review-step-title">{{ $step->label() }}</h4>
                                    <div class="review-step-badges">
                                        <span class="review-step-badge {{ $timelineState['color'] }}">@if($timelineState['refill_requested'])<span aria-hidden="true">! </span>@endif{{ $timelineState['label'] }}</span>
                                        @if($timelineState['resubmitted'])<span class="review-step-badge review-resubmitted-badge">Resubmitted</span>@endif
                                    </div>
                            @if($canOpenStep)
                                </a>
                            @else
                                </div>
                            @endif
                            <button type="button" class="review-history-button" data-step-history="step-history-{{ $step->value }}"
                                    aria-label="View {{ $step->label() }} history" title="{{ $canViewHistory ? 'View step history' : 'You do not have permission to view this history' }}"
                                    aria-haspopup="dialog" aria-controls="step-history-{{ $step->value }}"
                                    @disabled(!$canViewHistory)>
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 11a9 9 0 1 1 2.5 7M3 4v7h7M12 7v5l3 2"/>
                                </svg>
                            </button>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>
</div>
@foreach($steps as $step)
    @if(($step !== \Modules\Restaurant\Enums\OnboardingStep::BANKING || $canViewBanking) && ($step !== \Modules\Restaurant\Enums\OnboardingStep::TAX || $canViewTax) && ($step !== \Modules\Restaurant\Enums\OnboardingStep::KYC || $canViewKyc) && ($step !== \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES || $canViewDietary))
        @include('restaurant::admin.approvals.partials.step-history', ['histories' => $stepHistories->get($step->value, collect())])
    @endif
@endforeach
@if(!$isProfile && !$isReview && !in_array($activeStep, [\Modules\Restaurant\Enums\OnboardingStep::KYC, \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES], true))
<dialog id="registration-rejection" aria-labelledby="rejection-title" aria-describedby="rejection-description">
    <form action="{{ route(match ($activeStep) { $registration => 'admin.restaurant.approvals.reject', \Modules\Restaurant\Enums\OnboardingStep::TAX => 'admin.restaurant.approvals.tax.reject', default => 'admin.restaurant.approvals.banking.reject' }, $restaurant->id) }}" method="POST" data-review-confirm="Reject {{ $activeStep->label() }}?">
        @csrf
        <input type="hidden" name="step_key" value="{{ $activeStep->value }}">
        @if($activeStep === \Modules\Restaurant\Enums\OnboardingStep::TAX)<input type="hidden" name="type" value="{{ $taxType }}">@endif
        <div class="ar-dialog-head">
            <span class="ar-dialog-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg></span>
            <div>
                <h3 id="rejection-title">Reject {{ $activeStep->label() }}</h3>
                <p id="rejection-description" class="mt-1 text-sm text-gray-500">Explain what needs to be corrected before these details can be approved. The owner will see this reason.</p>
            </div>
        </div>
        <div class="ar-dialog-body">
            <label for="rejection-reason">Rejection reason <span class="text-red-600" aria-hidden="true">*</span></label>
            <textarea id="rejection-reason" name="rejection_reason" rows="4" required maxlength="500" placeholder="Enter the rejection reason…">{{ old('rejection_reason') }}</textarea>
            <p id="rejection-count" class="review-character-count">0 / 500 characters</p>
        </div>
        <div class="ar-dialog-foot"><button type="button" id="cancel-rejection" class="ar-btn">Cancel</button><button type="submit" class="ar-btn ar-btn--danger-solid">Reject {{ $activeStep->label() }}</button></div>
    </form>
</dialog>
@endif
<link rel="stylesheet" href="{{ asset('css/approval-review.css') }}?v={{ filemtime(public_path('css/approval-review.css')) }}">
@include('restaurant::admin.approvals.partials.request-changes')
@endsection

@push('scripts')
<script src="{{ asset('js/kyc-step-review.js') }}" defer></script>
<script src="{{ asset('js/onboarding-history.js') }}" defer></script>
@if($activeStep === $registration)
<script src="{{ asset('js/onboarding-location-selects.js') }}" defer></script>
@endif
<script>
(() => {
    const tabNavigation = document.querySelector('.approval-review .review-tabs');
    const selectedTab = tabNavigation?.querySelector('[aria-current="page"]');
    if (selectedTab) {
        requestAnimationFrame(() => {
            const navigationBounds = tabNavigation.getBoundingClientRect();
            const tabBounds = selectedTab.getBoundingClientRect();
            if (tabBounds.left < navigationBounds.left || tabBounds.right > navigationBounds.right) {
                tabNavigation.scrollLeft += tabBounds.left - navigationBounds.left - (navigationBounds.width - tabBounds.width) / 2;
            }
        });
    }
    const timeline = document.querySelector('.approval-review .review-timeline-list');
    const currentStep = timeline?.querySelector('[aria-current="step"]');
    if (currentStep) {
        requestAnimationFrame(() => {
            if (timeline.scrollWidth <= timeline.clientWidth) return;
            const listBounds = timeline.getBoundingClientRect();
            const stepBounds = currentStep.getBoundingClientRect();
            timeline.scrollLeft += stepBounds.left - listBounds.left - (listBounds.width - stepBounds.width) / 2;
        });
    }
    document.querySelectorAll('[data-step-history]').forEach(button => {
        button.addEventListener('click', () => document.getElementById(button.dataset.stepHistory)?.showModal());
    });
    document.querySelectorAll('.step-history-dialog').forEach(historyDialog => {
        historyDialog.querySelector('[data-close-history]').addEventListener('click', () => historyDialog.close());
        historyDialog.addEventListener('click', event => {
            if (event.target !== historyDialog) return;
            const bounds = historyDialog.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) historyDialog.close();
        });
    });
    const notifyError = message => {
        const text = document.createElement('span');
        text.textContent = message;
        showToast(text.innerHTML, 'error', 'Please check your details');
    };
    const serverErrors = @json($errors->all());
    if (serverErrors.length) notifyError([...new Set(serverErrors)].join(' '));

    const validateForm = form => {
        let firstInvalid = null;
        form.querySelectorAll('input:not([type="hidden"]), textarea, select').forEach(input => {
            input.value = input.value.trim();
            input.dispatchEvent(new Event('input'));
            if (!input.checkValidity()) {
                input.setAttribute('aria-invalid', 'true');
                firstInvalid ??= input;
            }
        });
        if (!firstInvalid) return true;
        const label = firstInvalid.labels?.[0]?.textContent.trim().replace(/\s*\*$/, '');
        notifyError(`${label ? label + ': ' : ''}${firstInvalid.validationMessage}`);
        firstInvalid.focus();
        return false;
    };

    document.querySelectorAll('form[data-review-confirm]').forEach(form => {
        form.noValidate = true;
        let submitting = false;
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (submitting || !validateForm(form)) return;
            const isReject = /^reject/i.test(form.dataset.reviewConfirm);
            const confirmed = await appConfirm({
                title: form.dataset.reviewConfirm,
                message: isReject
                    ? 'The owner will see your reason and must correct this step before it can be approved.'
                    : 'This step will be marked as approved and the owner will be notified.',
                confirmText: isReject ? 'Reject' : 'Approve',
                tone: isReject ? 'danger' : 'primary',
            });
            if (!confirmed || submitting) return;

            submitting = true;
            form.querySelectorAll('button[type="submit"]').forEach(button => {
                button.disabled = true;
                button.textContent = 'Submitting…';
            });
            form.submit();
        });
    });

    const dialog = document.getElementById('registration-rejection');
    const rejectionReason = document.getElementById('rejection-reason');
    const updateReasonCount = () => {
        if (!rejectionReason) return;
        document.getElementById('rejection-count').textContent = `${rejectionReason.value.length} / 500 characters`;
    };
    rejectionReason?.addEventListener('input', updateReasonCount);
    updateReasonCount();
    document.getElementById('open-rejection')?.addEventListener('click', () => {
        dialog.showModal();
        rejectionReason.focus();
    });
    document.getElementById('cancel-rejection')?.addEventListener('click', () => dialog.close());
    const editButton = document.getElementById('edit-registration');
    const editForm = document.getElementById('registration-edit');
    if (editForm) {
        editForm.noValidate = true;
        editForm.addEventListener('submit', event => {
            if (!validateForm(editForm)) event.preventDefault();
        });
    }
    editForm?.querySelectorAll('input:not([type="hidden"]), textarea').forEach(input => {
        const validateField = () => {
            const value = input.value.trim();
            let message = '';
            if (input.required && !value) message = 'This field is required.';
            else if (input.name === 'name' && Array.from(value).length < 5) message = 'The restaurant name must contain at least 5 characters.';
            else if (input.name === 'phone' && !/^(?=(?:\D*\d){7,15}\D*$)\+?[0-9][0-9 ().-]*$/.test(value)) message = 'Enter a valid phone number with 7 to 15 digits.';
            else if (input.maxLength > 0 && Array.from(value).length > input.maxLength) message = `Use no more than ${input.maxLength} characters.`;
            input.setCustomValidity(message);
            return message;
        };
        const showError = () => {
            validateField();
            const message = input.validationMessage;
            input.setAttribute('aria-invalid', String(Boolean(message)));
        };
        validateField();
        input.addEventListener('input', showError);
        input.addEventListener('blur', () => { input.value = input.value.trim(); showError(); });
        input.addEventListener('invalid', showError);
    });
    const setEditing = editing => {
        document.getElementById('registration-edit')?.classList.toggle('hidden', !editing);
        document.getElementById('registration-details')?.classList.toggle('hidden', editing);
        editButton?.setAttribute('aria-expanded', String(editing));
        if (editButton) editButton.textContent = editing ? 'Close editor' : 'Edit information';
        if (editing) document.getElementById('edit-name')?.focus();
        else editButton?.focus();
    };
    editButton?.addEventListener('click', () => setEditing(editButton.getAttribute('aria-expanded') !== 'true'));
    document.getElementById('cancel-edit')?.addEventListener('click', () => setEditing(false));
    const bankingEditButton = document.getElementById('edit-banking');
    const bankingEditForm = document.getElementById('banking-edit');
    const setBankingEditing = editing => {
        bankingEditForm?.classList.toggle('hidden', !editing);
        document.getElementById('banking-details')?.classList.toggle('hidden', editing);
        bankingEditButton?.setAttribute('aria-expanded', String(editing));
        if (bankingEditButton) bankingEditButton.textContent = editing ? 'Close editor' : 'Edit information';
        if (editing) document.getElementById('banking-account_holder_name')?.focus();
        else bankingEditButton?.focus();
    };
    bankingEditButton?.addEventListener('click', () => setBankingEditing(bankingEditButton.getAttribute('aria-expanded') !== 'true'));
    document.getElementById('cancel-banking-edit')?.addEventListener('click', () => setBankingEditing(false));
    if (bankingEditForm) {
        bankingEditForm.noValidate = true;
        const accountNumber = document.getElementById('banking-account-number');
        const accountConfirmation = document.getElementById('banking-account-confirmation');
        const validateAccount = () => {
            accountNumber.required = Boolean(accountConfirmation.value);
            accountConfirmation.required = Boolean(accountNumber.value);
            accountConfirmation.setCustomValidity(accountConfirmation.value !== accountNumber.value ? 'The account numbers do not match.' : '');
        };
        accountNumber.addEventListener('input', validateAccount);
        accountConfirmation.addEventListener('input', validateAccount);
        bankingEditForm.addEventListener('submit', event => {
            validateAccount();
            if (!validateForm(bankingEditForm)) event.preventDefault();
        });
    }
    @if($errors->any() && old('_banking_edit')) setBankingEditing(true); @endif
    const taxEditButton = document.getElementById('edit-tax');
    const taxTypeSelection = document.getElementById('tax-type-selection');
    const originalTaxType = taxTypeSelection?.querySelector('input[type="radio"]:checked')?.value;
    let changingTaxType = false;
    taxTypeSelection?.addEventListener('submit', async event => {
        event.preventDefault();
        const selected = taxTypeSelection.querySelector('input[type="radio"]:checked');
        if (changingTaxType || !selected || selected.value === originalTaxType) return;
        const label = selected.value === 'business' ? 'Business' : 'Individual';
        const confirmed = await appConfirm({
            title: `Change tax type to ${label}?`,
            message: 'Its saved information will be submitted for review again.',
            confirmText: 'Change tax type',
            tone: 'warning',
        });
        if (!confirmed) {
            taxTypeSelection.querySelectorAll('input[type="radio"]').forEach(radio => { radio.checked = radio.value === originalTaxType; });
            return;
        }
        changingTaxType = true;
        taxTypeSelection.setAttribute('aria-busy', 'true');
        taxTypeSelection.submit();
    });
    taxTypeSelection?.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => taxTypeSelection.requestSubmit());
    });
    const taxEditForm = document.getElementById('tax-edit');
    const setTaxEditing = editing => {
        taxEditForm?.classList.toggle('hidden', !editing);
        document.getElementById('tax-details')?.classList.toggle('hidden', editing);
        taxEditButton?.setAttribute('aria-expanded', String(editing));
        if (taxEditButton) taxEditButton.textContent = editing ? 'Close editor' : 'Edit information';
        if (editing) taxEditForm?.querySelector('input:not([type="hidden"])')?.focus();
        else taxEditButton?.focus();
    };
    taxEditButton?.addEventListener('click', () => setTaxEditing(taxEditButton.getAttribute('aria-expanded') !== 'true'));
    document.getElementById('cancel-tax-edit')?.addEventListener('click', () => setTaxEditing(false));
    if (taxEditForm) {
        const mailingSelect = document.getElementById('tax-same_mailing_address');
        const mailingFields = document.getElementById('tax-mailing-fields');
        const updateMailing = () => {
            const separate = mailingSelect.value === '0';
            mailingFields.classList.toggle('hidden', !separate);
            mailingFields.disabled = !separate;
            mailingFields.querySelectorAll('input').forEach(input => { input.required = separate; });
        };
        mailingSelect.addEventListener('change', updateMailing);
        updateMailing();
        const country = document.getElementById('tax-country_id');
        const state = document.getElementById('tax-state_id');
        const city = document.getElementById('tax-city_id');
        const locationError = document.getElementById('tax-location-error');
        const stateUrl = @json(route('api.countries.states-by-country', ['countryId' => '__ID__']));
        const cityUrl = @json(route('api.countries.cities-by-state', ['stateId' => '__ID__']));
        let stateRequest, cityRequest;
        const resetSelect = (select, label) => {
            select.replaceChildren(new Option(`Select ${label}`, ''));
            select.disabled = false;
        };
        const loadLocations = async (select, url, key, signal) => {
            select.disabled = true;
            locationError.classList.add('hidden');
            try {
                const response = await fetch(url, { signal, headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Request failed');
                const result = await response.json();
                if (!result.success || !Array.isArray(result[key])) throw new Error('Invalid response');
                result[key].forEach(option => select.add(new Option(option.name, option.id)));
            } catch (error) {
                if (error.name !== 'AbortError') {
                    locationError.textContent = 'Could not load locations. Select the country or state again to retry.';
                    locationError.classList.remove('hidden');
                }
            } finally {
                if (!signal.aborted) select.disabled = false;
            }
        };
        country.addEventListener('change', () => {
            stateRequest?.abort(); cityRequest?.abort();
            resetSelect(state, 'state'); resetSelect(city, 'city');
            if (country.value) {
                stateRequest = new AbortController();
                loadLocations(state, stateUrl.replace('__ID__', encodeURIComponent(country.value)), 'states', stateRequest.signal);
            }
        });
        state.addEventListener('change', () => {
            cityRequest?.abort(); resetSelect(city, 'city');
            if (state.value) {
                cityRequest = new AbortController();
                loadLocations(city, cityUrl.replace('__ID__', encodeURIComponent(state.value)), 'cities', cityRequest.signal);
            }
        });
        taxEditForm.noValidate = true;
        taxEditForm.addEventListener('submit', event => {
            if (state.disabled || city.disabled) {
                event.preventDefault();
                notifyError('Wait for locations to finish loading.');
            } else if (!validateForm(taxEditForm)) {
                event.preventDefault();
            }
        });
    }
    @if($errors->any() && old('_tax_edit')) setTaxEditing(true); @endif
    @if($errors->has('rejection_reason')) dialog?.showModal(); @endif
    @if($canEdit && collect(array_keys($fields))->merge(['description', 'country_id', 'state_id', 'city_id'])->contains(fn ($field) => $errors->has($field))) setEditing(true); @endif
})();
</script>
@endpush
