@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ filemtime(public_path('css/admin-dashboard.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/admin-dashboard.js') }}?v={{ filemtime(public_path('js/admin-dashboard.js')) }}" defer></script>
@endpush
@php
    $d = $ownerDashboard;
    $compact = [\Modules\Admin\Support\DashboardNumber::class, 'compact'];
    $periodLabel = $filters->start->format('M j, Y').' – '.$filters->end->format('M j, Y');
    $orderLabels = ($d['statuses'] ?? collect())->keyBy('code');
    $cards = $d ? [
        ['label' => 'Orders placed', 'value' => $d['totalOrders'], 'icon' => 'orders', 'tone' => 'orange', 'scope' => 'Selected period', 'help' => $d['orderChange'] !== null ? (($d['orderChange'] >= 0 ? '+' : '').$d['orderChange'].'% vs previous period') : 'Orders in the selected period'],
        ['label' => 'Completed orders', 'value' => $d['completedOrders'], 'icon' => 'check', 'tone' => 'green', 'scope' => 'Selected period', 'help' => $d['completionRate'].'% completion rate'],
        ['label' => 'Needs attention', 'value' => $d['pendingCount'], 'icon' => 'clock', 'tone' => 'amber', 'scope' => 'Live queue', 'help' => 'Awaiting your acknowledgement'],
        ['label' => 'Cancelled / failed', 'value' => $d['cancelledOrders'] + $d['failedOrders'], 'icon' => 'alert', 'tone' => 'red', 'scope' => 'Selected period', 'help' => $compact($d['cancelledOrders']).' cancelled · '.$compact($d['failedOrders']).' failed'],
        ['label' => 'Today’s orders', 'value' => $d['todayCount'], 'icon' => 'calendar', 'tone' => 'blue', 'scope' => 'Live schedule', 'help' => $d['completedToday'].' completed today'],
        ['label' => 'Upcoming orders', 'value' => $d['upcomingCount'], 'icon' => 'clock', 'tone' => 'purple', 'scope' => 'Live schedule', 'help' => 'Scheduled after today'],
        ['label' => 'Menu items available', 'value' => $d['availableCount'], 'icon' => 'store', 'tone' => 'green', 'scope' => 'Live snapshot', 'help' => $d['menuCount'].' items in your menu'],
        ['label' => 'Customer rating', 'value' => $d['rating'] === null ? '—' : number_format((float) $d['rating'], 1), 'icon' => 'star', 'tone' => 'amber', 'scope' => 'Live snapshot', 'help' => $d['reviewCount'].' approved reviews'],
    ] : [];
    $orderSeries = $d ? [
        ['key' => 'count', 'label' => 'Orders placed', 'color' => '#ff6210', 'short' => $compact($d['totalOrders']), 'full' => number_format($d['totalOrders']).' orders'],
        ['key' => 'completed', 'label' => 'Completed', 'color' => '#21a88a', 'short' => $compact($d['completedOrders']), 'full' => number_format($d['completedOrders']).' orders'],
    ] : [];
@endphp
<div class="admin-dashboard" data-admin-dashboard>
    <header class="ad-heading">
        <div><p class="ad-eyebrow">DAWAT · RESTAURANT WORKSPACE</p><h1>{{ $d ? $d['restaurant']->name : 'Restaurant dashboard' }}<span class="ad-heading-dot">.</span></h1><p>Welcome back{{ $userName ? ', '.$userName : '' }}. Here’s your restaurant’s latest activity.</p></div>
        @if($d)<div class="ad-heading-actions"><span class="ad-updated"><i></i> Updated {{ $d['updatedAt']->format('g:i A') }} <span>{{ $filters->timezone }}</span></span><a class="ad-button ad-button-secondary ad-refresh" href="{{ route('dashboard.index', request()->only(['period', 'fulfillment', 'start_date', 'end_date'])) }}" aria-label="Refresh dashboard">@include('admin::pages.dashboard.icon', ['name' => 'refresh']) Refresh</a></div>@endif
    </header>

    @if(!$d)
        <section class="ad-panel"><div class="ad-empty"><strong>Your restaurant dashboard is being prepared</strong><p>We couldn’t find a restaurant linked to this account. Contact support if you think this is a mistake.</p></div></section>
    @else
        @if($d['blockers'])<section class="ad-readiness" style="margin-bottom:20px"><span class="ad-readiness-icon">@include('admin::pages.dashboard.icon', ['name' => 'alert'])</span><div><h2>Setup items to review</h2><p>{{ implode(' · ', $d['blockers']) }}</p></div></section>@endif
        <form class="ad-filters" method="GET" action="{{ route('dashboard.index') }}" data-dashboard-filters>
            <div class="ad-filter-intro">@include('admin::pages.dashboard.icon', ['name' => 'calendar'])<div><strong>Reporting period</strong><span>{{ $periodLabel }}</span></div></div>
            <div class="ad-filter-fields">
                <label><span>Date range</span><select name="period" data-dashboard-period>@foreach(\Modules\Admin\Support\DashboardFilters::PERIODS as $value => $label)<option value="{{ $value }}" @selected($filters->period === $value)>{{ $label }}</option>@endforeach</select></label>
                <label class="ad-custom-date"><span>From</span><input name="start_date" type="date" value="{{ $filters->start->format('Y-m-d') }}" max="{{ $d['updatedAt']->format('Y-m-d') }}" required></label>
                <label class="ad-custom-date"><span>To</span><input name="end_date" type="date" value="{{ $filters->end->format('Y-m-d') }}" max="{{ $d['updatedAt']->format('Y-m-d') }}" required></label>
                <label><span>Fulfillment · orders</span><select name="fulfillment"><option value="all" @selected($filters->fulfillment === 'all')>All fulfillment types</option><option value="delivery" @selected($filters->fulfillment === 'delivery')>Delivery</option><option value="takeaway" @selected($filters->fulfillment === 'takeaway')>Pickup</option></select></label>
                <button class="ad-button ad-button-primary" type="submit">Apply filters</button><a class="ad-reset" href="{{ route('dashboard.index') }}">Reset</a>
            </div><p class="ad-filter-note">Order reports use the date an order was placed. Today’s schedule, menu, and review snapshot stay current.</p>
        </form>
        <section class="ad-kpis" aria-label="Restaurant key performance indicators">
            @foreach($cards as $card)<article class="ad-kpi ad-tone-{{ $card['tone'] }}"><div class="ad-kpi-top"><span class="ad-kpi-icon">@include('admin::pages.dashboard.icon', ['name' => $card['icon']])</span><span class="ad-scope">{{ $card['scope'] }}</span></div><h2>{{ $card['label'] }}</h2><div class="ad-kpi-bottom"><strong title="{{ is_numeric($card['value']) ? number_format((float)$card['value'], is_float($card['value']) ? 1 : 0) : $card['value'] }}">{{ is_numeric($card['value']) ? $compact($card['value']) : $card['value'] }}</strong></div><p>{{ $card['help'] }}</p></article>@endforeach
        </section>
        <div class="ad-section-heading"><div><p class="ad-eyebrow">OPERATIONAL PULSE</p><h2>Orders & fulfillment</h2></div><span class="ad-section-meta">{{ $periodLabel }}</span></div>
        <div class="ad-primary-grid">
            <section class="ad-panel ad-trend"><div class="ad-panel-header"><div><p class="ad-eyebrow">ORDER ACTIVITY</p><h2>A closer look at your orders</h2><p>{{ ['day' => 'Daily', 'week' => 'Weekly', 'month' => 'Monthly'][$d['trend']['unit']] }} totals · {{ $filters->fulfillment === 'takeaway' ? 'Pickup' : ($filters->fulfillment === 'delivery' ? 'Delivery' : 'All fulfillment types') }}</p></div><span class="ad-legend"><i></i> Your orders</span></div>
                @include('admin::pages.dashboard.chart', ['chartId' => 'owner-orders', 'chartTitle' => 'Restaurant order activity', 'chartUnit' => $d['trend']['unit'], 'points' => $d['trend']['points'], 'series' => $orderSeries, 'chartCurrency' => null])
                <div class="ad-chart-footer"><div><i class="ad-marker is-orange"></i><span>Delivery</span><strong>{{ $compact($d['deliveryCount']) }}</strong></div><div><i class="ad-marker is-dark"></i><span>Pickup</span><strong>{{ $compact($d['pickupCount']) }}</strong></div></div>
            </section>
            <section class="ad-panel"><div class="ad-panel-header"><div><p class="ad-eyebrow">FULFILLMENT HEALTH</p><h2>Orders by status</h2><p>Latest status · selected period</p></div></div>@include('admin::pages.dashboard.status-chart', ['statuses' => $d['statuses'], 'total' => $d['totalOrders']])</section>
        </div>
        <div class="ad-secondary-grid">
            <section class="ad-panel"><div class="ad-panel-header"><div><p class="ad-eyebrow">TODAY’S SCHEDULE</p><h2>Orders to prepare</h2><p>Orders scheduled for today · {{ $d['todayCount'] }} total</p></div><span class="ad-tag">Live</span></div>
                <div class="ad-review-list">@forelse($d['attention'] as $order)<article><span class="ad-avatar">{{ mb_substr($order->customer?->name ?? 'G', 0, 1) }}</span><div><h3>{{ $order->order_number }}</h3><p>{{ $order->customer?->name ?? 'Guest customer' }} · {{ $order->order_type === 'takeaway' ? 'Pickup' : ucfirst(str_replace('_', ' ', $order->order_type)) }}</p><span>{{ ($order->preferred_delivery_date ?? $order->estimated_delivery_time ?? $order->created_at)->timezone($filters->timezone)->format('M j, g:i A') }}</span></div><span class="ad-badge {{ $order->status === 'W' ? 'is-amber' : 'is-neutral' }}">{{ $orderLabels->get($order->status)['label'] ?? $order->status }}</span></article>@empty<div class="ad-empty"><strong>No orders need attention today</strong><p>New orders will appear here.</p></div>@endforelse</div>
            </section>
            <section class="ad-panel"><div class="ad-panel-header"><div><p class="ad-eyebrow">COMING UP</p><h2>Upcoming orders</h2><p>Scheduled for the next few days</p></div><span class="ad-count">{{ $compact($d['upcomingCount']) }}</span></div>
                <div class="ad-review-list">@forelse($d['upcoming'] as $order)<article><span class="ad-avatar">{{ mb_substr($order->customer?->name ?? 'G', 0, 1) }}</span><div><h3>{{ $order->order_number }}</h3><p>{{ $order->customer?->name ?? 'Guest customer' }} · {{ $order->order_type === 'takeaway' ? 'Pickup' : ucfirst(str_replace('_', ' ', $order->order_type)) }}</p><span>{{ ($order->preferred_delivery_date ?? $order->estimated_delivery_time)->timezone($filters->timezone)->format('M j, g:i A') }}</span></div></article>@empty<div class="ad-empty"><strong>No upcoming orders</strong><p>Scheduled orders will appear here.</p></div>@endforelse</div>
            </section>
        </div>
        <section class="ad-panel ad-orders-panel" id="dashboard-orders"><div class="ad-panel-header"><div><p class="ad-eyebrow">LATEST ACTIVITY</p><h2>Your recent orders</h2><p>{{ $periodLabel }} · {{ $filters->fulfillment === 'all' ? 'All fulfillment types' : ($filters->fulfillment === 'takeaway' ? 'Pickup' : 'Delivery') }}</p></div></div>
            <div class="ad-table-scroll"><table><thead><tr><th>Order</th><th>Customer</th><th>Placed</th><th>Fulfillment</th><th>Order total</th><th>Status</th></tr></thead><tbody>@forelse($d['recentOrders'] as $order)@php $amount = fn_format_currency_amount($order->total, $order->currency ?: 'USD', true); @endphp<tr><td><strong>{{ $order->order_number }}</strong><span class="ad-table-note">{{ ucwords(str_replace('_', ' ', $order->payment_status)) }}</span></td><td><strong>{{ $order->customer?->name ?? 'Guest customer' }}</strong></td><td>{{ $order->created_at->timezone($filters->timezone)->format('M j, Y') }}<span class="ad-table-note">{{ $order->created_at->timezone($filters->timezone)->format('g:i A') }}</span></td><td><span class="ad-type">{{ $order->order_type === 'takeaway' ? 'Pickup' : ucfirst(str_replace('_', ' ', $order->order_type)) }}</span></td><td class="ad-money" title="{{ $amount }}">{{ \Modules\Admin\Support\DashboardNumber::currency($order->total, $order->currency ?: 'USD') }}</td><td><span class="ad-badge {{ in_array($order->status, ['C','F']) ? 'is-red' : ($order->status === 'D' ? 'is-green' : 'is-neutral') }}">{{ $orderLabels->get($order->status)['label'] ?? $order->status }}</span></td></tr>@empty<tr><td colspan="6"><div class="ad-empty"><strong>No orders match these filters</strong><p>Try a wider date range or select all fulfillment types.</p></div></td></tr>@endforelse</tbody></table></div>
        </section>
        <section class="ad-readiness"><span class="ad-readiness-icon">@include('admin::pages.dashboard.icon', ['name' => 'store'])</span><div><h2>{{ $compact($d['availableCount']) }} menu items available to order</h2><p>{{ $d['reviewCount'] }} approved customer reviews · Average rating {{ $d['rating'] === null ? 'not rated yet' : number_format((float)$d['rating'], 1).' / 5' }}.</p></div><span>{{ $d['unscheduledCount'] ? $compact($d['unscheduledCount']).' orders need scheduling' : 'Schedule is up to date' }}</span></section>
        <footer class="ad-footer"><span>Restaurant overview · order totals retain each order’s currency.</span><span>Reporting timezone: {{ $filters->timezone }}</span></footer>
    @endif
</div>
