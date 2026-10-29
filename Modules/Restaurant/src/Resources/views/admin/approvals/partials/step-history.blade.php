<dialog id="step-history-{{ $step->value }}" aria-labelledby="step-history-title-{{ $step->value }}" class="step-history-dialog">
    <header class="history-header">
        <div>
            <h3 id="step-history-title-{{ $step->value }}" class="history-title">{{ $step->label() }} history</h3>
            <p class="history-subtitle">{{ $histories->count() }} {{ $histories->count() === 1 ? 'activity' : 'activities' }} · Newest first</p>
        </div>
        <button type="button" data-close-history aria-label="Close history" class="history-close">Close</button>
    </header>
    <div class="history-body" tabindex="0" role="region" aria-label="{{ $step->label() }} history entries">
    <ol class="history-list">
        @forelse($histories as $history)
            @php
                $oldStatus = \Modules\Restaurant\Enums\OnboardingStatus::tryFrom($history->old_status ?? '');
                $newStatus = \Modules\Restaurant\Enums\OnboardingStatus::tryFrom($history->new_status ?? '');
            @endphp
            <li class="history-entry">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="history-action">{{ $history->action === 'changes_requested' ? 'Refill requested' : ($history->isResubmission() ? 'Resubmitted' : \Illuminate\Support\Str::headline($history->action ?: 'Status changed')) }}</h4>
                    <time datetime="{{ $history->created_at?->toIso8601String() }}" class="text-xs text-gray-500">{{ $history->created_at?->format('M d, Y h:i:s A') ?? 'N/A' }}</time>
                </div>
                <div class="history-statuses flex flex-wrap items-center gap-2">
                    @if($history->old_status !== null)
                        <span class="rounded-full px-2 py-1 font-semibold {{ $oldStatus?->color() ?? 'bg-gray-100 text-gray-800' }}">{{ $oldStatus?->label() ?? \Illuminate\Support\Str::headline($history->old_status) }}</span>
                        <span aria-label="changed to">&rarr;</span>
                    @endif
                    <span class="rounded-full px-2 py-1 font-semibold {{ $newStatus?->color() ?? 'bg-gray-100 text-gray-800' }}">{{ $newStatus?->label() ?? \Illuminate\Support\Str::headline($history->new_status ?? 'Unknown') }}</span>
                </div>
                <p class="history-actor">By {{ $history->changer?->name ?? ($history->changed_by ? 'User #' . $history->changed_by : 'System') }}</p>
                @if(filled($history->reason))
                    <p class="history-reason">{{ $history->reason }}</p>
                @endif
            </li>
        @empty
            <li class="py-8 text-center text-sm text-gray-500">No history recorded for this step yet.</li>
        @endforelse
    </ol>
    </div>
</dialog>
