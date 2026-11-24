{{-- create form --}}
<form class="form-ajax" method="POST" action="{{ route('activity-log.advance-logging.block-ip', $log->id) }}">
    @csrf

    @if($block)
        <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700">Block Status</span>
                @php
                    $isActiveBlock = !empty($block) && $block->is_active && ($block->expires_at === null || optional($block->expires_at)->isFuture());
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $isActiveBlock ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $isActiveBlock ? 'Blocked' : 'Not Blocked' }}
                </span>
            </div>

            <div class="space-y-3 text-sm">
                <div class="rounded-lg border border-gray-100 bg-white px-3 py-2">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Reason</p>
                    <p class="mt-1 font-medium text-gray-800">{{ $block->reason ?? 'No active block reason' }}</p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-gray-100 bg-white px-3 py-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Expires At</p>
                        <p class="mt-1 font-medium text-gray-800">
                            {{ !empty($block) && $block->expires_at ? optional($block->expires_at)->format('Y-m-d H:i:s') : 'Indefinite / N/A' }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-white px-3 py-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Blocked By</p>
                        <p class="mt-1 font-medium text-gray-800">
                            {{ !empty($block) && $block->blocked_by_type ? class_basename($block->blocked_by_type) . ' #' . ($block->blocked_by_id ?? '-') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="action" value="0">
    @else
        <input type="hidden" name="action" value="1">
    @endif
        <!-- Name Field -->
        <div class="mb-2 mt-2">
            <label for="ip_address" class="custom-label required">IP Address</label>
            <input type="text" name="ip_address" id="ip_address" value="{{ $log->ip_address }}" readonly
                class="input-field" />
        </div>

        <!-- Name Field -->
        <div class="mb-2">
            <label for="reason" class="custom-label required">Reason for Blocking</label>
            <textarea name="reason" id="reason" rows="3" class="input-field"
                placeholder="Enter reason for blocking this IP address...">{{ $block?->reason }}</textarea>
        </div>

        <div class="mb-2">
            <label for="block_duration" class="custom-label required">Block Duration</label>
            <select name="block_duration" id="block_duration" class="input-field">
                <option value="60" @if($block?->expires_at && $block->expires_at->diffInMinutes(now()) === 60) selected @endif>1 Hour</option>
                <option value="360" @if($block?->expires_at && $block->expires_at->diffInMinutes(now()) === 360) selected @endif>6 Hours</option>
                <option value="720" @if($block?->expires_at && $block->expires_at->diffInMinutes(now()) === 720) selected @endif>12 Hours</option>
                <option value="1440" @if($block?->expires_at && $block->expires_at->diffInMinutes(now()) === 1440) selected @endif>24 Hours</option>
                <option value="4320" @if($block?->expires_at && $block->expires_at->diffInMinutes(now()) === 4320) selected @endif>3 Days</option>
                <option value="10080" @if($block?->expires_at && $block->expires_at->diffInMinutes(now()) === 10080) selected @endif>7 Days</option>
                <option value="-1" @if($block?->expires_at === null) selected @endif>Indefinitely</option>
            </select>
        </div>


    <div class="mt-4 flex justify-end">
        @if($block)
            <x-button type="submit"  class="btn-primary" label="Unblock IP" icon='' name='button'/>
        @else
             <x-button type="submit"  class="btn-primary" label="Block IP" icon='' name='button'/>
        @endif
    </div>
</form>
