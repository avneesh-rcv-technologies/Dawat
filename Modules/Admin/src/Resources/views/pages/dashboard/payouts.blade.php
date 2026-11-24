@php
    $financeFormat = fn ($amount) => fn_format_currency_amount($amount, $finance['currency'], true);
    $financeShort = fn ($amount) => \Modules\Admin\Support\DashboardNumber::currency($amount, $finance['currency']);
@endphp
<section class="ad-panel ad-payout-records" id="dashboard-payouts"><div class="ad-panel-header"><div><p class="ad-eyebrow">COMMISSION REGISTER</p><h2>Recent commission & payout records</h2><p>Selected order period · {{ $finance['currency'] }} · current settlement values</p></div>@if($can('admin.order-commissions.index'))<a class="ad-button ad-button-secondary" href="{{ route('admin.order-commissions.index') }}">View all records</a>@endif</div>
    <div class="ad-table-scroll"><table><thead><tr><th>Order / restaurant</th><th>Net commission</th><th>Restaurant payable</th><th>Paid amount</th><th>Settlement status</th><th>Recorded payout date</th><th><span class="ad-sr-only">View settlement</span></th></tr></thead><tbody>
        @forelse($finance['recent'] as $commission)
            <tr><td><strong>{{ $commission->order?->order_number ?? 'Order unavailable' }}</strong><span class="ad-table-note">{{ $commission->restaurant?->name ?? 'Restaurant unavailable' }}</span></td>
                @foreach([$commission->effective_commission, $commission->final_payable_amount, $commission->paid_amount] as $amount)<td class="ad-money" title="{{ $financeFormat($amount) }}">{{ $financeShort($amount) }}</td>@endforeach
                <td><span class="ad-badge {{ ['processed'=>'is-green','pending'=>'is-neutral','on_hold'=>'is-amber','cancelled'=>'is-red','failed'=>'is-red'][$commission->status] ?? 'is-neutral' }}">{{ ucwords(str_replace('_',' ',$commission->status)) }}</span></td><td>{{ $commission->processed_at?->timezone($filters->timezone)->format('M j, Y') ?? 'Not recorded' }}</td><td>@if($can('admin.order-commissions.show'))<a class="ad-order-link" href="{{ route('admin.order-commissions.show',$commission->id) }}" aria-label="View commission for {{ $commission->order?->order_number }}">@include('admin::pages.dashboard.icon',['name'=>'arrow'])</a>@endif</td></tr>
        @empty<tr><td colspan="7"><div class="ad-empty"><strong>No commission records in this period</strong><p>Try another date range, fulfillment type, or currency.</p></div></td></tr>@endforelse
    </tbody></table></div>
</section>
