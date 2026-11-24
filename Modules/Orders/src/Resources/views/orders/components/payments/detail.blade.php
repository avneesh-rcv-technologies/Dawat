@if($payment_details)
    @php
        $details = $payment_details->details ?? [];
        $cashPayment = ($isCod ?? false) || ($details['collection_type'] ?? '') === 'cash';
        $cardBrand = $details['card']['brand'] ?? 'Card';
        $last4 = $details['card']['last4'] ?? null;
        $amount = $payment_details->amount ?? 0;
        $paymentCurrency = $payment_details->currency ?? 'USD';
        $settled = in_array($payment_details->status, ['completed', 'paid', 'captured'], true);
    @endphp
    <div class="receipt-payment-detail rounded-xl shadow-sm p-3 flex items-center gap-3 border border-gray-200 bg-gray-50">
        <div class="w-10 h-10 bg-white rounded flex items-center justify-center">
            @if($cashPayment)
                <i class="fas fa-money-bill-wave text-green-700 text-lg" aria-hidden="true"></i>
            @elseif(strtolower($cardBrand) === 'visa')
                <i class="fab fa-cc-visa text-blue-800 text-lg" aria-hidden="true"></i>
            @elseif(strtolower($cardBrand) === 'mastercard')
                <i class="fab fa-cc-mastercard text-red-600 text-lg" aria-hidden="true"></i>
            @else
                <i class="far fa-credit-card text-gray-600 text-lg" aria-hidden="true"></i>
            @endif
        </div>
        <div class="flex-grow">
            <p class="text-sm text-gray-900">{{ $cashPayment ? ($settled ? 'Cash payment collected' : 'Cash on delivery') : ($last4 ? $cardBrand.' ending in '.$last4 : 'Online payment') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $settled ? 'Paid' : 'Payment amount' }}: {{ fn_format_currency_amount($amount, $paymentCurrency) }}</p>
            @if($cashPayment && $payment_details->processed_at)
                <p class="text-xs text-gray-500 mt-1">Collected {{ $payment_details->processed_at->format('M d, Y h:i A') }}</p>
            @endif
            @if($cashPayment && !empty($details['collection_notes']))
                <p class="text-xs text-gray-600 mt-2">{{ $details['collection_notes'] }}</p>
            @endif
        </div>
    </div>
@else
    <p class="text-sm text-gray-500">No payment details available.</p>
@endif
