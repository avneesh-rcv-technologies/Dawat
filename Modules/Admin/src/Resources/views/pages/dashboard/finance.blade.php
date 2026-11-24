@php
    $financeCurrency = $finance['currency'];
    $financeFormat = fn ($amount) => fn_format_currency_amount($amount, $financeCurrency, true);
    $financeShort = fn ($amount) => \Modules\Admin\Support\DashboardNumber::currency($amount, $financeCurrency);
    $financialSeries = [];
    foreach (['revenue' => ['Net commission', '#ff6210'], 'gross' => ['Gross fees', '#7c83fd'], 'paid' => ['Restaurant paid', '#21a88a']] as $key => [$label, $color]) {
        $financialSeries[] = ['key' => $key, 'label' => $label, 'color' => $color, 'short' => $financeShort($finance[$key]), 'full' => $financeFormat($finance[$key])];
    }
@endphp
<div class="ad-section-heading" id="dashboard-finance"><div><p class="ad-eyebrow">FINANCIAL PERFORMANCE</p><h2>Revenue & restaurant payouts</h2></div><span class="ad-currency-chip">{{ $finance['symbol'] }} <strong>{{ $financeCurrency }}</strong></span></div>
<div class="ad-financial-grid">
    <section class="ad-panel ad-revenue-panel" aria-labelledby="ad-revenue-title">
        <div class="ad-panel-header"><div><h2 id="ad-revenue-title">Commission revenue</h2><p>Earned platform fees, adjusted for successful refund reversals.</p></div><span class="ad-period-tag">{{ $periodLabel }}</span></div>
        @include('admin::pages.dashboard.chart', ['chartId' => 'revenue', 'chartTitle' => 'Commission revenue and recorded restaurant payouts', 'chartUnit' => $finance['timeline']['unit'], 'points' => $finance['timeline']['points'], 'series' => $financialSeries, 'chartCurrency' => ['code' => $financeCurrency, 'symbol' => $finance['symbol'], 'decimals' => $finance['decimals']]])
        <div class="ad-revenue-footer"><div><span>Gross commission</span><strong title="{{ $financeFormat($finance['gross']) }}">{{ $financeShort($finance['gross']) }}</strong></div><div><span>Refund reversals</span><strong title="{{ $financeFormat($finance['reversed']) }}">{{ $financeShort($finance['reversed']) }}</strong></div><div><span>Commissioned orders</span><strong title="{{ number_format($finance['records']) }}">{{ $compact($finance['records']) }}</strong></div></div>
    </section>
    <section class="ad-panel ad-payout-panel" aria-labelledby="ad-payout-title">
        <div class="ad-panel-header"><div><p class="ad-eyebrow">SETTLEMENT SNAPSHOT</p><h2 id="ad-payout-title">Restaurant payouts</h2><p>Current balances for the selected orders</p></div><span class="ad-payout-icon">@include('admin::pages.dashboard.icon',['name'=>'refund'])</span></div>
        <div class="ad-payout-total"><span>Remaining payable</span><strong title="{{ $financeFormat($finance['outstanding']) }}">{{ $financeShort($finance['outstanding']) }}</strong><p>Payable amounts less recorded payments, per order.</p></div>
        <div class="ad-settlement-progress"><div><strong>{{ (float) $finance['payable'] > 0 ? $finance['paidPercent'].'% covered' : 'No payable balance' }}</strong><span>of restaurant payable</span></div><div class="ad-settlement-track"><span style="width:{{ $finance['paidPercent'] }}%"></span></div></div>
        <dl class="ad-payout-details"><div><dt><i class="is-green"></i>Paid to restaurants</dt><dd title="{{ $financeFormat($finance['paid']) }}">{{ $financeShort($finance['paid']) }}</dd></div><div><dt><i class="is-blue"></i>Total restaurant payable</dt><dd title="{{ $financeFormat($finance['payable']) }}">{{ $financeShort($finance['payable']) }}</dd></div><div><dt><i class="is-amber"></i>On hold · unpaid</dt><dd title="{{ $financeFormat($finance['states']->get('on_hold')?->outstanding ?? 0) }}">{{ $financeShort($finance['states']->get('on_hold')?->outstanding ?? 0) }}</dd></div>@if((float)$finance['recovery'] > 0)<div class="ad-recovery"><dt><i class="is-red"></i>Recovery due</dt><dd title="{{ $financeFormat($finance['recovery']) }}">{{ $financeShort($finance['recovery']) }}</dd></div>@endif</dl>
        @if($can('admin.order-commissions.index'))<a class="ad-panel-link" href="{{ route('admin.order-commissions.index') }}">Manage commissions & payouts</a>@endif
    </section>
</div>
<p class="ad-finance-basis">Based on orders placed in the selected period, in {{ $financeCurrency }} only. Paid amounts are current recorded settlements for those orders, grouped by order date.</p>
