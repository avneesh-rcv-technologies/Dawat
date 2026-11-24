@extends('backend::layouts.app')
@section('title', 'Commission — ' . $commission->order->order_number)
@section('breadcrumbs')
    <x-breadcrumbs :breadcrumbs="[['label' => 'Payouts', 'url' => route('admin.order-commissions.index')], ['label' => $commission->order->order_number, 'url' => null]]" :name="'Commission — ' . $commission->order->order_number" />
@endsection
@php
    $settlement = $commission->settlementData();
    $statusLabel = static fn ($status) => ucfirst(str_replace('_', ' ', $status));
    $currencySymbol = fn_get_currency_symbol($commission->currency) ?? $commission->currency;
    $money = static fn ($amount) => fn_format_currency_amount((float) $amount, $commission->currency);
@endphp
@push('styles')
<style>
.commission-page { max-width:1440px;min-width:0;margin:auto;color:#253249; }
.commission-page * { box-sizing:border-box; }
.commission-page .commission-card { background:#fff;border:1px solid #e2e8ee;border-radius:14px;padding:24px;margin-bottom:22px;min-width:0; }
.commission-page .commission-layout { display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:22px;align-items:start; }
.commission-page .commission-layout > * { min-width:0; }
.commission-page h2 { font-size:17px;font-weight:700;margin:0 0 8px; }
.commission-page .commission-muted { font-size:12px;line-height:1.7;color:#7b8798;margin:0; }
.commission-page .commission-heading { display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:22px; }
.commission-page .commission-heading h1 { font-size:23px;line-height:1.4;font-weight:700;letter-spacing:-.025em;margin:6px 0;overflow-wrap:anywhere; }
.commission-page .commission-back { display:inline-flex;gap:7px;align-items:center;font-size:12px;color:#68788b;text-decoration:none; }
.commission-page .commission-summary { display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:22px; }
.commission-page .commission-summary > div { min-width:0;border:1px solid #e2e8ee;border-radius:12px;padding:20px;background:#fff; }
.commission-page .commission-summary > .commission-highlight { background:#f3faf5;border-color:#cee6d5; }
.commission-page .commission-summary strong { display:block;font-size:clamp(20px,1.7vw,27px);margin:10px 0 5px;font-variant-numeric:tabular-nums;overflow-x:auto;white-space:nowrap; }
.commission-page .commission-summary small { display:block;font-size:11px;color:#8995a4; }
.commission-page .commission-section-heading { padding-bottom:18px;border-bottom:1px solid #edf0f4;margin-bottom:8px; }
.commission-page dl { margin:0; }
.commission-page dl > div { display:flex;justify-content:space-between;gap:20px;padding:13px 0;border-bottom:1px solid #edf0f4;font-size:13px; }
.commission-page dl > div:last-child { border-bottom:0; }
.commission-page dt { color:#7b8798; }
.commission-page dd { text-align:right;margin:0;max-width:65%;overflow-wrap:anywhere;font-variant-numeric:tabular-nums; }
.commission-page .commission-badge { display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:6px;background:#f3f4f6;font-size:11px;font-weight:600;white-space:nowrap; }
.commission-page .commission-badge::before { content:'';width:5px;height:5px;border-radius:50%;background:currentColor; }
.commission-page .commission-badge[data-status=processed] { background:#ecfdf5;color:#047857; }
.commission-page .commission-badge[data-status=cancelled] { background:#edf1f4;color:#64748b; }
.commission-page .commission-badge[data-status=failed] { background:#fef2f2;color:#b91c1c; }
.commission-page .commission-badge[data-status=on_hold] { background:#fff8de;color:#92752f; }
.commission-page .commission-badge[data-status=pending] { background:#fff7ed;color:#c2410c; }
.commission-page label { display:block;font-size:12px;font-weight:600;margin:18px 0 7px; }
.commission-page select,.commission-page textarea { width:100%;min-width:0;border:1px solid #dce3eb;border-radius:8px;padding:11px 12px;background:#fff;font:inherit;font-size:13px;color:#354158; }
.commission-page textarea { resize:vertical; }
.commission-page .commission-button { display:flex;align-items:center;justify-content:center;gap:8px;width:100%;border:0;border-radius:8px;padding:12px 18px;background:#ff6210;color:#fff;font-weight:600;font-size:13px;margin-top:20px;cursor:pointer; }
.commission-page .commission-button:hover { background:#e95305; }
.commission-page .commission-link { color:#c2410c;text-decoration:none;font-weight:600;overflow-wrap:anywhere; }
.commission-page .commission-link:hover { text-decoration:underline;text-underline-offset:3px; }
.commission-page .commission-alert { padding:14px 18px;border:1px solid #fed7aa;border-radius:10px;background:#fff7ed;color:#9a3412;margin-bottom:18px;font-size:13px; }
.commission-page .commission-financials { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:18px; }
.commission-page .commission-financials > div { display:block;min-width:0;background:#f8fafb;border:1px solid #e9eef2;border-radius:9px;padding:15px; }
.commission-page .commission-financials dt { font-size:11px;margin-bottom:8px; }
.commission-page .commission-financials dd { max-width:100%;text-align:left;white-space:nowrap;overflow-x:auto;font-size:16px;font-weight:650;color:#354b3d; }
.commission-page .commission-financials .commission-payable { background:#f0f8f2;border-color:#d7e9db; }
.commission-page .commission-explanation { padding:13px 15px;margin-top:16px;background:#f8fafb;border-radius:8px;font-size:11px;line-height:1.8;color:#7b8798; }
.commission-page .commission-balance { padding:16px;background:#f3faf5;border:1px solid #dcece0;border-radius:10px;margin:18px 0; }
.commission-page .commission-balance span { font-size:11px;color:#657b6c; }
.commission-page .commission-balance strong { display:block;font-size:22px;margin-top:7px;font-variant-numeric:tabular-nums;white-space:nowrap;overflow-x:auto;color:#315c47; }
.commission-page .commission-history { list-style:none;padding:0;margin:20px 0 0; }
.commission-page .commission-event { position:relative;padding:0 0 25px 27px;border-left:1px solid #e0e7e3;margin-left:6px; }
.commission-page .commission-event:last-child { border-left-color:transparent;padding-bottom:0; }
.commission-page .commission-event::before { content:'';position:absolute;left:-5px;top:6px;width:9px;height:9px;border-radius:50%;background:#94b29f;box-shadow:0 0 0 4px #fff; }
.commission-page .commission-event:first-child::before { background:#217346; }
.commission-page .commission-event-header { display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap; }
.commission-page .commission-transition { display:flex;gap:8px;align-items:center;flex-wrap:wrap; }
.commission-page .commission-transition > span:not(.commission-badge) { color:#8995a4;font-size:11px; }
.commission-page .commission-event time { font-size:11px;color:#8a96a5;white-space:nowrap; }
.commission-page .commission-event-actor { margin:8px 0;font-size:11px;color:#7b8798; }
.commission-page .commission-event-notes { margin:8px 0 0;font-size:12px;color:#59687a;line-height:1.8;overflow-wrap:anywhere;white-space:pre-line; }
.commission-page .commission-refund-link { display:inline-flex;align-items:center;gap:7px;font-size:11px;padding:6px 9px;border-radius:6px;background:#fff7ed;margin-top:4px;max-width:100%; }
.commission-page .commission-refund-link span { min-width:0;overflow-wrap:anywhere; }
.commission-page .commission-empty { padding:20px 0;color:#8995a4;font-size:13px; }
.commission-page :is(a,button,input,select,textarea,[tabindex]):focus-visible { outline:2px solid #83b394;outline-offset:3px; }
@media(max-width:1000px) { .commission-page .commission-layout { grid-template-columns:minmax(0,1fr); } }
@media(max-width:640px) { .commission-page .commission-summary { grid-template-columns:minmax(0,1fr);gap:10px; } .commission-page .commission-card { padding:18px; } .commission-page .commission-financials { grid-template-columns:minmax(0,1fr); } .commission-page .commission-event-header { flex-direction:column; } .commission-page .commission-heading h1 { font-size:20px; } }
</style>
@endpush
@section('content')
<div class="commission-page">
    @if(session('success'))<div class="commission-alert" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="commission-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <header class="commission-heading">
        <div><a class="commission-back" href="{{ route('admin.order-commissions.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Payouts</a><h1>{{ $commission->order->order_number }}</h1><p class="commission-muted">{{ $commission->restaurant->name }} · Commission & settlement</p></div>
        <span class="commission-badge" data-status="{{ $commission->status }}">{{ $statusLabel($commission->status) }}</span>
    </header>
    <div class="commission-summary">
        <div><span class="commission-muted">Original order amount · {{ $currencySymbol }}</span><strong tabindex="0">{{ $money($commission->order_amount) }}</strong><small>Before successful refunds</small></div>
        <div><span class="commission-muted">Effective Dawat commission · {{ $currencySymbol }}</span><strong tabindex="0">{{ $money($settlement['effective_commission']) }}</strong><small>After commission reversals</small></div>
        <div class="commission-highlight"><span class="commission-muted">Restaurant payable · {{ $currencySymbol }}</span><strong tabindex="0">{{ $money($settlement['final_payable_amount']) }}</strong><small>Revised restaurant entitlement</small></div>
    </div>
    <div class="commission-layout">
        <div>
            <section class="commission-card" aria-labelledby="commission-original-heading">
                <div class="commission-section-heading"><h2 id="commission-original-heading">Original commission</h2><p class="commission-muted">The agreed rate and original amounts for this order.</p></div>
                <dl>
                    <div><dt>Order number</dt><dd><a class="commission-link" href="{{ route('admin.orders.show', $commission->order_id) }}">{{ $commission->order->order_number }}</a></dd></div>
                    <div><dt>Restaurant</dt><dd>{{ $commission->restaurant->name }}</dd></div>
                    <div><dt>Commission basis</dt><dd>Final order total</dd></div>
                    <div><dt>Accepted rate</dt><dd>{{ $commission->commission_percentage }}%</dd></div>
                    <div><dt>Original Dawat commission</dt><dd>{{ $money($commission->platform_commission_amount) }}</dd></div>
                    <div><dt>Original restaurant share</dt><dd>{{ $money($commission->restaurant_amount) }}</dd></div>
                    <div><dt>Agreement accepted</dt><dd>{{ !empty($commission->policy_snapshot['accepted_at']) ? \Carbon\Carbon::parse($commission->policy_snapshot['accepted_at'])->format('d M Y, H:i') : '—' }}</dd></div>
                    <div><dt>Calculated at</dt><dd>{{ $commission->calculated_at?->format('d M Y, H:i') ?? '—' }}</dd></div>
                    <div><dt>Processed at</dt><dd>{{ $commission->processed_at?->format('d M Y, H:i') ?? '—' }}</dd></div>
                    <div><dt>Last updated by</dt><dd>{{ $commission->processor?->name ?? 'System' }}</dd></div>
                </dl>
            </section>
            <section class="commission-card" aria-labelledby="commission-refunds-heading">
                <h2 id="commission-refunds-heading">Refunds & settlement</h2><p class="commission-muted">Current amounts in {{ $currencySymbol }}, based on successful refunds.</p>
                <dl class="commission-financials">
                    @foreach(['refunded_amount' => 'Successful refunds', 'commission_reversed_amount' => 'Commission reversed', 'effective_commission' => 'Effective Dawat commission', 'restaurant_refund_deduction' => 'Restaurant refund deduction', 'final_payable_amount' => 'Restaurant payable', 'paid_amount' => 'Recorded payout', 'recovery_due_amount' => 'Recovery due'] as $field => $label)
                        <div class="{{ $field === 'final_payable_amount' ? 'commission-payable' : '' }}"><dt>{{ $label }}</dt><dd tabindex="0">{{ $money($settlement[$field]) }}</dd></div>
                    @endforeach
                </dl>
                <p class="commission-explanation">Successful refunds reverse commission at the original {{ $commission->commission_percentage }}% rate. Original amounts are preserved. Recovery due is the amount to recover if the recorded payout exceeds the revised restaurant entitlement.</p>
            </section>
            <section class="commission-card" aria-labelledby="commission-history-heading">
                <h2 id="commission-history-heading">Status history</h2><p class="commission-muted">Latest activity first. Select a refund number to view its details.</p>
                <ol class="commission-history">
                    @forelse($historyEntries as $entry)
                        @php $history = $entry['history']; @endphp
                        <li class="commission-event">
                            <div class="commission-event-header">
                                <div class="commission-transition">
                                    @if($history->old_status)<span>{{ $statusLabel($history->old_status) }}</span><span aria-label="changed to">→</span>@endif
                                    <span class="commission-badge" data-status="{{ $history->new_status }}">{{ $statusLabel($history->new_status) }}</span>
                                </div>
                                <time datetime="{{ $history->created_at?->toIso8601String() }}">{{ $history->created_at?->format('d M Y, H:i') }}</time>
                            </div>
                            <p class="commission-event-actor">Updated by {{ $history->actor?->name ?? 'System' }}</p>
                            @if($entry['refund'])
                                <a class="commission-link commission-refund-link" href="{{ route('admin.refunds.show', $entry['refund']->id) }}"><i class="fas fa-undo" aria-hidden="true"></i><span>{{ $entry['refund']->refund_number }}</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                            @elseif($entry['refundUpdate'])<span class="commission-muted">Refund update</span>@endif
                            @if($entry['notes'] !== '')<p class="commission-event-notes">{{ $entry['notes'] }}</p>@endif
                        </li>
                    @empty<li class="commission-empty">No status changes recorded yet.</li>@endforelse
                </ol>
            </section>
        </div>
        <aside id="commission-status" class="commission-card" style="scroll-margin-top:90px" aria-labelledby="commission-status-heading">
            <h2 id="commission-status-heading">{{ $canUpdate ? 'Update payout status' : 'Payout summary' }}</h2>
            <p class="commission-muted">Current status <span class="commission-badge" data-status="{{ $commission->status }}">{{ $statusLabel($commission->status) }}</span></p>
            <div class="commission-balance"><span>Restaurant payable · {{ $currencySymbol }}</span><strong tabindex="0">{{ $money($settlement['final_payable_amount']) }}</strong></div>
            @if($canUpdate)
                <p class="commission-muted">Record the payout processing outcome. Status changes notify the admin and restaurant owner by email.</p>
                <form method="POST" action="{{ route('admin.order-commissions.update', $commission->id) }}" data-confirm="Save this commission status update?" data-confirm-button="Update status">
                    @csrf @method('PATCH')
                    <label for="commission-new-status">Payout status</label><select id="commission-new-status" name="status" required>@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $commission->status) === $status)>{{ $statusLabel($status) }}</option>@endforeach</select>
                    <label for="commission-notes">Processing notes <span class="commission-muted">(optional)</span></label><textarea id="commission-notes" name="admin_notes" rows="4" maxlength="5000" placeholder="Add payment details or a reason for this update…">{{ old('admin_notes', $commission->admin_notes) }}</textarea>
                    <button type="submit" class="commission-button"><i class="fas fa-check" aria-hidden="true"></i> Save status update</button>
                </form>
            @elseif($commission->isStatusLocked())
                <p class="commission-explanation"><i class="fas fa-lock" aria-hidden="true"></i> This commission is {{ strtolower($statusLabel($commission->status)) }}. Status and processing notes can no longer be updated.</p>
            @else
                <p class="commission-muted">You can view commission details and history here. An authorized platform admin manages payout status.</p>
            @endif
        </aside>
    </div>
</div>
@endsection
