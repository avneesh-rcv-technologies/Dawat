@php
    $iconState = match ($documentStatus) {
        'approved' => 'approved',
        'rejected' => 'rejected',
        'action_required' => 'attention',
        'under_review' => 'review',
        'pending', 'submitted', 'resubmitted' => 'pending',
        default => 'missing',
    };
@endphp
<span class="kyc-status-icon kyc-status-icon-{{ $iconState }}" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
        @switch($iconState)
            @case('approved')
                <path d="m5 12 4 4L19 6" />
                @break
            @case('rejected')
            @case('missing')
                <path d="m6 6 12 12M18 6 6 18" />
                @break
            @case('review')
                <circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 5 5M8 10.5l1.5 1.5 3-3" />
                @break
            @case('attention')
                <circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 4h.01"/>
                @break
            @default
                <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
        @endswitch
    </svg>
</span>
