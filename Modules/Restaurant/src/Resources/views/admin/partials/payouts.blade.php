@php
    $payoutRows = $payoutData['rows'];
    $payoutFilters = $payoutData['filters'];
    $exportFilters = \Illuminate\Support\Arr::except($payoutFilters, ['payout_page', 'payout_per_page']);
    $payoutColumns = ['order' => 'Order number', 'date' => 'Calculated at', 'amount' => 'Order amount', 'refunded' => 'Refunded', 'rate' => 'Commission %', 'reversed' => 'Reversed', 'commission' => 'Net commission', 'payable' => 'Restaurant payable', 'paid' => 'Paid', 'currency' => 'Currency', 'status' => 'Status'];
    $sortColumn = $payoutFilters['payout_sort'] ?? 'date';
    $sortDirection = $payoutFilters['payout_direction'] ?? 'desc';
    $sortLink = static fn ($key) => route('admin.restaurant.view', array_merge(['id' => $restaurant->id], request()->except(['id', 'payout_page', 'format']), ['tab' => 'payouts', 'payout_sort' => $key, 'payout_direction' => $sortColumn === $key && $sortDirection === 'asc' ? 'desc' : 'asc'])) . '#restaurant-payouts';
@endphp
<section id="restaurant-payouts" class="restaurant-payouts" aria-labelledby="restaurant-payouts-heading">
    <header class="rp-heading">
        <div class="rp-heading-title"><span class="rp-workbook-icon" aria-hidden="true"><i class="fas fa-file-excel"></i></span><div><h2 id="restaurant-payouts-heading">Payouts & commission</h2><p>Order commission records for {{ $restaurant->name }}.</p></div></div>
        @if($payoutRows->total() > 0)
        <div class="rp-downloads">
            <a href="{{ route('admin.restaurant.payouts.export', array_merge(['id' => $restaurant->id], $exportFilters, ['format' => 'csv'])) }}" class="rp-button rp-button-secondary"><i class="fas fa-download" aria-hidden="true"></i> CSV</a>
            <a href="{{ route('admin.restaurant.payouts.export', array_merge(['id' => $restaurant->id], $exportFilters, ['format' => 'xlsx'])) }}" class="rp-button rp-button-excel"><i class="fas fa-file-excel" aria-hidden="true"></i> Download Excel</a>
        </div>
        @endif
    </header>
    @if($errors->any())<div class="rp-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="rp-filters" method="GET" action="{{ route('admin.restaurant.view', $restaurant->id) }}#restaurant-payouts">
        <input type="hidden" name="tab" value="payouts">
        <input type="hidden" name="payout_per_page" value="{{ $payoutRows->perPage() }}">
        <input type="hidden" name="payout_sort" value="{{ $sortColumn }}">
        <input type="hidden" name="payout_direction" value="{{ $sortDirection }}">
        @foreach(['date_from', 'date_to'] as $analyticsFilter)
            @if(is_string(request($analyticsFilter)) && request($analyticsFilter) !== '')<input type="hidden" name="{{ $analyticsFilter }}" value="{{ request($analyticsFilter) }}">@endif
        @endforeach
        <div class="rp-search-field"><label for="payout-search">Order number</label><input id="payout-search" name="payout_search" value="{{ $payoutFilters['payout_search'] ?? '' }}" maxlength="100" placeholder="Search orders…"></div>
        <div><label for="payout-status">Payout status</label><select id="payout-status" name="payout_status"><option value="">All statuses</option>@foreach(\Modules\Orders\Models\OrderCommission::STATUSES as $payoutStatus)<option value="{{ $payoutStatus }}" @selected(($payoutFilters['payout_status'] ?? '') === $payoutStatus)>{{ ucfirst(str_replace('_', ' ', $payoutStatus)) }}</option>@endforeach</select></div>
        <div><label for="payout-from">Calculated from</label><input id="payout-from" type="date" name="payout_from" value="{{ $payoutFilters['payout_from'] ?? '' }}"></div>
        <div><label for="payout-to">Calculated to</label><input id="payout-to" type="date" name="payout_to" value="{{ $payoutFilters['payout_to'] ?? '' }}"></div>
        <div class="rp-currency-field"><label for="payout-currency">Currency</label><input id="payout-currency" name="payout_currency" value="{{ $payoutFilters['payout_currency'] ?? '' }}" maxlength="3" pattern="[A-Za-z]{3}" placeholder="All"></div>
        <div class="rp-filter-actions"><button type="submit" class="rp-button rp-button-primary">Apply filters</button><a href="{{ route('admin.restaurant.view', ['id' => $restaurant->id, 'tab' => 'payouts']) }}#restaurant-payouts" class="rp-reset">Reset</a></div>
    </form>
    <div class="rp-ledger-toolbar"><span><i class="fas fa-table" aria-hidden="true"></i> Commission ledger</span><span>{{ number_format($payoutRows->total()) }} {{ $payoutRows->total() === 1 ? 'record' : 'records' }} <span aria-hidden="true">·</span> Read-only <span aria-hidden="true">·</span> Amounts in row currency</span></div>
    <div class="rp-sheet-scroll" tabindex="0" role="region" aria-label="Scrollable restaurant payout ledger">
        <table class="rp-sheet">
            <caption class="rp-sr-only">Restaurant payout and commission records. Column headings can be selected to sort. Downloads include all records matching the filters.</caption>
            <thead>
                <tr class="rp-column-letters" aria-hidden="true"><th class="rp-row-number"></th>@foreach(array_keys($payoutColumns) as $payoutKey)<th class="{{ $loop->first ? 'rp-frozen-order' : '' }}">{{ chr(65 + $loop->index) }}</th>@endforeach</tr>
                <tr class="rp-column-headings"><th scope="col" class="rp-row-number">#</th>@foreach($payoutColumns as $payoutKey => $payoutLabel)<th scope="col" class="{{ $loop->first ? 'rp-frozen-order' : '' }} {{ in_array($payoutKey, ['order', 'date', 'currency', 'status'], true) ? '' : 'rp-number' }}" aria-sort="{{ $sortColumn === $payoutKey ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ $sortLink($payoutKey) }}">{{ $payoutLabel }} @if($sortColumn === $payoutKey)<i class="fas fa-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" aria-hidden="true"></i>@endif</a></th>@endforeach</tr>
            </thead>
            <tbody>
                @forelse($payoutRows as $payout)
                    <tr>
                        <th scope="row" class="rp-row-number">{{ $payoutRows->firstItem() + $loop->index }}</th>
                        <td class="rp-frozen-order"><a href="{{ route('admin.orders.show', $payout->order_id) }}" class="rp-order-link">{{ $payout->order_number }}</a></td>
                        <td class="rp-date">{{ $payout->calculated_at?->format('d M Y') }}<span>{{ $payout->calculated_at?->format('H:i') }}</span></td>
                        <td class="rp-number">{{ fn_format_currency_amount((float) $payout->order_amount, $payout->currency) }}</td>
                        <td class="rp-number {{ (float) $payout->refunded_amount > 0 ? 'rp-refund-value' : '' }}">{{ fn_format_currency_amount((float) $payout->refunded_amount, $payout->currency) }}</td>
                        <td class="rp-number rp-rate">{{ $payout->commission_percentage }}%</td>
                        <td class="rp-number">{{ fn_format_currency_amount((float) $payout->commission_reversed_amount, $payout->currency) }}</td>
                        <td class="rp-number rp-key-value">{{ fn_format_currency_amount((float) $payout->effective_commission, $payout->currency) }}</td>
                        <td class="rp-number rp-key-value">{{ fn_format_currency_amount((float) $payout->final_payable_amount, $payout->currency) }}</td>
                        <td class="rp-number">{{ fn_format_currency_amount((float) $payout->paid_amount, $payout->currency) }}</td>
                        <td><span class="rp-currency">{{ fn_get_currency_symbol($payout->currency) ?? $payout->currency }}</span></td>
                        <td><a href="{{ route('admin.order-commissions.show', $payout->id) }}" class="rp-status" data-status="{{ $payout->status }}" title="View payout for {{ $payout->order_number }}">{{ ucfirst(str_replace('_', ' ', $payout->status)) }}</a></td>
                    </tr>
                @empty<tr><td colspan="12" class="rp-empty"><i class="fas fa-table" aria-hidden="true"></i><strong>No payout records found</strong><span>Try another status or date range. Commissions appear after an eligible order is paid.</span></td></tr>@endforelse
            </tbody>
            @if($payoutData['totals']->isNotEmpty())
                <tfoot>
                    @foreach($payoutData['totals'] as $payoutTotal)
                        <tr>
                            <td class="rp-row-number" aria-hidden="true">Σ</td>
                            <th scope="row" class="rp-frozen-order">Filtered totals · {{ fn_get_currency_symbol($payoutTotal->currency) ?? $payoutTotal->currency }}</th>
                            <td class="rp-date">{{ number_format($payoutTotal->record_count) }} orders</td>
                            <td class="rp-number">{{ fn_format_currency_amount((float) $payoutTotal->order_amount, $payoutTotal->currency) }}</td>
                            <td class="rp-number">{{ fn_format_currency_amount((float) $payoutTotal->refunded_amount, $payoutTotal->currency) }}</td>
                            <td class="rp-number" aria-label="Commission percentage is not summed">—</td>
                            <td class="rp-number">{{ fn_format_currency_amount((float) $payoutTotal->reversed_amount, $payoutTotal->currency) }}</td>
                            <td class="rp-number rp-key-value">{{ fn_format_currency_amount((float) $payoutTotal->net_commission, $payoutTotal->currency) }}</td>
                            <td class="rp-number rp-key-value">{{ fn_format_currency_amount((float) $payoutTotal->payable_amount, $payoutTotal->currency) }}</td>
                            <td class="rp-number">{{ fn_format_currency_amount((float) $payoutTotal->paid_amount, $payoutTotal->currency) }}</td>
                            <td><span class="rp-currency">{{ fn_get_currency_symbol($payoutTotal->currency) ?? $payoutTotal->currency }}</span></td>
                            <td>All matching rows</td>
                        </tr>
                    @endforeach
                </tfoot>
            @endif
        </table>
    </div>
    <div class="rp-sheet-footer"><span class="rp-sheet-tab"><i class="fas fa-table" aria-hidden="true"></i> Payouts</span><span>Downloads include all {{ number_format($payoutRows->total()) }} matching records.</span></div>
    <footer class="rp-pagination">
        <div class="rp-pagination-left">
            <form method="GET" action="{{ route('admin.restaurant.view', $restaurant->id) }}#restaurant-payouts" class="rp-page-size">
                <input type="hidden" name="tab" value="payouts">
                @foreach(\Illuminate\Support\Arr::except($payoutFilters, ['payout_page', 'payout_per_page']) as $filterName => $filterValue)
                    @if($filterValue !== null && $filterValue !== '')<input type="hidden" name="{{ $filterName }}" value="{{ $filterValue }}">@endif
                @endforeach
                @foreach(['date_from', 'date_to'] as $analyticsFilter)
                    @if(is_string(request($analyticsFilter)) && request($analyticsFilter) !== '')<input type="hidden" name="{{ $analyticsFilter }}" value="{{ request($analyticsFilter) }}">@endif
                @endforeach
                <label for="payout-per-page">Rows per page</label>
                <select id="payout-per-page" name="payout_per_page" onchange="this.form.submit()" aria-label="Rows per page">@foreach([5, 10, 25, 50] as $pageSize)<option value="{{ $pageSize }}" @selected($payoutRows->perPage() === $pageSize)>{{ $pageSize }}</option>@endforeach</select>
            </form>
            <span>Showing {{ $payoutRows->firstItem() ?? 0 }}–{{ $payoutRows->lastItem() ?? 0 }} of {{ number_format($payoutRows->total()) }}</span>
        </div>
        <nav class="rp-pager-controls" aria-label="Payouts pagination">
            <span class="rp-page-count">Page {{ $payoutRows->currentPage() }} of {{ $payoutRows->lastPage() }}</span>
            @if($payoutRows->onFirstPage())
                <button type="button" class="rp-pager-button" disabled aria-label="Previous page"><i class="fas fa-chevron-left" aria-hidden="true"></i><span class="rp-nav-label">Previous</span></button>
            @else
                <a href="{{ $payoutRows->previousPageUrl() }}" class="rp-pager-button" rel="prev" aria-label="Previous page"><i class="fas fa-chevron-left" aria-hidden="true"></i><span class="rp-nav-label">Previous</span></a>
            @endif
            @php
                $pageNumbers = collect([1, $payoutRows->lastPage(), ...range(min($payoutRows->lastPage(), max(1, $payoutRows->currentPage() - 1)), min($payoutRows->lastPage(), $payoutRows->currentPage() + 1))])->unique()->sort()->values();
                $previousPageNumber = 0;
            @endphp
            @foreach($pageNumbers as $pageNumber)
                @if($previousPageNumber && $pageNumber > $previousPageNumber + 1)<span class="rp-page-gap" aria-hidden="true">…</span>@endif
                @if($pageNumber === $payoutRows->currentPage())
                    <span class="rp-pager-button rp-current-page" aria-current="page" aria-label="Page {{ $pageNumber }}">{{ $pageNumber }}</span>
                @else
                    <a class="rp-pager-button" href="{{ $payoutRows->url($pageNumber) }}" aria-label="Go to page {{ $pageNumber }}">{{ $pageNumber }}</a>
                @endif
                @php $previousPageNumber = $pageNumber; @endphp
            @endforeach
            @if($payoutRows->hasMorePages())
                <a href="{{ $payoutRows->nextPageUrl() }}" class="rp-pager-button" rel="next" aria-label="Next page"><span class="rp-nav-label">Next</span><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
            @else
                <button type="button" class="rp-pager-button" disabled aria-label="Next page"><span class="rp-nav-label">Next</span><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
            @endif
        </nav>
    </footer>
    @if($payoutData['totals']->isNotEmpty())
        <div class="rp-totals">
            <h3>Filtered totals <span>All matching rows across every page, grouped by currency</span></h3>
            @foreach($payoutData['totals'] as $payoutTotal)
                <div class="rp-total-group">
                    <span class="rp-total-currency">{{ fn_get_currency_symbol($payoutTotal->currency) ?? $payoutTotal->currency }}</span>
                    <div class="rp-total-row">
                        <div><span>Total orders</span><strong tabindex="0">{{ number_format($payoutTotal->record_count) }}</strong></div>
                        @foreach(['order_amount' => 'Total order amount', 'refunded_amount' => 'Refunded', 'reversed_amount' => 'Commission reversed', 'net_commission' => 'Net commission', 'payable_amount' => 'Restaurant payable', 'paid_amount' => 'Paid'] as $totalField => $totalLabel)
                            <div><span>{{ $totalLabel }}</span><strong tabindex="0" title="{{ fn_format_currency_amount((float) $payoutTotal->{$totalField}, $payoutTotal->currency) }}">{{ fn_format_currency_amount((float) $payoutTotal->{$totalField}, $payoutTotal->currency) }}</strong></div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</section>
@push('styles')
<style>
.restaurant-payouts { min-width:0;max-width:100%;border:1px solid #dfe6ec;border-radius:13px;background:#fff;overflow:hidden;color:#171717;scroll-margin-top:96px; }
.restaurant-payouts * { box-sizing:border-box; }
.restaurant-payouts .rp-heading { display:flex;align-items:center;justify-content:space-between;gap:20px;padding:22px 24px;border-bottom:1px solid #edf0f4; }
.restaurant-payouts .rp-heading-title { display:flex;align-items:center;gap:12px;min-width:0; }
.restaurant-payouts h2 { font-size:17px;font-weight:650;margin:0 0 4px;letter-spacing:-.02em; }
.restaurant-payouts .rp-heading p { color:#171717;font-size:12px;line-height:1.7;margin:0; }
.restaurant-payouts .rp-workbook-icon { display:flex;align-items:center;justify-content:center;flex:none;width:40px;height:40px;border-radius:9px;background:#fff3e8;border:1px solid #ffdbbd;color:#171717;font-size:19px; }
.restaurant-payouts .rp-downloads { display:flex;align-items:center;gap:10px;flex:none; }
.restaurant-payouts .rp-button { display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:8px;padding:10px 14px;min-height:40px;font-size:12px;font-weight:600;line-height:1.5;text-decoration:none;cursor:pointer;border:1px solid transparent;white-space:nowrap; }
.restaurant-payouts .rp-button-secondary { background:#fff7ef;border-color:#ffdbbd;color:#171717; }
.restaurant-payouts .rp-button-excel { background:#ffe4cc;border-color:#ffdbbd;color:#171717; }
.restaurant-payouts :is(.rp-button-excel,.rp-button-secondary,.rp-button-primary):hover { background:#ffd9ba; }
.restaurant-payouts .rp-button-primary { background:#ffe4cc;color:#171717; }
.restaurant-payouts .rp-filters { display:flex;align-items:flex-end;flex-wrap:wrap;gap:14px;padding:20px 24px; }
.restaurant-payouts .rp-filters > div { flex:1 1 130px;min-width:0; }
.restaurant-payouts .rp-filters .rp-search-field { flex:1.5 1 180px; }
.restaurant-payouts .rp-filters .rp-currency-field { flex:0 1 88px; }
.restaurant-payouts label { display:block;font-size:11px;font-weight:600;color:#171717;margin-bottom:7px; }
.restaurant-payouts input,.restaurant-payouts select { width:100%;min-width:0;height:40px;border:1px solid #dce3eb;border-radius:7px;background:#fff;color:#171717;padding:9px 11px;font:inherit;font-size:12px; }
.restaurant-payouts .rp-filter-actions { display:flex;align-items:center;gap:12px;flex:0 0 auto; }
.restaurant-payouts .rp-reset { color:#171717;font-size:11px;text-decoration:none; }
.restaurant-payouts .rp-reset:hover { color:#171717; }
.restaurant-payouts .rp-ledger-toolbar { display:flex;justify-content:space-between;align-items:center;gap:12px;border-top:1px solid #e4e9ed;border-bottom:1px solid #dde4e8;background:#f7f9fa;padding:11px 18px;font-size:11px;color:#171717; }
.restaurant-payouts .rp-ledger-toolbar > span:first-child { display:flex;align-items:center;gap:8px;color:#171717;font-weight:600; }
.restaurant-payouts .rp-ledger-toolbar i { color:#171717; }
.restaurant-payouts .rp-sheet-scroll { position:relative;overflow:auto;height:360px;max-height:50dvh;min-height:240px;max-width:100%;scrollbar-gutter:stable;scrollbar-width:thin;scrollbar-color:#e4b58f #fff5eb; }
.restaurant-payouts .rp-sheet-scroll::-webkit-scrollbar { width:9px;height:9px; }
.restaurant-payouts .rp-sheet-scroll::-webkit-scrollbar-track { background:#fff5eb; }
.restaurant-payouts .rp-sheet-scroll::-webkit-scrollbar-thumb { background:#e4b58f;border:2px solid #fff5eb;border-radius:8px; }
.restaurant-payouts .rp-sheet { width:100%;min-width:1560px;border-collapse:separate;border-spacing:0;font-size:12px; }
.restaurant-payouts .rp-sheet th,.restaurant-payouts .rp-sheet td { border-right:1px solid #e3e8eb;border-bottom:1px solid #e3e8eb;padding:12px;vertical-align:middle;background:#fff;white-space:nowrap; }
.restaurant-payouts .rp-sheet tr > :last-child { border-right:0; }
.restaurant-payouts .rp-sheet .rp-row-number { position:sticky;left:0;width:42px;min-width:42px;max-width:42px;padding:0 10px;text-align:center;background:#f5f7f8;color:#171717;font-size:10px;font-weight:400;z-index:2; }
.restaurant-payouts .rp-column-letters th { position:sticky;top:0;text-align:center;font-size:10px;font-weight:500;background:#fff0e2;color:#171717;padding:5px 10px;height:26px;z-index:4; }
.restaurant-payouts .rp-column-headings th { position:sticky;top:26px;background:#fff7ef;color:#171717;font-size:10px;font-weight:650;height:42px;z-index:4; }
.restaurant-payouts .rp-column-headings th a { display:inline-flex;align-items:center;gap:7px;color:inherit;text-decoration:none; }
.restaurant-payouts .rp-column-headings th i { font-size:9px;color:#171717; }
.restaurant-payouts .rp-sheet .rp-frozen-order { position:sticky;left:42px;z-index:2;min-width:190px;box-shadow:2px 0 3px #34463b05; }
.restaurant-payouts .rp-column-headings .rp-frozen-order,.restaurant-payouts .rp-column-letters .rp-frozen-order,.restaurant-payouts .rp-sheet thead .rp-row-number { z-index:5; }
.restaurant-payouts .rp-sheet tbody tr:nth-child(even) td { background:#fffcf8; }
.restaurant-payouts .rp-sheet tbody tr:hover :is(td,th) { background:#ffecd9; }
.restaurant-payouts .rp-sheet tfoot { position:sticky;bottom:0;z-index:3;box-shadow:0 -2px 5px #34463b0d; }
.restaurant-payouts .rp-sheet tfoot :is(th,td) { background:#ffeddb;font-weight:650;border-top:2px solid #f5c9a5;color:#171717; }
.restaurant-payouts .rp-sheet tfoot .rp-frozen-order { font-size:11px;text-align:left; }
.restaurant-payouts .rp-sheet :is(td,th).rp-number { min-width:120px; }
.restaurant-payouts .rp-order-link { color:#171717;font-size:11px;font-weight:600;text-decoration:none; }
.restaurant_payouts .rp-order-link:hover { text-decoration:underline;text-underline-offset:3px; }
.restaurant-payouts .rp-number { text-align:right;font-variant-numeric:tabular-nums; }
.restaurant-payouts .rp-key-value { font-weight:600;color:#171717; }
.restaurant-payouts .rp-refund-value { color:#171717; }
.restaurant-payouts .rp-rate { color:#171717; }
.restaurant-payouts .rp-date { color:#171717;font-size:11px; }
.restaurant-payouts .rp-date > span { display:block;font-size:10px;color:#171717;margin-top:3px; }
.restaurant-payouts .rp-currency { font-size:10px;font-weight:600;color:#171717; }
.restaurant-payouts .rp-status { display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:5px;font-size:10px;font-weight:600;text-decoration:none;color:#171717;background:#fff6e9; }
.restaurant-payouts .rp-status::before { content:'';width:4px;height:4px;border-radius:50%;background:currentColor; }
.restaurant-payouts .rp-status[data-status=processed] { background:#fff0e2;color:#171717; }
.restaurant-payouts .rp-status[data-status=cancelled] { background:#edf1f4;color:#171717; }
.restaurant-payouts .rp-status[data-status=failed] { background:#fff0ef;color:#171717; }
.restaurant-payouts .rp-status[data-status=on_hold] { background:#fff8de;color:#171717; }
.restaurant-payouts .rp-empty { text-align:center;padding:48px 20px !important;white-space:normal !important; }
.restaurant-payouts .rp-empty > * { display:block; }
.restaurant-payouts .rp-empty > i { font-size:26px;color:#171717;margin-bottom:14px; }
.restaurant-payouts .rp-empty strong { font-size:14px;font-weight:600;color:#171717; }
.restaurant-payouts .rp-empty span { font-size:12px;color:#171717;margin-top:8px; }
.restaurant-payouts .rp-sheet-footer { display:flex;align-items:center;justify-content:space-between;gap:16px;background:#fff9f3;border-bottom:1px solid #e4e9ec;padding:0 18px;font-size:10px;color:#171717; }
.restaurant-payouts .rp-sheet-tab { display:flex;align-items:center;gap:7px;border-bottom:3px solid #ed9c5e;padding:10px 13px;background:white;color:#171717;font-weight:600; }
.restaurant-payouts .rp-totals { padding:20px 24px;border-bottom:1px solid #edf0f4; }
.restaurant_payouts .rp-totals h3 { font-size:12px;font-weight:650;margin:0 0 16px;color:#171717; }
.restaurant_payouts .rp-totals h3 > span { font-size:10px;font-weight:400;color:#171717;margin-left:7px; }
.restaurant-payouts .rp-total-group + .rp-total-group { border-top:1px solid #edf0f4;margin-top:18px;padding-top:18px; }
.restaurant-payouts .rp-total-row { display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:12px;margin-top:12px; }
.restaurant-payouts .rp-total-row > div { min-width:0;border:1px solid #f1dfd0;border-radius:8px;background:#fffcf8;padding:12px 14px; }
.restaurant-payouts .rp-total-currency { display:inline-block;padding:5px 8px;border:1px solid #f1dfd0;border-radius:5px;font-size:11px;font-weight:600;color:#171717;justify-self:start; }
.restaurant-payouts .rp-total-row > div > span { display:block;font-size:10px;color:#171717;margin-bottom:4px; }
.restaurant-payouts .rp-total-row strong { display:block;max-width:100%;overflow-x:auto;white-space:nowrap;font-size:clamp(15px,1.2vw,18px);font-weight:650;color:#171717;font-variant-numeric:tabular-nums;scrollbar-width:thin;padding-bottom:3px; }
.restaurant-payouts .rp-total-row strong:focus-visible { outline:2px solid #f3a563;outline-offset:3px; }
.restaurant-payouts .rp-pagination { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;padding:16px 20px;font-size:11px;color:#171717;border-bottom:1px solid #edf0f4;background:#fff; }
.restaurant-payouts .rp-pagination-left { display:flex;align-items:center;flex-wrap:wrap;gap:16px; }
.restaurant-payouts .rp-page-size { display:flex;align-items:center;gap:8px; }
.restaurant-payouts .rp-page-size label { margin:0;font-size:11px;font-weight:400;color:#171717;white-space:nowrap; }
.restaurant_payouts .rp-page-size select { width:65px;min-width:65px;height:32px;padding:4px 8px;font-size:11px; }
.restaurant-payouts .rp-pager-controls { display:flex;align-items:center;flex-wrap:wrap;gap:5px; }
.restaurant-payouts .rp-page-count { margin-right:7px;font-size:10px;color:#171717; }
.restaurant-payouts .rp-pager-button { display:inline-flex;align-items:center;justify-content:center;gap:6px;min-width:31px;min-height:32px;padding:5px 9px;border:1px solid #efddce;border-radius:6px;background:#fff;color:#171717;font-size:11px;font-weight:500;line-height:1.4;text-decoration:none;cursor:pointer; }
.restaurant-payouts .rp-pager-button i { font-size:9px; }
.restaurant_payouts a.rp-pager-button:hover { background:#fff3e8;border-color:#f5c9a5; }
.restaurant-payouts .rp-current-page { background:#ffcea6;border-color:#ffcea6;color:#171717;cursor:default; }
.restaurant-payouts .rp-pager-button:disabled { color:#9ca3af;background:#f9fbfa;cursor:not-allowed;border-color:#edf0ef; }
.restaurant-payouts .rp-page-gap { padding:0 3px;color:#171717; }
.restaurant-payouts .rp-errors { color:#171717;background:#fff7ed;padding:14px 24px;font-size:12px; }
.restaurant_payouts .rp-errors ul { list-style:disc;padding-left:16px; }
.restaurant-payouts :is(a,button,input,select):focus-visible,.restaurant-payouts .rp-sheet-scroll:focus-visible { outline:2px solid #f3a563;outline-offset:3px; }
.restaurant-payouts .rp-sr-only { position:absolute;top:0;left:0;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0; }
@media(max-width:760px) { .restaurant-payouts .rp-heading { align-items:flex-start;flex-direction:column;padding:20px; } .restaurant-payouts .rp-downloads { width:100%; } .restaurant-payouts .rp-downloads a { flex:1; } .restaurant-payouts .rp-filters { padding:18px 20px;gap:14px; } .restaurant-payouts .rp-filters > div,.restaurant-payouts .rp-filters .rp-currency-field { flex:1 1 130px; } .restaurant-payouts .rp-filters .rp-search-field { flex-basis:100%; } .restaurant-payouts .rp-filter-actions { flex-basis:100%; } .restaurant-payouts .rp-ledger-toolbar { flex-direction:column;align-items:flex-start; } .restaurant-payouts .rp-sheet-footer { align-items:flex-start;gap:8px;font-size:9px; } .restaurant-payouts .rp-sheet { min-width:1560px; } .restaurant-payouts .rp-totals { padding:18px 20px; } .restaurant-payouts .rp-total-currency { grid-column:1 / -1; } .restaurant_payouts .rp-totals h3 > span { display:block;margin:5px 0 0; } .restaurant-payouts .rp-pagination { flex-direction:column;align-items:flex-start;padding:18px 20px; } }
@media(max-width:480px) { .restaurant-payouts .rp-pager-button .rp-nav-label { display:none; } .restaurant-payouts .rp-page-count { margin-right:4px; } .restaurant-payouts .rp-pagination-left { gap:10px; } }
</style>
@endpush
