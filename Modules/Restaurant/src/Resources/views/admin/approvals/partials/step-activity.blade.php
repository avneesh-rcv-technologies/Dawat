@php
    $activityRecord = $stepRecords->get($activeStep->value);
    $activityState = $stepTabStates[$activeStep->value];
    $activityHistory = $stepHistories->get($activeStep->value, collect());
    $latestRequest = $activityHistory->first(fn ($event) => $event->action === 'changes_requested' || $event->new_status === 'action_required');
    $latestReview = $activityHistory->first(fn ($event) => in_array($event->new_status, ['approved', 'rejected'], true) && $event->action !== 'changes_requested');
    $latestFeedback = $activityHistory->first(fn ($event) => filled($event->reason));
    $requestDate = $latestRequest?->created_at ?? ($activityState['refill_requested'] ? $activityRecord?->reviewed_at : null);
    $requestName = $latestRequest?->changer?->name ?? ($activityState['refill_requested'] ? $activityRecord?->reviewer?->name : null);
    $reviewDate = $latestReview?->created_at ?? (!$activityState['refill_requested'] ? $activityRecord?->reviewed_at : null);
    $reviewName = $latestReview?->changer?->name ?? (!$activityState['refill_requested'] ? $activityRecord?->reviewer?->name : null);
    $updatedAt = match ($activeStep) {
        \Modules\Restaurant\Enums\OnboardingStep::BANKING => $restaurant->bankingDetail?->updated_at,
        \Modules\Restaurant\Enums\OnboardingStep::TAX => $taxRecords->get($taxType)?->updated_at,
        default => null,
    };
@endphp
<section class="step-activity" aria-labelledby="step-activity-title">
    <header class="step-activity-header">
        <div>
            <p class="step-activity-eyebrow">{{ $activeStep->label() }}</p>
            <h4 id="step-activity-title">Submission &amp; review details</h4>
        </div>
        <div class="step-activity-actions">
            <span class="step-activity-status {{ $activityState['color'] }}">{{ $activityState['label'] }}</span>
            <button type="button" data-step-history="step-history-{{ $activeStep->value }}" aria-haspopup="dialog" aria-controls="step-history-{{ $activeStep->value }}">View history <span aria-hidden="true">↗</span></button>
        </div>
    </header>
    <dl class="step-activity-grid">
        <div><dt>Submitted at</dt><dd>@if($activityRecord?->submitted_at)<time datetime="{{ $activityRecord->submitted_at->toIso8601String() }}">{{ $activityRecord->submitted_at->format('M d, Y · h:i A') }}</time>@else Not submitted @endif</dd></div>
        <div><dt>Reviewed at</dt><dd>@if($reviewDate)<time datetime="{{ $reviewDate->toIso8601String() }}">{{ $reviewDate->format('M d, Y · h:i A') }}</time>@else Not reviewed @endif</dd></div>
        <div><dt>Reviewed by</dt><dd>{{ $reviewName ?? ($reviewDate ? 'Team member' : 'Not reviewed') }}</dd></div>
        <div><dt>Resubmitted at</dt><dd>@if($activityState['resubmitted_at'])<time datetime="{{ $activityState['resubmitted_at']->toIso8601String() }}">{{ $activityState['resubmitted_at']->format('M d, Y · h:i A') }}</time>@else No resubmission @endif</dd></div>
        <div><dt>Changes requested at</dt><dd>@if($requestDate)<time datetime="{{ $requestDate->toIso8601String() }}">{{ $requestDate->format('M d, Y · h:i A') }}</time>@else No change request @endif</dd></div>
        <div><dt>Changes requested by</dt><dd>{{ $requestDate ? ($requestName ?? 'Team member') : 'No change request' }}</dd></div>
        @if($updatedAt)<div><dt>Last updated</dt><dd><time datetime="{{ $updatedAt->toIso8601String() }}">{{ $updatedAt->format('M d, Y · h:i A') }}</time></dd></div>@endif
    </dl>
    @if($latestFeedback || filled($activityRecord?->rejection_reason))
        <div class="step-activity-feedback">
            <h5>Latest review feedback</h5>
            <p>{{ $latestFeedback?->reason ?? $activityRecord->rejection_reason }}</p>
            @if($latestFeedback)<span>{{ $latestFeedback->created_at?->format('M d, Y · h:i A') }} · {{ $latestFeedback->changer?->name ?? 'Team member' }}</span>@endif
        </div>
    @endif
</section>
