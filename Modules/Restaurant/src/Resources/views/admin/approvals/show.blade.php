@extends('backend::layouts.app')

@section('title', 'Restaurant Application Details')
@section('content-class', 'approval-page-content space-y-6')

@section('breadcrumbs')
    @php
        $title = 'Application Details';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Restaurant', 'url' => route('admin.restaurant.index')],
            ['label' => 'Approvals', 'url' => route('admin.restaurant.approvals.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
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
<div class="approval-review mx-auto space-y-6">
    <header class="review-header rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.restaurant.approvals.index') }}" class="text-sm text-blue-600 hover:underline">&larr; All applications</a>
                <h2 class="mt-3 text-2xl font-bold text-gray-900">{{ $restaurant->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">Restaurant Application Review</p>
            </div>
            <div class="review-header-status flex flex-wrap gap-6">
                <div>
                    <p class="mb-2 text-sm text-gray-500">Restaurant status</p>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ match ($restaurant->status) {
                        'active' => 'bg-green-100 text-green-800',
                        'inactive', 'closed' => 'bg-red-100 text-red-800',
                        'draft' => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-gray-100 text-gray-600',
                    } }}">
                        {{ \Illuminate\Support\Str::headline($restaurant->status ?? 'Not set') }}
                    </span>
                </div>
                <div>
                    <p class="mb-2 text-sm text-gray-500">Onboarding status</p>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ $restaurant->onboarding_status?->color() ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $restaurant->onboarding_status?->label() ?? 'Not started' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="mt-5 grid grid-cols-1 gap-4 border-t border-gray-100 pt-4 text-sm md:grid-cols-3">
            <div><p class="text-gray-500">Applied</p><p class="mt-1 font-semibold">{{ $restaurant->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</p></div>
            <div><p class="text-gray-500">Location</p><p class="mt-1 font-semibold">{{ $restaurant->city ?: 'N/A' }}</p></div>
            <div><p class="text-gray-500">Owner</p><p class="mt-1 font-semibold">{{ $restaurant->admin?->name ?? 'N/A' }}</p></div>
        </div>
    </header>

    <div class="review-layout grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="review-main overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
            <nav aria-label="Onboarding steps" class="review-tabs overflow-x-auto border-b border-gray-200 bg-gray-50" style="--review-tab-count: {{ count($tabs) + 2 }}">
                <a href="{{ route('admin.restaurant.approvals.show', ['id' => $restaurant->id, 'step' => 'profile']) }}" @if($isProfile) aria-current="page" @endif
                   class="review-tab flex items-center gap-2 border-b-2 px-6 py-4 text-sm font-semibold text-gray-700 {{ $isProfile ? 'bg-blue-50 border-blue-300' : 'bg-white border-transparent' }}">
                    <span aria-hidden="true" class="flex h-8 w-8 shrink-0 items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>
                    </span>
                    <span class="review-tab-label">Profile Details</span>
                </a>
                @foreach($tabs as $tab)
                    @php
                        $tabStyles = $tab->styles();
                        $tabState = $stepTabStates[$tab->value];
                        $correctionPermission = match ($tab) {
                            \Modules\Restaurant\Enums\OnboardingStep::BANKING => 'admin.restaurant.approvals.banking.show',
                            \Modules\Restaurant\Enums\OnboardingStep::TAX => 'admin.restaurant.approvals.tax.show',
                            \Modules\Restaurant\Enums\OnboardingStep::KYC => 'admin.kyc.submissions.review',
                            default => 'admin.restaurant.approvals.show',
                        };
                        $canRequestTabChanges = $canRequestChanges && bouncer('admin')->hasPermission($correctionPermission);

                    @endphp
                    <div class="review-tab-slot">
                    <a href="{{ route('admin.restaurant.approvals.show', ['id' => $restaurant->id, 'step' => $tab->value]) }}" @if($tab === $activeStep) aria-current="page" @endif
                       class="review-tab {{ $tabState['resubmitted'] ? 'is-resubmitted' : '' }} flex items-center gap-2 border-b-2 px-6 py-4 text-sm font-semibold {{ $tabStyles['text'] }} {{ $tab === $activeStep ? $tabStyles['bg'] . ' ' . $tabStyles['border'] : 'bg-white border-transparent' }}">
                        <span aria-hidden="true" class="flex h-8 w-8 shrink-0 items-center justify-center">{!! $tab->icon() !!}</span>
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
                            'correctionDisabled' => !$stepRecords->get($tab->value) || in_array($stepRecords->get($tab->value)->status, ['draft', 'not_started'], true),
                            'correctionView' => null,
                        ])
                    @endif
                    </div>
                @endforeach
                <a href="{{ route('admin.restaurant.approvals.show', ['id' => $restaurant->id, 'step' => 'review']) }}" @if($isReview) aria-current="page" @endif class="review-tab flex items-center gap-2 border-b-2 px-6 py-4 text-sm font-semibold text-gray-700 {{ $isReview ? 'bg-blue-50 border-blue-300' : 'bg-white border-transparent' }}">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h4"/></svg>
                    <span class="review-tab-label">Review</span>
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
            <div class="space-y-6 p-6" id="registration-panel">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Restaurant Information</h3>
                        <p class="review-help">Review the business and owner details before making a decision.</p>
                    </div>
                    @if($canEdit)
                        <button type="button" id="edit-registration" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold" aria-expanded="false" aria-controls="registration-edit">Edit information</button>
                    @endif
                </div>
                <div id="registration-details" class="space-y-5">
                    <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach($fields as $field => $label)
                            <div class="rounded-xl bg-gray-50 p-4"><dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</dt><dd class="mt-2 break-words text-sm text-gray-900">{{ $restaurant->{$field} ?: 'N/A' }}</dd></div>
                        @endforeach
                        <div class="rounded-xl bg-gray-50 p-4 md:col-span-2"><dt class="text-xs font-semibold uppercase text-gray-500">Description</dt><dd class="mt-2 whitespace-pre-line text-sm">{{ $restaurant->description ?: 'N/A' }}</dd></div>
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
                        <p class="review-help">Fields marked * are required. Restaurant names need at least 5 characters.</p>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach(array_diff_key($fields, array_flip(['country', 'state', 'city'])) as $field => $label)
                                <div>
                                    <label for="edit-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }}@if(in_array($field, ['name', 'phone'])) <span aria-hidden="true">*</span>@endif</label>
                                    <input id="edit-{{ $field }}" name="{{ $field }}" type="{{ $field === 'phone' ? 'tel' : 'text' }}" value="{{ old($field, $restaurant->{$field}) }}" maxlength="{{ $field === 'name' ? 150 : ($field === 'phone' ? 20 : 255) }}" @if($field === 'name') minlength="5" @endif @if(in_array($field, ['name', 'phone'])) required @endif aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
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
                        <div class="flex gap-3"><button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white">Save changes</button><button id="cancel-edit" type="button" class="rounded-lg border border-gray-300 px-4 py-2">Cancel</button></div>
                    </form>
                @endif

                <div class="border-t border-gray-100 pt-5">
                    <h4 class="font-semibold">Owner Information</h4>
                    <dl class="mt-3 grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                        <div><dt class="text-gray-500">Contact person</dt><dd class="mt-1">{{ $restaurant->admin?->name ?? 'N/A' }}</dd></div>
                        <div><dt class="text-gray-500">Email</dt><dd class="mt-1 break-words">{{ $restaurant->admin?->email ?? 'N/A' }}</dd></div>
                    </dl>
                </div>
                <dl class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-5 text-sm md:grid-cols-2">
                    <div><dt class="text-gray-500">Submitted</dt><dd class="mt-1">{{ $record?->submitted_at?->format('M d, Y h:i A') ?? 'N/A' }}</dd></div>
                    <div><dt class="text-gray-500">Reviewed</dt><dd class="mt-1">{{ $record?->reviewed_at?->format('M d, Y h:i A') ?? 'N/A' }} @if($record?->reviewer) by {{ $record->reviewer->name }} @endif</dd></div>
                </dl>
                @if($record?->rejection_reason && $record->status !== 'action_required')
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800"><h4 class="font-semibold">Rejection reason</h4><p class="mt-2 whitespace-pre-line text-sm">{{ $record->rejection_reason }}</p></div>
                @endif
            </div>
            @elseif($activeStep === \Modules\Restaurant\Enums\OnboardingStep::MENU)
                @include('restaurant::admin.approvals.partials.menu')
            @elseif($activeStep === \Modules\Restaurant\Enums\OnboardingStep::KYC)
                @include('restaurant::admin.approvals.partials.kyc')
            @elseif($activeStep === \Modules\Restaurant\Enums\OnboardingStep::TAX)
                @include('restaurant::admin.approvals.partials.tax')
            @else
                @include('restaurant::admin.approvals.partials.banking')
            @endif
            @if(!$isProfile && !$isReview && !in_array($activeStep, [\Modules\Restaurant\Enums\OnboardingStep::MENU, \Modules\Restaurant\Enums\OnboardingStep::KYC], true))
                @include('restaurant::admin.approvals.partials.review-actions')
            @endif
        </section>
        <aside class="review-sidebar">
            <section class="review-timeline rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="review-eyebrow">APPLICATION STEPS</p>
                <h3 class="review-timeline-title">Onboarding Timeline</h3>
                <p class="review-timeline-description">Track submissions and review each step.</p>
                <ol class="review-timeline-list" tabindex="0" aria-label="Onboarding steps and submission times">
                    @foreach($steps as $step)
                        @php
                            $timelineRecord = $stepRecords->get($step->value);
                            $timelineState = $stepTabStates[$step->value];
                            $canOpenStep = in_array($step, $tabs, true);
                            $stepStyles = $step->styles();
                            $timelineApproved = $timelineRecord?->status === 'approved';
                            $canViewHistory = match ($step) {
                                \Modules\Restaurant\Enums\OnboardingStep::BANKING => $canViewBanking,
                                \Modules\Restaurant\Enums\OnboardingStep::TAX => $canViewTax,
                                \Modules\Restaurant\Enums\OnboardingStep::KYC => $canViewKyc,
                                default => true,
                            };
                        @endphp
                        <li class="review-step {{ $step === $activeStep ? 'review-step-current' : '' }} {{ $timelineState['resubmitted'] ? 'review-step-resubmitted' : '' }} {{ $timelineState['refill_requested'] ? 'review-step-refill-requested' : '' }}" @if($step === $activeStep) aria-current="step" @endif>
                            <span aria-hidden="true" class="review-step-marker {{ $timelineApproved ? 'review-step-marker-approved' : $stepStyles['bg'] . ' ' . $stepStyles['text'] . ' ' . $stepStyles['border'] }}">
                                @if($timelineApproved)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                                @else
                                    {!! $step->icon() !!}
                                @endif
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
                                @if($timelineRecord?->submitted_at || $timelineState['resubmitted_at'] || $timelineRecord?->reviewed_at || $timelineRecord?->reviewer)
                                    <div class="review-step-meta">
                                        @if($timelineRecord?->submitted_at)<p><span>Submitted</span><time datetime="{{ $timelineRecord->submitted_at->toIso8601String() }}">{{ $timelineRecord->submitted_at->format('M d, Y · h:i A') }}</time></p>@endif
                                        @if($timelineState['resubmitted_at'])<p><span>Resubmitted</span><time datetime="{{ $timelineState['resubmitted_at']->toIso8601String() }}">{{ $timelineState['resubmitted_at']->format('M d, Y · h:i A') }}</time></p>@endif
                                        @if($timelineRecord?->reviewed_at)<p><span>{{ $timelineState['refill_requested'] ? 'Refill requested' : 'Reviewed' }}</span><time datetime="{{ $timelineRecord->reviewed_at->toIso8601String() }}">{{ $timelineRecord->reviewed_at->format('M d, Y · h:i A') }}</time></p>@endif
                                        @if($timelineRecord?->reviewer)<p class="review-step-reviewer">By {{ $timelineRecord->reviewer->name }}</p>@endif
                                    </div>
                                @endif
                                @if($timelineRecord?->rejection_reason)<p class="review-step-reason">{{ $timelineRecord->rejection_reason }}</p>@endif
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
    @if(($step !== \Modules\Restaurant\Enums\OnboardingStep::BANKING || $canViewBanking) && ($step !== \Modules\Restaurant\Enums\OnboardingStep::TAX || $canViewTax) && ($step !== \Modules\Restaurant\Enums\OnboardingStep::KYC || $canViewKyc))
        @include('restaurant::admin.approvals.partials.step-history', ['histories' => $stepHistories->get($step->value, collect())])
    @endif
@endforeach
@if(!$isProfile && !$isReview && !in_array($activeStep, [\Modules\Restaurant\Enums\OnboardingStep::MENU, \Modules\Restaurant\Enums\OnboardingStep::KYC], true))
<dialog id="registration-rejection" aria-labelledby="rejection-title" aria-describedby="rejection-description" class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl">
    <form action="{{ route(match ($activeStep) { $registration => 'admin.restaurant.approvals.reject', \Modules\Restaurant\Enums\OnboardingStep::TAX => 'admin.restaurant.approvals.tax.reject', default => 'admin.restaurant.approvals.banking.reject' }, $restaurant->id) }}" method="POST" class="space-y-4" data-review-confirm="Reject {{ $activeStep->label() }}?">
        @csrf
        <input type="hidden" name="step_key" value="{{ $activeStep->value }}">
        @if($activeStep === \Modules\Restaurant\Enums\OnboardingStep::TAX)<input type="hidden" name="type" value="{{ $taxType }}">@endif
        <h3 id="rejection-title" class="text-lg font-semibold">Reject {{ $activeStep->label() }}</h3>
        <p id="rejection-description" class="text-sm text-gray-500">Explain what needs to be corrected before these details can be approved.</p>
        <label for="rejection-reason" class="block text-sm font-medium">Rejection reason *</label>
        <textarea id="rejection-reason" name="rejection_reason" rows="4" required maxlength="500" class="w-full rounded-lg border border-gray-300 p-3" placeholder="Enter the rejection reason…">{{ old('rejection_reason') }}</textarea>
        <p id="rejection-count" class="review-character-count">0 / 500 characters</p>
        <div class="flex justify-end gap-3"><button type="button" id="cancel-rejection" class="rounded-lg border border-gray-300 px-4 py-2">Cancel</button><button type="submit" class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white">Reject {{ $activeStep->label() }}</button></div>
    </form>
</dialog>
@endif
<style>
    .approval-page-content { width: 100%; min-width: 0; padding: 24px clamp(16px, 3vw, 48px); }
    .approval-review { --review-accent: #621f09; color: #172033; width: 100%; max-width: 1800px; min-width: 0; container-type: inline-size; }
    .approval-review .review-layout { display: grid; grid-template-columns: minmax(0, 1fr); align-items: start; }
    .approval-review .review-main { grid-column: auto; min-width: 0; }
    .approval-review .review-sidebar { min-width: 0; }
    .approval-review :is(dl, dl > div, fieldset, form) { min-width: 0; }
    .approval-review :is(dd, h2, h3, h4, .review-help, .review-header p) { overflow-wrap: anywhere; }
    .approval-review :is(input:not([type="radio"]):not([type="checkbox"]), select, textarea) { max-width: 100%; min-width: 0; }
    .approval-review .review-header { border-top: 4px solid var(--review-accent); background: linear-gradient(120deg, #fffaf6, #fff 65%); }
    .approval-review .review-eyebrow { margin: 16px 0 6px; font-size: 11px; font-weight: 700; letter-spacing: .12em; color: #786355; }
    .approval-review .review-timeline > .review-eyebrow { margin-top: 0; }
    .approval-review .review-help { margin-top: 5px; font-size: 13px; line-height: 1.6; color: #64748b; }
    .approval-review .review-tabs { display: grid; grid-template-columns: repeat(var(--review-tab-count), minmax(136px, 1fr)); width: 100%; max-width: 100%; overflow-x: auto; overscroll-behavior-x: contain; scroll-snap-type: x proximity; scroll-padding-inline: 8px; }
    .approval-review .review-tab { min-width: 0; min-height: 124px; flex-direction: column; justify-content: center; gap: 8px; padding: 12px 8px; border-inline-start: 1px solid #e5e7eb; text-align: center; text-decoration: none; scroll-snap-align: start; }
    .approval-review .review-tab:first-child { border-inline-start: 0; }
    .approval-review .review-tab { position: relative; }
    .approval-review .review-tab-alert { position: absolute; top: 8px; right: 8px; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #fff7ed; color: #b45309; }
    .approval-review .review-tab > svg { flex-shrink: 0; }
    .approval-review .review-tab-copy { display: flex; flex-direction: column; align-items: center; width: 100%; min-width: 0; gap: 6px; }
    .approval-review .review-tab-label { font-size: 12px; line-height: 1.5; white-space: normal; overflow-wrap: anywhere; }
    .approval-review .review-tab-number { font-size: 10px; line-height: 1.3; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .approval-review .review-tab-badges { display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 5px; }
    .approval-review .review-tab-status, .approval-review .review-resubmitted-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 999px; font-size: 10px; line-height: 1.5; font-weight: 600; white-space: nowrap; }
    .approval-review .review-tab.is-resubmitted { background: #fffbeb; border-inline-color: #fcd34d; border-bottom-color: #f59e0b; position: relative; }
    .approval-review .review-resubmitted-badge { background: #fef3c7; color: #92400e; }
    .approval-review #registration-details dl { gap: 0 24px; }
    .approval-review #registration-details dl > div { background: transparent; border-bottom: 1px solid #edf0f3; border-radius: 0; padding: 18px 0; }
    .approval-review #registration-details dd { font-size: 14px; line-height: 1.6; font-weight: 500; }
    .approval-review #registration-details dt { font-size: 10px; letter-spacing: .08em; }
    .approval-review button, #registration-rejection button { transition: background-color .15s, box-shadow .15s; }
    .approval-review :is(button, a, input, select, textarea):focus-visible,
    #registration-rejection :is(button, textarea):focus-visible { outline: 3px solid #93c5fd; outline-offset: 3px; }
    .approval-review #edit-registration { background: #fff; color: var(--review-accent); border-color: #dfcfc5; }
    .approval-review #edit-registration:hover { background: #fff7ed; }
    .approval-review #registration-edit [aria-invalid="true"] { border-color: #dc2626; background: #fffafa; }
    .approval-review .review-tab-actions { padding: 0 24px 24px; }
    .approval-review .review-actions { display: flex; flex-wrap: wrap; gap: 14px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; align-items: center; }
    .approval-review .review-action-copy { flex: 1 0 100%; }
    .approval-review .review-action-copy strong { font-size: 14px; }
    .approval-review .review-action-copy p { margin-top: 4px; font-size: 12px; color: #64748b; }
    .approval-review .review-action-buttons { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; width: 100%; }
    .approval-review .review-action-buttons button { padding: 9px 16px; border-radius: 8px; font-size: 12px; line-height: 1.5; font-weight: 600; }
    .approval-review .review-start { background: #2563eb; color: #fff; }
    .approval-review .review-start:hover { background: #1d4ed8; }
    .approval-review .review-approve { background: #059669; color: #fff; }
    .approval-review .review-approve:hover { background: #047857; }
    .approval-review .review-in-progress { margin-right: auto; padding: 4px 8px; border-radius: 6px; background: #dbeafe; color: #1e40af; font-size: 11px; font-weight: 600; }
    .approval-review .review-reject { background: #fff; color: #b91c1c; border: 1px solid #fecaca; }
    .approval-review .review-reject:hover { background: #fef2f2; }
    .approval-review button:disabled, #registration-rejection button:disabled { opacity: .6; cursor: wait; }
    .approval-review .review-timeline-title { margin: 0; color: #0f172a; font-size: 16px; line-height: 1.5; font-weight: 700; }
    .approval-review .review-timeline-description { margin: 5px 0 0; color: #64748b; font-size: 11px; line-height: 1.6; }
    .approval-review .review-timeline-list { margin: 22px 0 0; padding: 0; list-style: none; }
    .approval-review .review-step { position: relative; display: grid; grid-template-columns: 34px minmax(0, 1fr); gap: 10px; padding-bottom: 16px; }
    .approval-review .review-step:not(:last-child)::before { content: ''; position: absolute; top: 34px; bottom: 0; left: 16px; width: 1px; background: #e2e8f0; }
    .approval-review .review-step-marker { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; margin-top: 10px; border-width: 1px; border-style: solid; border-radius: 50%; box-shadow: 0 0 0 4px #fff; }
    .approval-review .review-step-marker svg { width: 18px; height: 18px; }
    .approval-review .review-step-marker.review-step-marker-approved { background: #dcfce7; color: #15803d; border-color: #86efac; }
    .approval-review .review-step-card { position: relative; min-width: 0; overflow-wrap: anywhere; border: 1px solid transparent; border-radius: 12px; transition: background .15s, border-color .15s; }
    .approval-review .review-step-current .review-step-card { border-color: #bfdbfe; background: #eff6ff; }
    .approval-review .review-step-resubmitted .review-step-card { border-color: #fcd34d; background: #fffbeb; }
    .approval-review .review-step-link { display: block; padding: 10px 39px 10px 10px; color: inherit; text-decoration: none; border-radius: 11px; }
    .approval-review a.review-step-link:hover { background: rgba(148, 163, 184, .08); }
    .approval-review .review-step-number { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; margin: 0 0 5px; font-size: 9px; line-height: 1.5; font-weight: 600; color: #64748b; letter-spacing: .06em; text-transform: uppercase; }
    .approval-review .review-step-viewing { color: #2563eb; letter-spacing: 0; }
    .approval-review .review-step-title { margin: 0; font-size: 12px; line-height: 1.5; font-weight: 600; color: #1e293b; }
    .approval-review .review-step-badges { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 7px; }
    .approval-review .review-step-badge { display: inline-flex; align-items: center; padding: 2px 7px; border-radius: 6px; font-size: 10px; line-height: 1.5; font-weight: 600; }
    .approval-review .review-step-meta { display: grid; gap: 6px; margin: 0 10px 10px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 10px; line-height: 1.6; color: #64748b; }
    .approval-review .review-step-meta p { margin: 0; }
    .approval-review .review-step-meta span { display: block; color: #64748b; }
    .approval-review .review-step-meta time { display: block; color: #475569; font-variant-numeric: tabular-nums; }
    .approval-review .review-step-reviewer { overflow-wrap: anywhere; }
    .approval-review .review-step-reason { margin: 0 10px 10px; padding: 7px 8px; border-radius: 6px; background: #fef2f2; color: #b91c1c; font-size: 11px; line-height: 1.6; }
    .approval-review .review-history-button { position: absolute; top: 10px; right: 7px; display: inline-flex; align-items: center; justify-content: center; width: 27px; height: 27px; border: 1px solid #e2e8f0; border-radius: 7px; background: #fff; color: #64748b; cursor: pointer; }
    .approval-review .review-history-button:hover:not(:disabled) { background: #eff6ff; border-color: #93c5fd; color: #2563eb; }
    .approval-review .review-step-link:focus-visible, .approval-review .review-history-button:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
    .approval-review .review-history-button:disabled { cursor: not-allowed; opacity: .45; }
    .approval-review .review-step:last-child { padding-bottom: 0; }
    .approval-review .kyc-counts { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
    .approval-review .kyc-count-card { --count-color: #64748b; padding: 13px 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; }
    .approval-review .kyc-count-card dt { display: flex; align-items: center; gap: 6px; font-size: 10px; line-height: 1.5; font-weight: 600; color: #64748b; }
    .approval-review .kyc-count-dot { flex-shrink: 0; width: 6px; height: 6px; border-radius: 50%; background: var(--count-color); }
    .approval-review .kyc-count-card dd { margin: 8px 0 5px; font-size: 23px; line-height: 1.2; font-weight: 700; color: var(--count-color); }
    .approval-review .kyc-count-card p { margin: 0; font-size: 10px; line-height: 1.5; color: #94a3b8; }
    .approval-review .kyc-count-pending { --count-color: #b45309; }
    .approval-review .kyc-count-approved { --count-color: #047857; }
    .approval-review .kyc-count-rejected { --count-color: #b91c1c; }
    .approval-review .kyc-count-resubmitted { --count-color: #4f46e5; }
    .approval-review .kyc-document { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; }
    .approval-review .kyc-document-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 16px; border-bottom: 1px solid #edf0f3; }
    .approval-review .kyc-document-header h4 { margin: 0; font-size: 13px; line-height: 1.6; font-weight: 600; overflow-wrap: anywhere; }
    .approval-review .kyc-document-header p { margin-top: 5px; font-size: 11px; color: #64748b; }
    .approval-review .kyc-status { flex-shrink: 0; padding: 4px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; }
    .approval-review .kyc-document-content { padding: 16px; }
    .approval-review .kyc-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 20px; }
    .approval-review .kyc-fields dt, .approval-review .kyc-section-label { font-size: 11px; font-weight: 500; color: #64748b; }
    .approval-review .kyc-fields dd { margin-top: 5px; font-size: 12px; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }
    .approval-review .kyc-files { margin-top: 16px; padding-top: 12px; border-top: 1px solid #edf0f3; }
    .approval-review .kyc-files ul { display: flex; flex-wrap: wrap; gap: 8px; margin: 8px 0 0; padding: 0; list-style: none; font-size: 12px; }
    .approval-review .kyc-file-link { display: inline-flex; gap: 8px; padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 7px; color: #2563eb; }
    .approval-review .kyc-file-link:hover { background: #eff6ff; }
    .approval-review .kyc-rejection { margin-top: 14px; padding: 12px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #991b1b; font-size: 12px; }
    .approval-review .kyc-rejection p { margin-top: 4px; white-space: pre-line; overflow-wrap: anywhere; }
    .approval-review .kyc-document-footer { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 16px; border-top: 1px solid #edf0f3; background: #f8fafc; }
    .approval-review .kyc-document-footer p { color: #64748b; font-size: 11px; }
    .approval-review .kyc-review-link { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 7px; background: #4f46e5; color: #fff; font-size: 12px; font-weight: 600; }
    .approval-review .kyc-review-link:hover { background: #4338ca; }
    .approval-review .kyc-type-checklist { padding: 16px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; }
    .approval-review .kyc-type-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 12px; margin: 14px 0 0; padding: 0; list-style: none; }
    .approval-review .kyc-type-item { display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #edf0f3; border-radius: 9px; background: #f8fafc; }
    .approval-review .kyc-type-name { display: block; font-size: 12px; line-height: 1.5; font-weight: 600; overflow-wrap: anywhere; }
    .approval-review a.kyc-type-name:hover { color: #4f46e5; text-decoration: underline; }
    .approval-review .kyc-type-status { display: block; margin-top: 3px; font-size: 11px; color: #64748b; }
    .approval-review .kyc-status-icon { display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; }
    .approval-review .kyc-status-icon-approved { background: #d1fae5; color: #047857; }
    .approval-review .kyc-status-icon-rejected { background: #fee2e2; color: #b91c1c; }
    .approval-review .kyc-status-icon-review { background: #dbeafe; color: #1d4ed8; }
    .approval-review .kyc-status-icon-pending { background: #fef3c7; color: #b45309; }
    .approval-review .kyc-status-icon-missing { background: #e2e8f0; color: #64748b; }
    .approval-review .kyc-document { scroll-margin-top: 24px; }
    .approval-review .kyc-document:target { outline: 2px solid #a5b4fc; outline-offset: 3px; }
    .approval-review .kyc-empty { padding: 32px 16px; border: 1px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; text-align: center; }
    .approval-review .kyc-empty h4 { font-size: 13px; font-weight: 600; }
    .approval-review .kyc-empty p { margin-top: 8px; font-size: 12px; color: #64748b; }
    .review-character-count { text-align: right; font-size: 12px; color: #64748b; }
    @container (min-width: 1150px) {
        .approval-review .review-layout { grid-template-columns: minmax(0, 1fr) 300px; }
        .approval-review .review-sidebar { position: sticky; top: 24px; }
    }
    @container (max-width: 1149px) {
        .approval-review .review-sidebar { grid-row: 1; position: static; }
        .approval-review .review-main { grid-row: 2; }
        .approval-review .review-timeline-list { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(220px, 1fr); gap: 16px; max-width: 100%; overflow-x: auto; overscroll-behavior-x: contain; scroll-snap-type: x proximity; padding: 4px 2px 12px; }
        .approval-review .review-timeline-list:focus-visible { outline: 2px solid #2563eb; outline-offset: 3px; }
        .approval-review .review-step { grid-template-columns: minmax(0, 1fr); grid-template-rows: 34px 1fr; gap: 12px; padding-bottom: 0; scroll-snap-align: start; }
        .approval-review .review-step-marker { margin-top: 0; }
        .approval-review .review-step:not(:last-child)::before { top: 17px; bottom: auto; left: 34px; right: -16px; width: auto; height: 1px; }
        .approval-review .review-step-card { border-color: #e2e8f0; }
    }
    @container (max-width: 900px) {
        .approval-review .kyc-counts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @container (max-width: 640px) {
        .approval-review .review-header, .approval-review .review-main > [id$="-panel"], .approval-review .review-timeline { padding: 16px; }
        .approval-review .review-tabs { grid-template-columns: repeat(var(--review-tab-count), minmax(128px, 1fr)); }
        .approval-review .review-tab { min-height: 116px; }
        .approval-review .review-main :is(dl.grid, form .grid) { grid-template-columns: minmax(0, 1fr); }
        .approval-review .review-main :is(dl.grid, form .grid) > * { grid-column: auto; }
        .approval-review .review-header > .grid { grid-template-columns: minmax(0, 1fr); }
        .approval-review .review-header h2 { font-size: 22px; }
        .approval-review .review-main > [id$="-panel"] > .flex { align-items: stretch; }
        .approval-review .review-main > [id$="-panel"] > .flex > :is(div, form) { flex-basis: 100%; }
        .approval-review .review-main form > .flex { flex-wrap: wrap; }
        .approval-review .review-main form > .flex > button { flex: 1 1 140px; min-height: 44px; }
        .approval-review #tax-type-selection fieldset { gap: 8px; }
        .approval-review #tax-type-selection label { flex: 1; min-height: 44px; }
        .approval-review .review-actions > form, .approval-review .review-actions > button { width: 100%; }
        .approval-review .review-actions > form > button { width: 100%; }
        .approval-review .review-tab-actions { padding: 0 18px 18px; }
        .approval-review .kyc-fields { grid-template-columns: minmax(0, 1fr); }
        .approval-review .kyc-counts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .approval-review .review-action-buttons > form { flex: 1; }
        .approval-review .review-action-buttons > form > button { width: 100%; }
        .approval-review .review-action-buttons > :is(form, button) { flex: 1 1 100%; }
        .approval-review .review-action-buttons button { min-height: 44px; }
        .approval-review .kyc-document-header { flex-wrap: wrap; }
        .approval-review .kyc-review-link { justify-content: center; min-height: 44px; width: 100%; }
        .approval-review .onboarding-review-document .report-fields :is(th, td) { padding: 8px; font-size: 12px; }
    }
    #registration-rejection {
        position: fixed;
        inset: 0;
        margin: auto;
        width: calc(100% - 2rem);
        max-width: 32rem;
        max-height: calc(100dvh - 2rem);
        overflow-y: auto;
    }
    #registration-rejection::backdrop { background: rgb(15 23 42 / 55%); }
    .step-history-dialog { position: fixed; inset: 0; margin: auto; width: calc(100% - 32px); max-width: 600px; height: min(560px, calc(100dvh - 48px)); max-height: calc(100dvh - 48px); padding: 0; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #fff; color: #172033; box-shadow: 0 24px 70px rgb(15 23 42 / 25%); }
    .step-history-dialog[open] { display: flex; flex-direction: column; }
    .step-history-dialog .history-header { display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; gap: 16px; padding: 16px 18px; border-bottom: 1px solid #e2e8f0; background: #fff; }
    .step-history-dialog .history-title { margin: 0; font-size: 14px; line-height: 1.5; font-weight: 600; }
    .step-history-dialog .history-subtitle { margin: 3px 0 0; color: #64748b; font-size: 11px; }
    .step-history-dialog .history-close { flex-shrink: 0; padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 7px; color: #475569; font-size: 11px; background: #fff; }
    .step-history-dialog .history-close:hover { background: #f1f5f9; }
    .step-history-dialog .history-body { flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 14px 18px; background: #f8fafc; }
    .step-history-dialog .history-list { display: grid; gap: 10px; margin: 0; padding: 0; list-style: none; }
    .step-history-dialog .history-entry { padding: 12px; border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; }
    .step-history-dialog .history-action { margin: 0; font-size: 12px; font-weight: 600; }
    .step-history-dialog time { color: #64748b; font-size: 10px; }
    .step-history-dialog .history-statuses { margin-top: 8px; font-size: 10px; }
    .step-history-dialog .history-statuses > span { padding-top: 3px; padding-bottom: 3px; }
    .step-history-dialog .history-actor { margin: 8px 0 0; font-size: 10px; color: #64748b; }
    .step-history-dialog .history-reason { margin: 6px 0 0; white-space: pre-line; overflow-wrap: anywhere; font-size: 12px; line-height: 1.6; color: #475569; }
    .step-history-dialog::backdrop { background: rgb(15 23 42 / 55%); }
    .step-history-dialog button:focus-visible { outline: 3px solid #93c5fd; outline-offset: 3px; }
    @media (max-width: 640px) {
        #registration-rejection { padding: 16px; }
        #registration-rejection form > .flex { flex-wrap: wrap; }
        #registration-rejection button { flex: 1 1 120px; min-height: 44px; }
        .step-history-dialog .history-header { align-items: flex-start; padding: 14px; }
        .step-history-dialog .history-title { overflow-wrap: anywhere; }
        .step-history-dialog .history-body { padding: 14px; }
        .step-history-dialog .history-close { min-height: 44px; }
    }
</style>
@include('restaurant::admin.approvals.partials.request-changes')
@endsection

@push('scripts')
<script src="{{ asset('js/kyc-step-review.js') }}" defer></script>
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
        form.addEventListener('submit', event => {
            if (submitting || !validateForm(form) || !window.confirm(form.dataset.reviewConfirm)) {
                event.preventDefault();
                return;
            }

            submitting = true;
            form.querySelectorAll('button[type="submit"]').forEach(button => {
                button.disabled = true;
                button.textContent = 'Submitting…';
            });
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
    taxTypeSelection?.addEventListener('submit', event => {
        const selected = taxTypeSelection.querySelector('input[type="radio"]:checked');
        if (changingTaxType || !selected || selected.value === originalTaxType) {
            event.preventDefault();
            return;
        }
        const label = selected.value === 'business' ? 'Business' : 'Individual';
        if (!window.confirm(`Are you sure you want to change the tax type to ${label}? This will submit its information for review again.`)) {
            event.preventDefault();
            taxTypeSelection.querySelectorAll('input[type="radio"]').forEach(radio => { radio.checked = radio.value === originalTaxType; });
            return;
        }
        changingTaxType = true;
        taxTypeSelection.setAttribute('aria-busy', 'true');
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
