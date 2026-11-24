@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ filemtime(public_path('css/admin-dashboard.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/admin-dashboard.js') }}?v={{ filemtime(public_path('js/admin-dashboard.js')) }}" defer></script>
@endpush
@php
    $d = $dashboard;
    $compact = [\Modules\Admin\Support\DashboardNumber::class, 'compact'];
    $actor = auth('admin')->user();
    $can = fn ($permission) => $actor->hasPermission($permission);
    $orderLabels = $d['statuses']->keyBy('code');
    $periodLabel = $filters->start->format('M j, Y').' – '.$filters->end->format('M j, Y');
    $dashboardCurrency = fn_get_setting('general.currency') ?: 'USD';
    $cards = [
        ['label' => 'Restaurants', 'value' => $d['restaurantCount'], 'icon' => 'store', 'tone' => 'orange', 'scope' => 'All time', 'help_exact' => '+'.number_format($d['newRestaurants']).' registered in this period', 'help' => '+'.$compact($d['newRestaurants']).' registered in this period', 'href' => $can('admin.restaurant.index') ? route('admin.restaurant.index') : null],
        ['label' => 'Pending applications', 'value' => $d['applicationCount'], 'icon' => 'clock', 'tone' => 'amber', 'scope' => 'Live queue', 'help' => 'Submitted and awaiting a decision', 'href' => '#dashboard-reviews'],
        ['label' => 'Document reviews', 'value' => $d['documentCount'], 'icon' => 'document', 'tone' => 'blue', 'scope' => 'Live queue', 'help' => 'Restaurants with sections to review', 'href' => '#dashboard-reviews'],
        ['label' => 'Ready for orders', 'value' => $d['eligibleCount'], 'icon' => 'check', 'tone' => 'green', 'scope' => 'Live snapshot', 'help' => 'Approved, verified and accepting orders', 'href' => '#dashboard-readiness'],
        ['label' => 'Customers', 'value' => $d['customerCount'], 'icon' => 'users', 'tone' => 'purple', 'scope' => 'All time', 'help_exact' => '+'.number_format($d['newCustomers']).' registered in this period', 'help' => '+'.$compact($d['newCustomers']).' registered in this period', 'href' => null],
        ['label' => 'Paid orders placed', 'value' => $d['totalOrders'], 'icon' => 'orders', 'tone' => 'orange', 'scope' => 'Selected period', 'help' => $d['orderChange'] !== null ? ($d['orderChange'] >= 0 ? '+' : '').$d['orderChange'].'% vs previous period' : ($d['previousOrders'] === 0 ? 'No previous-period orders to compare' : 'Compared with the previous period'), 'href' => '#dashboard-orders'],
        ['label' => 'Cancelled / failed', 'value' => $d['cancelledOrders'] + $d['failedOrders'], 'icon' => 'alert', 'tone' => 'red', 'scope' => 'Selected period', 'help_exact' => number_format($d['cancelledOrders']).' cancelled · '.number_format($d['failedOrders']).' failed', 'help' => $compact($d['cancelledOrders']).' cancelled · '.$compact($d['failedOrders']).' failed', 'href' => '#dashboard-statuses'],
        ['label' => 'Refunds needing attention', 'value' => $d['refundCount'], 'icon' => 'refund', 'tone' => 'amber', 'scope' => 'Live queue', 'help' => 'Pending, processing or failed refunds', 'href' => '#dashboard-refunds'],
    ];
@endphp
<div class="admin-dashboard" data-admin-dashboard>
    <header class="ad-heading">
        <div><p class="ad-eyebrow">DAWAT · ADMIN WORKSPACE</p><h1>Platform overview<span class="ad-heading-dot">.</span></h1><p>Welcome back{{ $userName ? ', '.$userName : '' }}. Here’s what needs your attention.</p></div>
        <div class="ad-heading-actions"><span class="ad-updated"><i></i> Updated {{ $d['updatedAt']->format('g:i A') }} <span>{{ $filters->timezone }}</span></span><a class="ad-button ad-button-secondary ad-refresh" href="{{ route('dashboard.index', request()->only(['period', 'fulfillment', 'start_date', 'end_date', 'currency'])) }}" aria-label="Refresh dashboard">@include('admin::pages.dashboard.icon', ['name' => 'refresh']) Refresh</a></div>
    </header>

    @if($errors->any())<div class="ad-validation" role="alert"><strong>Please check your filters.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="ad-filters" method="GET" action="{{ route('dashboard.index') }}" data-dashboard-filters>
        <div class="ad-filter-intro">@include('admin::pages.dashboard.icon', ['name' => 'calendar'])<div><strong>Reporting period</strong><span>{{ $periodLabel }}</span></div></div>
        <div class="ad-filter-fields">
            <label><span>Date range</span><select name="period" data-dashboard-period>@foreach(\Modules\Admin\Support\DashboardFilters::PERIODS as $value => $label)<option value="{{ $value }}" @selected($filters->period === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="ad-custom-date"><span>From</span><input name="start_date" type="date" value="{{ $filters->start->format('Y-m-d') }}" max="{{ $d['updatedAt']->format('Y-m-d') }}" required></label>
            <label class="ad-custom-date"><span>To</span><input name="end_date" type="date" value="{{ $filters->end->format('Y-m-d') }}" max="{{ $d['updatedAt']->format('Y-m-d') }}" required></label>
            <label><span>Fulfillment · orders</span><select name="fulfillment"><option value="all" @selected($filters->fulfillment === 'all')>All fulfillment types</option><option value="delivery" @selected($filters->fulfillment === 'delivery')>Delivery</option><option value="takeaway" @selected($filters->fulfillment === 'takeaway')>Pickup</option></select></label>
            <!-- @if($finance)<label><span>Revenue currency</span><select name="currency">@foreach($finance['currencies'] as $code)<option value="{{ $code }}" @selected($filters->currency === $code)>{{ $code }}</option>@endforeach</select></label>@endif -->
            <button class="ad-button ad-button-primary" type="submit">Apply filters</button><a class="ad-reset" href="{{ route('dashboard.index') }}">Reset</a>
        </div>
        <p class="ad-filter-note">Order reports use the date an order was placed. Live queues and all-time totals stay visible across date filters.</p>
    </form>

    <section class="ad-kpis" aria-label="Platform key performance indicators">
        @foreach($cards as $card)
            <article class="ad-kpi ad-tone-{{ $card['tone'] }}"><div class="ad-kpi-top"><span class="ad-kpi-icon">@include('admin::pages.dashboard.icon', ['name' => $card['icon']])</span><span class="ad-scope">{{ $card['scope'] }}</span></div><h2>{{ $card['label'] }}</h2><div class="ad-kpi-bottom"><strong data-kpi="{{ $card['icon'] }}" title="{{ number_format($card['value']) }}" aria-label="{{ number_format($card['value']) }}">{{ $compact($card['value']) }}</strong>@if($card['href'])<a href="{{ $card['href'] }}" aria-label="View {{ strtolower($card['label']) }}">@include('admin::pages.dashboard.icon', ['name' => 'arrow'])</a>@endif</div><p title="{{ $card['help_exact'] ?? $card['help'] }}">{{ $card['help'] }}</p></article>
        @endforeach
    </section>

    @if($finance)@include('admin::pages.dashboard.finance')@endif

    <div class="ad-section-heading"><div><p class="ad-eyebrow">OPERATIONAL PULSE</p><h2>Orders & fulfillment</h2></div><span class="ad-section-meta">{{ $periodLabel }}</span></div>
    <div class="ad-primary-grid">
        <section class="ad-panel ad-trend" aria-labelledby="dashboard-trend-title">
            <div class="ad-panel-header"><div><p class="ad-eyebrow">ORDER ACTIVITY</p><h2 id="dashboard-trend-title">A closer look at your orders</h2><p>{{ ['day' => 'Daily', 'week' => 'Weekly', 'month' => 'Monthly'][$d['trend']['unit']] }} totals · {{ $filters->fulfillment === 'takeaway' ? 'Pickup' : ($filters->fulfillment === 'delivery' ? 'Delivery' : 'All fulfillment types') }}</p></div><span class="ad-legend"><i></i> Paid orders</span></div>
            @php
                $orderSeries = [
                    ['key' => 'count', 'label' => 'Orders placed', 'color' => '#ff6210', 'short' => $compact($d['totalOrders']), 'full' => number_format($d['totalOrders']).' orders'],
                    ['key' => 'completed', 'label' => 'Delivered', 'color' => $orderLabels->get('D')['color'] ?? '#21a88a', 'short' => $compact($d['completedOrders']), 'full' => number_format($d['completedOrders']).' orders'],
                ];
            @endphp
            @include('admin::pages.dashboard.chart', ['chartId' => 'orders', 'chartTitle' => 'Order activity', 'chartUnit' => $d['trend']['unit'], 'points' => $d['trend']['points'], 'series' => $orderSeries, 'chartCurrency' => null])
            <div class="ad-chart-footer"><div><i class="ad-marker is-orange"></i><span>Delivery</span><strong title="{{ number_format($d['deliveryCount']) }}" aria-label="{{ number_format($d['deliveryCount']) }}">{{ $compact($d['deliveryCount']) }}</strong></div><div><i class="ad-marker is-dark"></i><span>Pickup</span><strong title="{{ number_format($d['pickupCount']) }}" aria-label="{{ number_format($d['pickupCount']) }}">{{ $compact($d['pickupCount']) }}</strong></div>@if($d['otherCount'])<div><i class="ad-marker"></i><span>Other</span><strong title="{{ number_format($d['otherCount']) }}" aria-label="{{ number_format($d['otherCount']) }}">{{ $compact($d['otherCount']) }}</strong></div>@endif</div>
        </section>
        <section class="ad-panel" id="dashboard-statuses"><div class="ad-panel-header"><div><p class="ad-eyebrow">FULFILLMENT HEALTH</p><h2>Orders by status</h2><p>Latest status · selected period</p></div></div>
            @include('admin::pages.dashboard.status-chart', ['statuses' => $d['statuses'], 'total' => $d['totalOrders']])
        </section>
    </div>

    <div class="ad-secondary-grid">
        <section class="ad-panel" id="dashboard-reviews"><div class="ad-panel-header"><div><p class="ad-eyebrow">ONBOARDING</p><h2>Ready for your review</h2><p>Oldest submissions first · live queue</p></div><span class="ad-tag">Live</span></div>
            <div class="ad-review-list">@forelse($d['reviewQueue'] as $restaurant)
                @php $waiting = $restaurant->onboardingSteps->whereIn('status', ['submitted','pending','under_review'])->pluck('step_key')->map(fn ($key) => \Modules\Restaurant\Enums\OnboardingStep::tryFrom($key)?->label() ?? ucfirst($key))->all(); @endphp
                <article><span class="ad-avatar">{{ mb_substr($restaurant->name, 0, 1) }}</span><div><h3>{{ $restaurant->name }}</h3><p>{{ implode(' · ', $waiting) ?: 'Certification review' }}</p><span>{{ ($restaurant->submitted_at ?? $restaurant->created_at)->timezone($filters->timezone)->format('M j, g:i A') }}</span></div>@if($can('admin.restaurant.approvals.show'))<a class="ad-text-link" href="{{ route('admin.restaurant.approvals.show', $restaurant->id) }}">Review<span class="ad-sr-only"> {{ $restaurant->name }}</span></a>@endif</article>
            @empty<div class="ad-empty">@include('admin::pages.dashboard.icon', ['name' => 'check'])<strong>You’re all caught up</strong><p>No applications or documents are awaiting review.</p></div>@endforelse</div>
            @if($can('admin.restaurant.approvals.index'))<a class="ad-panel-link" href="{{ route('admin.restaurant.approvals.index') }}">View all applications</a>@endif
        </section>
        <section class="ad-panel" id="dashboard-refunds"><div class="ad-panel-header"><div><p class="ad-eyebrow">PAYMENT OPERATIONS</p><h2>Refund attention</h2><p>Manual review · live queue</p></div><span class="ad-count" title="{{ number_format($d['refundCount']) }}">{{ $compact($d['refundCount']) }}</span></div>
            <div class="ad-refund-list">@forelse($d['refundQueue'] as $refund)@php $refundAmount = fn_format_currency_amount($refund->refund_amount, $refund->order?->currency ?: $dashboardCurrency, true); @endphp<article><div><h3>{{ $refund->order?->order_number ?? 'Order unavailable' }}</h3><p>{{ $refund->order?->vendor?->name ?? 'Restaurant unavailable' }}</p><span class="ad-badge {{ $refund->refund_status === 'failed' ? 'is-red' : 'is-amber' }}">{{ ucfirst($refund->refund_status) }}</span></div><div><strong title="{{ $refundAmount }}" aria-label="{{ $refundAmount }}">{{ \Modules\Admin\Support\DashboardNumber::currency($refund->refund_amount, $refund->order?->currency ?: $dashboardCurrency) }}</strong>@if($can('admin.refunds.index'))<a class="ad-text-link" href="{{ route('admin.refunds.show', $refund->id) }}">Open refund</a>@endif</div></article>@empty<div class="ad-empty">@include('admin::pages.dashboard.icon', ['name' => 'check'])<strong>No refunds awaiting action</strong><p>New refund requests will appear here.</p></div>@endforelse</div>
            @if($can('admin.refunds.index'))<a class="ad-panel-link" href="{{ route('admin.refunds.index') }}">View all refunds</a>@endif
        </section>
    </div>

    @if($finance)@include('admin::pages.dashboard.payouts')@endif

    <section class="ad-panel ad-orders-panel" id="dashboard-orders"><div class="ad-panel-header"><div><p class="ad-eyebrow">LATEST ACTIVITY</p><h2>Recent paid orders</h2><p>{{ $periodLabel }} · {{ $filters->fulfillment === 'all' ? 'All fulfillment types' : ($filters->fulfillment === 'takeaway' ? 'Pickup' : 'Delivery') }}</p></div>@if($can('admin.orders.index'))<a class="ad-button ad-button-secondary" href="{{ route('admin.orders.index') }}">View all orders</a>@endif</div>
        <div class="ad-table-scroll"><table><thead><tr><th>Order</th><th>Restaurant / customer</th><th>Placed</th><th>Fulfillment</th><th>Order total</th><th>Order status</th><th><span class="ad-sr-only">Open order</span></th></tr></thead><tbody>@forelse($d['recentOrders'] as $order)
            @php
                $status = $orderLabels[$order->status] ?? null;
                $orderAmount = fn_format_currency_amount($order->total, $order->currency ?: $dashboardCurrency, true);
            @endphp
            <tr><td><strong>{{ $order->order_number }}</strong><span class="ad-table-note">{{ ucwords(str_replace('_', ' ', $order->payment_status)) }}</span></td><td><strong>{{ $order->vendor?->name ?? 'Restaurant unavailable' }}</strong><span class="ad-table-note">{{ $order->customer?->name ?? 'Guest customer' }}</span></td><td>{{ $order->created_at->timezone($filters->timezone)->format('M j, Y') }}<span class="ad-table-note">{{ $order->created_at->timezone($filters->timezone)->format('g:i A') }}</span></td><td><span class="ad-type">{{ $order->order_type === 'takeaway' ? 'Pickup' : ucfirst(str_replace('_', ' ', $order->order_type)) }}</span></td><td class="ad-money" title="{{ $orderAmount }}" aria-label="{{ $orderAmount }}">{{ \Modules\Admin\Support\DashboardNumber::currency($order->total, $order->currency ?: $dashboardCurrency) }}</td><td><span class="ad-badge {{ in_array($order->status, ['C','F']) ? 'is-red' : ($order->status === 'D' ? 'is-green' : 'is-neutral') }}">{{ $status['label'] ?? $order->status }}</span></td><td>@if($can('admin.orders.show'))<a class="ad-order-link" href="{{ route('admin.orders.show', $order->id) }}" aria-label="View order {{ $order->order_number }}">@include('admin::pages.dashboard.icon', ['name' => 'arrow'])</a>@endif</td></tr>
        @empty<tr><td colspan="7"><div class="ad-empty"><strong>No orders match these filters</strong><p>Try a wider date range or select all fulfillment types.</p></div></td></tr>@endforelse</tbody></table></div>
    </section>
    <section class="ad-readiness" id="dashboard-readiness"><span class="ad-readiness-icon">@include('admin::pages.dashboard.icon', ['name' => 'store'])</span><div><h2 title="{{ number_format($d['eligibleCount']) }} restaurants ready to receive orders">{{ $compact($d['eligibleCount']) }} restaurants ready to receive orders</h2><p>Application approval, required onboarding approvals, active status, and order acceptance are checked separately.</p></div><span title="{{ number_format($d['pendingOrders']) }} paid orders awaiting acknowledgement">{{ $compact($d['pendingOrders']) }} paid orders awaiting acknowledgement</span></section>
    <footer class="ad-footer"><span>Operational overview · monetary values retain each order’s currency.</span><span>Reporting timezone: {{ $filters->timezone }}</span></footer>
</div>
