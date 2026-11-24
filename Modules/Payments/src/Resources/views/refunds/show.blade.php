@extends('backend::layouts.app')
@section('title', 'Refund ' . $refund->refund_number)
@push('styles') @include('payments::refunds.styles') @endpush
@section('breadcrumbs')
    <x-breadcrumbs :breadcrumbs="[['label' => 'Dashboard', 'url' => route('dashboard.index')], ['label' => 'Refund requests', 'url' => route('admin.refunds.index')], ['label' => $refund->refund_number, 'url' => null]]" name="Refund details" />
@endsection
@section('content')
@php
    $currency = $refund->payment?->currency ?? $refund->order?->currency ?? 'USD';
    $currencySymbol = fn_get_currency_symbol($currency) ?? $currency;
    $money = static fn ($amount) => $amount === null ? '—' : fn_format_currency_amount((float) $amount, $currency);
    $statusLabel = static fn ($status) => $status ? ucfirst(str_replace('_', ' ', $status)) : '—';
    $isEditable = $canUpdate && $refund->refund_status !== 'succeeded';
    $previewConfig = $commission?->only(['order_amount', 'commission_percentage', 'platform_commission_amount', 'restaurant_amount', 'refunded_amount', 'paid_amount']);
    $settlement = $commission?->settlementData();
    $selectedStatus = old('refund_status', $refund->refund_status);
    $selectedStatus = in_array($selectedStatus, $statuses, true) ? $selectedStatus : '';
    $needsReference = in_array($selectedStatus, ['processing', 'succeeded'], true);
    $statusMessages = [
        'pending' => 'The refund is awaiting review. Commission remains on hold.',
        'processing' => 'The refund is in progress. Add the payment provider reference.',
        'succeeded' => 'Saving Succeeded makes the refund final and updates the commission.',
        'failed' => 'Record the failed attempt. No commission reversal is applied.',
        'rejected' => 'Record the rejection. No commission reversal is applied.',
    ];
@endphp
<div class="refund-page refund-detail-page">
    <a class="refund-link refund-back" href="{{ route('admin.refunds.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to refunds</a>
    <header class="refund-head refund-card">
        <div class="refund-header-main">
            <div>
                <p class="refund-eyebrow">Refund request</p>
                <h1>{{ $refund->refund_number }}</h1>
                <p class="refund-muted">{{ $refund->order?->vendor?->name ?? 'Restaurant' }} <span aria-hidden="true">·</span> Requested {{ $refund->created_at?->format('d M Y, H:i') }}</p>
            </div>
            <span class="refund-badge" data-status="{{ $refund->refund_status }}">{{ $statusLabel($refund->refund_status) }}</span>
        </div>
        <div class="refund-summary">
            <div><span>{{ $refund->refund_status === 'succeeded' ? 'Refunded amount' : 'Requested amount' }}</span><strong>{{ $money($refund->refund_amount) }}</strong></div>
            <div><span>Original captured payment</span><strong>{{ $money($refund->payment?->amount) }}</strong></div>
            <div><span>Related order</span><a class="refund-link" href="{{ route('admin.orders.show', $refund->order_id) }}">{{ $refund->order?->order_number ?? 'View order' }} <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a></div>
        </div>
    </header>
    @if(session('success'))<div class="refund-alert refund-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="refund-alert" role="alert"><strong>Review the refund details</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="refund-detail-grid">
        <main class="refund-detail-main">
            @if($isEditable && $commission)
                <section id="refund-commission-preview" class="refund-card refund-impact-card" aria-labelledby="refund-impact-heading">
                    <div class="refund-panel-heading">
                        <div><p class="refund-eyebrow">Calculation preview</p><h2 id="refund-impact-heading">Refund impact</h2></div>
                        <span class="refund-type-badge" data-refund-preview="type">{{ ucfirst($commissionPreview['refund_type']) }} refund</span>
                    </div>
                    <p class="refund-muted">Estimated totals after this refund is marked <strong>Succeeded</strong>.</p>
                    <div class="refund-impact-totals" aria-live="polite" aria-atomic="true">
                        <div class="refund-impact-customer"><span>Customer receives</span><strong data-refund-preview="refund">{{ $money($refund->refund_amount) }}</strong></div>
                        <div><span>Dawat commission retained</span><strong data-refund-preview="effective">{{ $money($commissionPreview['effective_commission']) }}</strong></div>
                        <div><span>Restaurant payable</span><strong data-refund-preview="payable">{{ $money($commissionPreview['final_payable_amount']) }}</strong></div>
                    </div>
                    <dl class="refund-detail-list refund-impact-breakdown">
                        <div><dt>Customer net paid for the order</dt><dd data-refund-preview="net">{{ $money($commissionPreview['customer_net_paid']) }}</dd></div>
                        <div><dt>Total commission reversed</dt><dd data-refund-preview="reversed">{{ $money($commissionPreview['commission_reversed_amount']) }}</dd></div>
                        <div><dt>Restaurant refund deduction</dt><dd data-refund-preview="deduction">{{ $money($commissionPreview['restaurant_refund_deduction']) }}</dd></div>
                        <div class="refund-recovery-row" data-refund-recovery-row @if((float) $commissionPreview['recovery_due_amount'] <= 0) hidden @endif><dt>Recovery due after prior payout</dt><dd data-refund-preview="recovery">{{ $money($commissionPreview['recovery_due_amount']) }}</dd></div>
                    </dl>
                    <p class="refund-preview-note"><i class="fas fa-circle-info" aria-hidden="true"></i><span>Commission uses the original {{ $commission->commission_percentage }}% rate on the non-refunded amount. Original order totals remain in the record.</span></p>
                </section>
            @endif
            <section class="refund-card">
                <div class="refund-panel-heading"><div><h2>Order & payment</h2><p class="refund-muted">The original payment linked to this request.</p></div></div>
                <dl class="refund-fact-grid">
                    <div><dt>Order status</dt><dd>{{ $refund->order?->formatted_status ?? '—' }}</dd></div>
                    <div><dt>Payment method</dt><dd>{{ $refund->payment?->method?->method?->name ?? '—' }}</dd></div>
                    <div><dt>Payment status</dt><dd>{{ $statusLabel($refund->payment?->status) }}</dd></div>
                    <div><dt>Refund source</dt><dd>{{ $statusLabel($refund->refund_source) }}</dd></div>
                </dl>
                <dl class="refund-detail-list">
                    <div><dt>Payment reference</dt><dd class="refund-reference-value">{{ $refund->payment?->gateway_reference ?: '—' }}</dd></div>
                    <div><dt>Refund transaction</dt><dd>@if($refund->transaction)<a class="refund-link" href="{{ route('admin.transactions.show', $refund->transaction_id) }}">{{ $refund->transaction->transaction_number }} <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>@else — @endif</dd></div>
                    <div><dt>Last updated by</dt><dd>{{ $refund->processor?->name ?? '—' }}</dd></div>
                    <div><dt>Processed at</dt><dd>{{ $refund->processed_at?->format('d M Y, H:i') ?? 'Not processed yet' }}</dd></div>
                </dl>
                <div class="refund-reason"><span>Cancellation reason</span><p>{{ $refund->refund_reason ?: 'No reason recorded' }}</p></div>
            </section>
            @if($commission)
                <section class="refund-card">
                    <div class="refund-panel-heading"><div><h2>Current payout</h2><p class="refund-muted">Saved amounts based on refunds that have already succeeded.</p></div><span class="refund-badge" data-status="{{ $commission->status }}">{{ $statusLabel($commission->status) }}</span></div>
                    <div class="refund-current-totals">
                        <div><span>Refunded to customer</span><strong>{{ $money($settlement['refunded_amount']) }}</strong></div>
                        <div><span>Effective commission</span><strong>{{ $money($settlement['effective_commission']) }}</strong></div>
                        <div><span>Restaurant payable</span><strong>{{ $money($settlement['final_payable_amount']) }}</strong></div>
                    </div>
                    <dl class="refund-detail-list">
                        <div><dt>Commission reversed</dt><dd>{{ $money($settlement['commission_reversed_amount']) }}</dd></div>
                        <div><dt>Restaurant refund deduction</dt><dd>{{ $money($settlement['restaurant_refund_deduction']) }}</dd></div>
                        <div><dt>Already paid to restaurant</dt><dd>{{ $money($settlement['paid_amount']) }}</dd></div>
                        @if((float) $settlement['recovery_due_amount'] > 0)<div class="refund-recovery-row"><dt>Restaurant recovery due</dt><dd>{{ $money($settlement['recovery_due_amount']) }}</dd></div>@endif
                    </dl>
                    <a class="refund-link refund-section-link" href="{{ route('admin.order-commissions.show', $commission->id) }}">View payout details <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                </section>
            @endif
            <section class="refund-card refund-history-card">
                <h2>Activity history</h2><p class="refund-muted">Changes to the refund amount, status and processing notes.</p>
                <div class="refund-table-wrap"><table><thead><tr><th scope="col">Date & actor</th><th scope="col">Status change</th><th scope="col">Amount & reference</th><th scope="col">Order & payment</th><th scope="col">Notes</th></tr></thead><tbody>
                @forelse($refund->histories as $history)
                    <tr><td>{{ $history->created_at?->format('d M Y, H:i') }}<div class="refund-muted">{{ $history->actor?->name ?? 'System / customer cancellation' }}</div></td><td><div class="refund-history-status">{{ $history->old_status ? $statusLabel($history->old_status) : 'Created' }} <span aria-hidden="true">→</span></div><span class="refund-badge" data-status="{{ $history->new_status }}">{{ $statusLabel($history->new_status) }}</span></td><td><strong>{{ $money($history->after['refund_amount'] ?? null) }}</strong><div class="refund-muted refund-reference-value">{{ $history->after['stripe_refund_id'] ?? 'No reference' }}</div></td><td>{{ $orderStatusNames[$history->after['order_status'] ?? ''] ?? '—' }}<div class="refund-muted">{{ $statusLabel($history->after['order_payment_status'] ?? null) }}</div><div class="refund-muted">Payment: {{ $statusLabel($history->after['payment_status'] ?? null) }}</div></td><td>{{ $history->after['admin_notes'] ?? '—' }}</td></tr>
                @empty<tr><td colspan="5" class="refund-empty">No changes recorded yet.</td></tr>@endforelse
                </tbody></table></div>
            </section>
        </main>
        <aside class="refund-card refund-update-card" aria-labelledby="refund-update-heading">
            <div class="refund-update-heading"><span class="refund-update-icon" aria-hidden="true"><i class="fas {{ $isEditable ? 'fa-pen-to-square' : 'fa-receipt' }}"></i></span><div><h2 id="refund-update-heading">{{ $isEditable ? 'Update refund' : ($refund->refund_status === 'succeeded' ? 'Refund completed' : 'Refund summary') }}</h2><p class="refund-muted">{{ $isEditable ? 'Record the latest refund information.' : 'The saved details for this refund.' }}</p></div></div>
            @if(!$canUpdate)
                <p class="refund-provider-note"><i class="fas fa-lock" aria-hidden="true"></i><span>Refund processing is managed by authorized platform admins.</span></p>
                <dl class="refund-detail-list"><div><dt>Amount</dt><dd>{{ $money($refund->refund_amount) }}</dd></div><div><dt>Status</dt><dd>{{ $statusLabel($refund->refund_status) }}</dd></div><div><dt>Reference</dt><dd class="refund-reference-value">{{ $refund->stripe_refund_id ?: '—' }}</dd></div></dl>
            @elseif(!$isEditable)
                <div class="refund-alert refund-success"><i class="fas fa-circle-check" aria-hidden="true"></i> This refund is complete and its saved details are final.</div>
                <dl class="refund-detail-list"><div><dt>Refunded amount</dt><dd>{{ $money($refund->refund_amount) }}</dd></div><div><dt>Reference</dt><dd class="refund-reference-value">{{ $refund->stripe_refund_id ?: '—' }}</dd></div><div><dt>Internal notes</dt><dd>{{ $refund->admin_notes ?: '—' }}</dd></div></dl>
            @else
                <p class="refund-provider-note"><i class="fas fa-circle-info" aria-hidden="true"></i><span>Complete the refund with your payment provider, then record its amount and reference here.</span></p>
                <form method="POST" action="{{ route('admin.refunds.update', $refund->id) }}" data-confirm="Save this manual refund update? A succeeded refund is final." data-confirm-button="Save refund">
                    @csrf @method('PATCH')
                    <div class="refund-form-field">
                        <div class="refund-field-heading"><label for="refund-amount">Refund amount <span class="refund-required" aria-hidden="true">*</span></label><button id="refund-full-amount" class="refund-text-button" type="button">Use full amount</button></div>
                        <div class="refund-amount-control"><span>{{ $currencySymbol }}</span><input id="refund-amount" name="refund_amount" type="number" inputmode="decimal" min="0.01" max="{{ $amountRange['max'] }}" step="0.01" required value="{{ old('refund_amount', $refund->refund_amount) }}" aria-describedby="refund-amount-help"></div>
                        <input id="refund-amount-range" class="refund-range" type="range" min="0.01" max="{{ max(0.01, (float) $amountRange['max']) }}" step="0.01" value="{{ old('refund_amount', $refund->refund_amount) }}" aria-label="Select refund amount">
                        <div class="refund-range-labels"><span>{{ $money(0.01) }}</span><span>{{ $money($amountRange['max']) }}</span></div>
                        <p id="refund-amount-help" class="refund-field-help">Choose a partial amount or use the full available balance.</p>
                    </div>
                    <div class="refund-form-field">
                        <label for="refund-status">Refund status <span class="refund-required" aria-hidden="true">*</span></label>
                        <select id="refund-status" name="refund_status" required aria-describedby="refund-status-help">@if($selectedStatus === '')<option value="" disabled selected>Choose next status</option>@endif @foreach($statuses as $optionStatus)<option value="{{ $optionStatus }}" @selected($selectedStatus === $optionStatus)>{{ $statusLabel($optionStatus) }}</option>@endforeach</select>
                        <p id="refund-status-help" class="refund-field-help" aria-live="polite">{{ $statusMessages[$selectedStatus] ?? '' }}</p>
                    </div>
                    <div class="refund-form-field">
                        <label for="refund-reference">Refund reference <span id="refund-reference-required" class="refund-required" aria-hidden="true" @if(!$needsReference) hidden @endif>*</span></label>
                        <input id="refund-reference" name="stripe_refund_id" maxlength="255" @required($needsReference) aria-describedby="refund-reference-help" value="{{ old('stripe_refund_id', $refund->stripe_refund_id) }}" placeholder="Gateway refund ID or bank reference">
                        <p id="refund-reference-help" class="refund-field-help">Required for Processing and Succeeded refunds.</p>
                    </div>
                    <div class="refund-form-field">
                        <label for="refund-notes">Internal notes <span class="refund-optional">Optional</span></label>
                        <textarea id="refund-notes" name="admin_notes" rows="3" maxlength="5000" placeholder="Add processing details for the refund record…" aria-describedby="refund-notes-help">{{ old('admin_notes', $refund->admin_notes) }}</textarea>
                        <p id="refund-notes-help" class="refund-field-help">These notes are not included in customer notifications.</p>
                    </div>
                    @if(!$commission)<p class="refund-field-help">No commission snapshot is recorded for this order.</p>@endif
                    <div class="refund-form-footer"><button type="submit" class="refund-btn"><i class="fas fa-check" aria-hidden="true"></i><span id="refund-save-label">{{ $selectedStatus === 'succeeded' ? 'Record completed refund' : 'Save refund update' }}</span></button><p class="refund-field-help"><i class="fas fa-bell" aria-hidden="true"></i> Status changes notify the customer.</p></div>
                </form>
            @endif
        </aside>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const amount = document.getElementById('refund-amount');
    const range = document.getElementById('refund-amount-range');
    if (!amount || !range) return;
    const config = @json($previewConfig);
    const currencySymbol = @json($currencySymbol);
    const statusMessages = @json($statusMessages);
    const status = document.getElementById('refund-status');
    const reference = document.getElementById('refund-reference');
    const cents = value => Math.round(Number(value || 0) * 100);
    const money = value => currencySymbol + ' ' + (value / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const update = () => {
        const validAmount = amount.value !== '' && amount.validity.valid;
        const progress = validAmount ? Math.min(100, Math.max(0, Number(amount.value) / Number(amount.max) * 100)) : 0;
        range.style.setProperty('--refund-progress', progress + '%');
        if (!config) return;
        if (!validAmount) {
            document.querySelectorAll('[data-refund-preview]').forEach(node => { node.textContent = '—'; });
            return;
        }
        const order = cents(config.order_amount), fee = cents(config.platform_commission_amount);
        const refund = cents(amount.value), total = Math.min(order, cents(config.refunded_amount) + refund);
        const retained = Math.min(fee, Math.floor(((order - total) * cents(config.commission_percentage) + 5000) / 10000));
        const reversed = fee - retained, deduction = total - reversed;
        const payable = cents(config.restaurant_amount) - deduction;
        const recovery = Math.max(0, cents(config.paid_amount) - payable);
        const values = {type: total >= order ? 'Full refund' : 'Partial refund', refund: money(refund), net: money(order - total), reversed: money(reversed), effective: money(retained), deduction: money(deduction), payable: money(payable), recovery: money(recovery)};
        for (const [key, value] of Object.entries(values)) {
            document.querySelector('[data-refund-preview="' + key + '"]').textContent = value;
        }
        document.querySelector('[data-refund-recovery-row]').hidden = recovery <= 0;
    };
    const updateStatus = () => {
        const required = ['processing', 'succeeded'].includes(status.value);
        reference.required = required;
        document.getElementById('refund-reference-required').hidden = !required;
        document.getElementById('refund-status-help').textContent = statusMessages[status.value] || '';
        document.getElementById('refund-save-label').textContent = status.value === 'succeeded' ? 'Record completed refund' : 'Save refund update';
    };
    range.addEventListener('input', () => { amount.value = Number(range.value).toFixed(2); update(); });
    amount.addEventListener('input', () => { range.value = amount.value; update(); });
    document.getElementById('refund-full-amount').addEventListener('click', () => { amount.value = Number(amount.max).toFixed(2); range.value = amount.value; update(); });
    status.addEventListener('change', updateStatus);
    updateStatus();
    update();
});
</script>
@endpush
