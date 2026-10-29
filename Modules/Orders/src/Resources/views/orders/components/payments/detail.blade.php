@if($payment_details)
    <div class="rounded-xl shadow-sm p-3 flex items-center gap-3 border divide-gray-100 dark:bg-slate-800">
        @php
            $details = $payment_details->details;
            $cardBrand = $details['card']['brand'] ?? 'Card';
            $last4 = $details['card']['last4'] ?? '****';
            $amount = $payment_details->amount ?? 0;
            $currency = $payment_details->currency ?? 'USD';
        @endphp
        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
            @if(strtolower($cardBrand) === 'visa')
                <i class="fab fa-cc-visa text-blue-800 text-lg"></i>
            @elseif(strtolower($cardBrand) === 'mastercard')
                <i class="fab fa-cc-mastercard text-red-600 text-lg"></i>
            @else
                <i class="far fa-credit-card text-gray-600 text-lg"></i>
            @endif
        </div>
        <div class="flex-grow">
            <p class="text-sm dark:text-white">{{ $cardBrand }} ending in {{ $last4 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Charged: {{ $currency }} {{ number_format($amount, 2) }}</p>
        </div>
    </div>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">No payment details available.</p>
@endif
