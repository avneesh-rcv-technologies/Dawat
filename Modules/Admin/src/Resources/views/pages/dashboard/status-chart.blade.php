@php
    $offset = 0;
    $compact = [\Modules\Admin\Support\DashboardNumber::class, 'compact'];
@endphp
<div class="ad-status-chart-layout">
    <div class="ad-status-pie-wrap">
        <svg class="ad-status-pie" viewBox="0 0 200 200" role="img" aria-labelledby="ad-status-pie-title ad-status-pie-description">
            <title id="ad-status-pie-title">Orders by status</title>
            <desc id="ad-status-pie-description">{{ number_format($total) }} paid orders in the selected period. Status counts and percentages are listed below.</desc>
            <circle cx="100" cy="100" r="76" fill="none" stroke="#f1eeeb" stroke-width="26"/>
            @foreach($statuses as $status)
                @if($total > 0 && $status['count'] > 0)
                    @php $share = $status['count'] / $total * 100; @endphp
                    <circle class="ad-status-slice" data-status-code="{{ $status['code'] ?? $loop->index }}" cx="100" cy="100" r="76" fill="none" stroke="{{ $status['color'] }}" stroke-width="26"
                        pathLength="100" stroke-dasharray="{{ $share }} {{ 100 - $share }}" stroke-dashoffset="{{ -$offset }}" transform="rotate(-90 100 100)" tabindex="0"
                        aria-label="{{ $status['label'] }}: {{ number_format($status['count']) }} orders, {{ round($share, 1) }} percent">
                        <title>{{ $status['label'] }} · {{ number_format($status['count']) }} orders ({{ round($share, 1) }}%)</title>
                    </circle>
                    @php $offset += $share; @endphp
                @endif
            @endforeach
            <text class="ad-status-pie-total" x="100" y="99" text-anchor="middle"><title>{{ number_format($total) }} orders</title>{{ $compact($total) }}</text>
            <text class="ad-status-pie-label" x="100" y="119" text-anchor="middle">TOTAL ORDERS</text>
        </svg>
        @if(!$total)<p class="ad-status-pie-empty">No orders in this period</p>@endif
    </div>
    <div class="ad-status-key" aria-label="Order status breakdown">
        @foreach($statuses as $status)
            <div data-status-label="{{ $status['code'] ?? $loop->index }}" tabindex="0" class="ad-status-key-item {{ !$status['count'] ? 'is-empty' : '' }}">
                <i style="background:{{ $status['color'] }}" aria-hidden="true"></i>
                <span>{{ $status['label'] }}</span>
                <div><strong title="{{ number_format($status['count']) }}" aria-label="{{ number_format($status['count']) }}">{{ $compact($status['count']) }}</strong><small>{{ $total ? round($status['count'] / $total * 100, 1) : 0 }}%</small></div>
            </div>
        @endforeach
    </div>
</div>
