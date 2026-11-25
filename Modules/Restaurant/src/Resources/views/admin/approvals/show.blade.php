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
                    <a href="{{ route('admin.restaurant.approvals.show', ['id' => $restaurant->id, 'step' => $tab->value]) }}" @if($tab === $activeStep) aria-current="page" @endif class="review-tab">
                        <span aria-hidden="true" class="review-tab-icon">{!! $tab->icon() !!}</span>
                        @if($stepRecords->get($tab->value)?->status !== \Modules\Restaurant\Enums\OnboardingStatus::APPROVED->value)
                            <span class="review-tab-alert" role="img" aria-label="{{ $tab->label() }} is not approved: {{ $tabState['label'] }}" title="{{ $tab->label() }} is not approved: {{ $tabState['label'] }}">
                                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 2.1 18a2 2 0 0 0 1.7 3h16.4a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 4h.01"/></svg>
                            </span>
                        @endif
                        <span class="review-tab-copy">
                            <span class="review-tab-number">Step {{ $tab->order() }}</span>
                            <span class="review-tab-label">{{ $tab->label() }}</span>
                            <span class="review-tab-badges"><span class="review-tab-status {{ $tabState['color'] }}">@if($tabState['refill_requested'])<span aria-hidden="true">! </span>@endif{{ $tabState['label'] }}</span></span>
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
                </div>
                <div id="registration-details">
                    <dl class="ar-fields">
                        @foreach($fields as $field => $label)
                            <div class="ar-field"><dt>{{ $label }}</dt><dd @class(['is-empty' => blank($restaurant->{$field})])>{{ $restaurant->{$field} ?: 'Not provided' }}</dd></div>
                        @endforeach
                        <div class="ar-field ar-field--wide"><dt>Description</dt><dd @class(['is-empty' => blank($restaurant->description)])>{{ $restaurant->description ?: 'Not provided' }}</dd></div>
                    </dl>
                </div>
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
                        <li class="review-step {{ $step === $activeStep ? 'review-step-current' : '' }} {{ $timelineState['refill_requested'] ? 'review-step-refill-requested' : '' }}" @if($step === $activeStep) aria-current="step" @endif>
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
                                    <div class="review-step-badges"><span class="review-step-badge {{ $timelineState['color'] }}">@if($timelineState['refill_requested'])<span aria-hidden="true">! </span>@endif{{ $timelineState['label'] }}</span></div>
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
    document.querySelectorAll('[data-sensitive-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const visible = button.getAttribute('aria-pressed') !== 'true';
            const display = button.parentElement.querySelector('[data-sensitive-display]');
            const slash = button.querySelector('[data-sensitive-slash]');
            display.textContent = visible ? button.dataset.sensitiveValue : `•••• ${button.dataset.sensitiveValue.slice(-4)}`;
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', `${visible ? 'Hide' : 'Show'} ${button.dataset.sensitiveLabel}`);
            button.title = `${visible ? 'Hide' : 'Show'} ${button.dataset.sensitiveLabel}`;
            slash.style.display = visible ? 'none' : '';
        });
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
    @if($errors->has('rejection_reason')) dialog?.showModal(); @endif
})();
</script>
@endpush
