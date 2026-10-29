{{-- Reuse with @include('restaurant::admin.partials.onboarding-timeline', ['timeline' => $onboardingTimeline]).
     Data is provided by RestaurantOnboardingDetailsService::build($restaurant). --}}
@php
    $timelineSteps = collect($timeline['steps']);
    $timelineComplete = $timeline['progress']['is_complete'];
    $storedCurrentStep = $timelineSteps->firstWhere('number', (int) ($timeline['restaurant']['onboarding_step'] ?? 0));
    $timelineCurrentKey = $timelineComplete ? null : (
        $storedCurrentStep && !$storedCurrentStep['is_approved']
            ? $storedCurrentStep['key'] : ($timeline['next_step']['key'] ?? null)
    );
    $formatTimelineDate = fn ($value) => \Illuminate\Support\Carbon::parse($value)->timezone(config('app.timezone', 'UTC'))->format('M d, Y · h:i A');
@endphp
<section class="restaurant-onboarding-timeline" aria-label="Restaurant onboarding timeline">
    <header class="rot-header">
        <div>
            <h3>Onboarding Timeline</h3>
            <p>{{ $timeline['progress']['approved_required_steps'] }} of {{ $timeline['progress']['required_steps'] }} required steps approved</p>
        </div>
        <div class="rot-header-actions">
            <span class="rot-completion {{ $timelineComplete ? 'rot-completion-done' : '' }}">{{ $timelineComplete ? 'All required steps approved' : $timeline['progress']['percentage'] . '% complete' }}</span>
            @if(!empty($detailsUrl))
                <a href="{{ $detailsUrl }}" class="rot-details-link">View onboarding details <span aria-hidden="true">→</span></a>
            @endif
        </div>
    </header>
    <div class="rot-scroll" tabindex="0" role="region" aria-label="Onboarding steps, scroll horizontally to view all">
        <ol class="rot-steps">
            @foreach($timelineSteps as $timelineStep)
                @php
                    $timelineState = match ($timelineStep['status']) {
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                        'action_required' => 'attention',
                        'under_review' => 'review',
                        'submitted', 'pending', 'resubmitted' => 'pending',
                        default => 'draft',
                    };
                    $timelineIsCurrent = $timelineCurrentKey === $timelineStep['key'];
                @endphp
                <li class="rot-step rot-step-{{ $timelineState }} {{ $timelineIsCurrent ? 'rot-step-current' : '' }}" @if($timelineIsCurrent) aria-current="step" @endif>
                    <div class="rot-marker" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            @switch($timelineState)
                                @case('approved')
                                    <path d="m5 12 4 4L19 6" />
                                    @break
                                @case('rejected')
                                    <path d="m6 6 12 12M18 6 6 18" />
                                    @break
                                @case('attention')
                                    <circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 4h.01"/>
                                    @break
                                @case('review')
                                    <circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 5 5" />
                                    @break
                                @case('pending')
                                    <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                                    @break
                                @default
                                    <path d="M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h4" />
                            @endswitch
                        </svg>
                    </div>
                    <div class="rot-card">
                        <p class="rot-step-number">Step {{ $timelineStep['number'] }}@if($timelineStep['can_skip'])<span class="rot-optional">Optional</span>@endif</p>
                        <h4>{{ $timelineStep['label'] }}</h4>
                        <div class="rot-badges">
                            <span class="rot-status">{{ $timelineStep['status_label'] }}</span>
                            @if($timelineIsCurrent)<span class="rot-current-label">Current</span>@endif
                            @if($timelineStep['resubmitted'])<span class="rot-resubmitted">Resubmitted</span>@endif
                        </div>
                        <dl class="rot-dates">
                            @foreach(['submitted_at' => 'Submitted', 'resubmitted_at' => 'Resubmitted', 'reviewed_at' => 'Reviewed'] as $dateField => $dateLabel)
                                @if($timelineStep[$dateField])
                                    <div><dt>{{ $dateLabel }}</dt><dd><time datetime="{{ $timelineStep[$dateField] }}">{{ $formatTimelineDate($timelineStep[$dateField]) }}</time></dd></div>
                                @endif
                            @endforeach
                        </dl>
                        @if($timelineStep['reviewer'])<p class="rot-reviewer">By {{ $timelineStep['reviewer']['name'] }}</p>@endif
                        @if($timelineStep['rejection_reason'])<p class="rot-reason">{{ $timelineStep['rejection_reason'] }}</p>@endif
                        @if(!$timelineStep['submitted_at'] && !$timelineStep['reviewed_at'] && !$timelineStep['resubmitted_at'])<p class="rot-awaiting">{{ $timelineStep['can_skip'] ? 'This step can be skipped.' : 'No submission recorded yet.' }}</p>@endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
@once
<style>
    .restaurant-onboarding-timeline { margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; color: #172033; box-shadow: 0 1px 2px rgb(15 23 42 / 5%); }
    .restaurant-onboarding-timeline .rot-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; padding: 20px 24px; border-bottom: 1px solid #edf0f3; }
    .restaurant-onboarding-timeline h3 { margin: 0; font-size: 16px; font-weight: 600; }
    .restaurant-onboarding-timeline .rot-header p { margin: 5px 0 0; color: #64748b; font-size: 12px; }
    .restaurant-onboarding-timeline .rot-completion { padding: 6px 10px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: 600; }
    .restaurant-onboarding-timeline .rot-completion-done { background: #d1fae5; color: #047857; }
    .restaurant-onboarding-timeline .rot-header-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
    .restaurant-onboarding-timeline .rot-details-link { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #c7d2fe; border-radius: 7px; color: #4338ca; background: #eef2ff; font-size: 12px; font-weight: 600; text-decoration: none; }
    .restaurant-onboarding-timeline .rot-details-link:hover { background: #e0e7ff; }
    .restaurant-onboarding-timeline .rot-details-link:focus-visible { outline: 2px solid #818cf8; outline-offset: 3px; }
    .restaurant-onboarding-timeline .rot-scroll { overflow-x: auto; padding: 24px; }
    .restaurant-onboarding-timeline .rot-scroll:focus-visible { outline: 2px solid #93c5fd; outline-offset: -2px; }
    .restaurant-onboarding-timeline .rot-steps { display: flex; margin: 0; padding: 0; list-style: none; }
    .restaurant-onboarding-timeline .rot-step { --rot-color: #64748b; --rot-bg: #f1f5f9; position: relative; flex: 1 0 205px; min-width: 0; padding-right: 20px; }
    .restaurant-onboarding-timeline .rot-step:last-child { padding-right: 0; }
    .restaurant-onboarding-timeline .rot-step:not(:last-child)::after { content: ''; position: absolute; top: 19px; left: 40px; right: 0; height: 2px; background: #e2e8f0; }
    .restaurant-onboarding-timeline .rot-step-approved { --rot-color: #047857; --rot-bg: #d1fae5; }
    .restaurant-onboarding-timeline .rot-step-approved::after { background: #a7f3d0 !important; }
    .restaurant-onboarding-timeline .rot-step-rejected { --rot-color: #b91c1c; --rot-bg: #fee2e2; }
    .restaurant-onboarding-timeline .rot-step-attention { --rot-color: #92400e; --rot-bg: #fef3c7; }
    .restaurant-onboarding-timeline .rot-step-review { --rot-color: #1d4ed8; --rot-bg: #dbeafe; }
    .restaurant-onboarding-timeline .rot-step-pending { --rot-color: #b45309; --rot-bg: #fef3c7; }
    .restaurant-onboarding-timeline .rot-marker { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border: 2px solid #fff; border-radius: 50%; background: var(--rot-bg); color: var(--rot-color); }
    .restaurant-onboarding-timeline .rot-card { margin-top: 12px; padding: 12px; border: 1px solid #edf0f3; border-radius: 9px; }
    .restaurant-onboarding-timeline .rot-step-current .rot-card { border-color: #93c5fd; background: #f8fbff; }
    .restaurant-onboarding-timeline .rot-step-number { display: flex; align-items: center; gap: 6px; margin: 0 0 6px; color: #64748b; font-size: 10px; }
    .restaurant-onboarding-timeline .rot-optional { padding: 2px 5px; border-radius: 4px; background: #f1f5f9; }
    .restaurant-onboarding-timeline h4 { margin: 0; font-size: 12px; line-height: 1.5; font-weight: 600; }
    .restaurant-onboarding-timeline .rot-badges { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px; font-size: 10px; font-weight: 600; }
    .restaurant-onboarding-timeline .rot-status { padding: 3px 6px; border-radius: 5px; color: var(--rot-color); background: var(--rot-bg); }
    .restaurant-onboarding-timeline .rot-resubmitted { padding: 3px 6px; border-radius: 5px; color: #92400e; background: #fef3c7; }
    .restaurant-onboarding-timeline .rot-current-label { padding: 3px 6px; color: #2563eb; }
    .restaurant-onboarding-timeline .rot-dates { display: grid; gap: 8px; margin: 12px 0 0; font-size: 10px; line-height: 1.6; }
    .restaurant-onboarding-timeline dt { color: #64748b; }
    .restaurant-onboarding-timeline dd { margin: 0; }
    .restaurant-onboarding-timeline .rot-reviewer, .restaurant-onboarding-timeline .rot-awaiting { margin: 10px 0 0; font-size: 10px; color: #64748b; }
    .restaurant-onboarding-timeline .rot-reason { margin-top: 10px; padding-top: 8px; border-top: 1px solid #fecaca; font-size: 11px; line-height: 1.5; color: #b91c1c; white-space: pre-line; overflow-wrap: anywhere; }
    @media (max-width: 640px) { .restaurant-onboarding-timeline .rot-header, .restaurant-onboarding-timeline .rot-scroll { padding: 16px; } }
</style>
@endonce
