@if($mode === 'view' && !empty($order_id))
@php
    $timelineEntries = collect();
    if (!empty($created_at)) {
        $timelineEntries->push([
            'title' => 'Order Created', 'time' => $created_at,
            'icon' => 'fa-plus', 'color' => '#2563EB', 'description' => null,
        ]);
    }

    $currentStatusCode = $status ?? 'W';
    $workflowCodes = array_map(
        fn ($step) => $step->code(),
        \Modules\Orders\Enums\OrderStatus::workflow($order_type ?? 'delivery')
    );
    $currentIndex = array_search($currentStatusCode, $workflowCodes, true);
    $visibleStatusCodes = $currentIndex !== false
        ? array_slice($workflowCodes, 0, $currentIndex + 1)
        : array_values(array_unique(['W', $currentStatusCode]));
    $statusIcons = [
        'W' => 'fa-clock', 'O' => 'fa-inbox', 'P' => 'fa-check-circle',
        'H' => 'fa-spinner', 'Z' => 'fa-check', 'N' => 'fa-truck',
        'D' => 'fa-check-circle', 'C' => 'fa-times', 'F' => 'fa-times-circle', 'R' => 'fa-undo',
    ];

    foreach ($visibleStatusCodes as $timelineStatus) {
        $timelineStatusInfo = fn_get_order_status($timelineStatus, 'O');
        $timelineEnum = \Modules\Orders\Enums\OrderStatus::fromCode($timelineStatus);
        $isCurrentStep = $timelineStatus === $currentStatusCode;
        $timelineTime = match (true) {
            $timelineStatus === 'W' => $created_at ?? null,
            $timelineStatus === 'C' && !empty($cancelled_at) => $cancelled_at,
            $timelineStatus === 'D' && !empty($actual_delivery_time) => $actual_delivery_time,
            $isCurrentStep => $updated_at ?? null,
            default => null,
        };
        $timelineEntries->push([
            'title' => $timelineStatusInfo->name ?? $timelineEnum?->label() ?? 'Status Updated',
            'time' => $timelineTime,
            'icon' => $statusIcons[$timelineStatus] ?? 'fa-check',
            'color' => $timelineStatusInfo->color ?? '#6B7280',
            'description' => $timelineStatus === 'C' ? ($cancellation_reason ?? null) : null,
            'inferred' => !$isCurrentStep && $timelineStatus !== 'W',
            'timeLabel' => $isCurrentStep && !in_array($timelineStatus, ['W', 'C', 'D'], true) ? 'Last updated: ' : null,
        ]);
    }

    foreach ($transactions ?? [] as $transaction) {
        if (($transaction['status'] ?? '') !== 'completed' || !in_array($transaction['type'] ?? '', ['payment', 'refund'], true)) {
            continue;
        }
        $isRefund = $transaction['type'] === 'refund';
        $timelineEntries->push([
            'title' => $isRefund ? 'Refund Completed' : 'Payment Received',
            'time' => $transaction['processed_at'] ?? $transaction['created_at'] ?? null,
            'icon' => $isRefund ? 'fa-undo' : 'fa-credit-card',
            'color' => $isRefund ? '#D97706' : '#16A34A',
            'description' => fn_convert_currency($transaction['amount'], $transaction['currency'] ?? $currency ?? 'USD'),
        ]);
    }

    if (!empty($stock_restored_at)) {
        $timelineEntries->push([
            'title' => 'Reserved Stock Returned', 'time' => $stock_restored_at,
            'icon' => 'fa-box', 'color' => '#16A34A', 'description' => null,
        ]);
    }

@endphp
<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-history mr-2 text-primary-100"></i>Order Timeline
    </h3>
    <div class="relative">
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
        <div class="space-y-4">
            @foreach($timelineEntries as $entry)
            <div class="relative flex items-start">
                <div class="absolute left-0 w-8 h-8 rounded-full flex items-center justify-center z-10"
                    style="background-color: {{ $entry['color'] }};">
                    <i class="fas {{ $entry['icon'] }} text-white text-xs"></i>
                </div>
                <div class="ml-12">
                    <p class="text-sm font-semibold text-gray-900">{{ $entry['title'] }}</p>
                    @if(!empty($entry['description']))
                    <p class="text-xs text-gray-600 mt-1">{{ $entry['description'] }}</p>
                    @endif
                    @if($entry['time'])
                    <p class="text-xs text-gray-500 mt-1">{{ $entry['timeLabel'] ?? '' }}{{ \Carbon\Carbon::parse($entry['time'])->format('M d, Y h:i A') }}</p>
                    @elseif($entry['inferred'] ?? false)
                    <p class="text-xs text-gray-400 mt-1">Shown from current workflow status; time not recorded.</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
