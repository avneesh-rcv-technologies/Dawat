@if(($mode ?? '') === 'view' && ($isCod ?? false) && !empty($order_id)
    && !in_array($status ?? '', ['C', 'F', 'R'], true)
    && in_array($payment_status ?? '', ['pending', 'unpaid'], true)
    && bouncer('admin')->hasPermission('admin.orders.update-payment-status'))
    <form method="POST" action="{{ route('admin.orders.update-payment-status', $order_id) }}" class="receipt-cod-collection">
        @csrf
        <input type="hidden" name="payment_status" value="paid">
        <div class="receipt-cod-heading"><span aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span><div><h4>Record cash payment</h4><p>Confirm payment after collecting cash from the customer.</p></div></div>
        <div class="receipt-cod-amount"><span>Amount to collect <small>{{ fn_get_currency_symbol($currency ?? 'USD') ?? $currency ?? 'USD' }}</small></span><strong>{{ fn_convert_currency($order_summary['total'] ?? $total ?? 0, $currency ?? 'USD') }}</strong></div>
        <label for="cod-payment-notes" class="receipt-field-label">Collection note <span class="receipt-optional">Optional</span></label>
        <textarea id="cod-payment-notes" name="payment_notes" rows="2" maxlength="1000" class="receipt-collection-notes" placeholder="e.g. Cash collected at delivery">{{ old('payment_notes') }}</textarea>
        @error('payment_status')<p class="text-xs text-red-600 mb-2" role="alert">{{ $message }}</p>@enderror
        @error('payment_notes')<p class="text-xs text-red-600 mb-2" role="alert">{{ $message }}</p>@enderror
        <button type="submit" class="receipt-action-button cod-payment-submit"><i class="fas fa-check-circle" aria-hidden="true"></i><span>Confirm cash collected</span></button>
        <p class="receipt-action-help"><i class="fas fa-circle-info" aria-hidden="true"></i> Payment status changes to Paid after saving.</p>
    </form>
@endif
