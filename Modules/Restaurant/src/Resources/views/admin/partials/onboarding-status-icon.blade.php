<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($markerStatus)
        @case('approved')
        @case('active')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('submitted')
        @case('pending')
        @case('resubmitted')
            <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
            @break
        @case('under_review')
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5h4"/>
            @break
        @case('action_required')
        @case('refill_requested')
            <path d="M20 7v5h-5M4 17v-5h5"/><path d="M6.1 7a7 7 0 0 1 11.5-1.4L20 8M4 16l2.4 2.4A7 7 0 0 0 17.9 17"/>
            @break
        @case('rejected')
            <path d="m7 7 10 10M17 7 7 17"/>
            @break
        @case('suspended')
            <path d="M9 6v12M15 6v12"/>
            @break
        @default
            <path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15Z"/>
    @endswitch
</svg>
