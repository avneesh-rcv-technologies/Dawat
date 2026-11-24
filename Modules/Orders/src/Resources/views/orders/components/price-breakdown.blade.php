@php
    $summaryMoney = $formatMoney ?? fn ($amount) => fn_convert_currency((float) $amount, $currency ?? 'USD');
    $summarySubtotal = (float) ($order_summary['subtotal'] ?? $subtotal ?? 0);
    $summaryDiscount = (float) ($order_summary['discount'] ?? $discount ?? 0);
    $summaryTax = (float) ($order_summary['tax'] ?? $tax ?? 0);
    $summaryDelivery = (float) ($order_summary['delivery_fee'] ?? $delivery_fee ?? 0);
    $summaryService = (float) ($order_summary['service_charge'] ?? $service_charge ?? 0);
    $summaryTotal = (float) ($order_summary['total'] ?? $total ?? 0);
    $summaryInclusive = (bool) ($order_summary['is_tax_inclusive'] ?? $is_tax_inclusive ?? false);
    $isDelivery = ($order_type ?? '') === 'delivery';
    $duration = isset($estimated_delivery_minutes) ? (int) $estimated_delivery_minutes : null;
@endphp
<section class="receipt-summary bg-white rounded-lg shadow-sm p-6" aria-label="Order price breakdown">
    <div class="receipt-summary-heading"><h3 class="text-lg font-semibold text-gray-900">Order Summary</h3><span>{{ fn_get_currency_symbol($currency ?? 'USD') ?? $currency ?? 'USD' }}</span></div>
    <dl class="receipt-rows">
        <div><dt>Items subtotal</dt><dd>{{ $summaryMoney($summarySubtotal) }}</dd></div>
        @if($summaryDiscount > 0)
            <div class="receipt-saving"><dt>Discount @if(!empty($coupon_code))<span class="receipt-coupon">{{ $coupon_code }}</span>@endif</dt><dd>−{{ $summaryMoney($summaryDiscount) }}</dd></div>
        @endif
        <div><dt>Tax{{ $summaryInclusive ? ' (included in prices)' : '' }}</dt><dd>{{ $summaryMoney($summaryTax) }}</dd></div>
        @if($isDelivery || $summaryDelivery > 0)
            <div><dt>Delivery charge</dt><dd class="{{ $summaryDelivery === 0.0 ? 'receipt-saving' : '' }}">{{ $summaryDelivery === 0.0 ? 'Free delivery' : $summaryMoney($summaryDelivery) }}</dd></div>
        @endif
        @if($summaryService > 0)
            <div><dt>Service charge</dt><dd>{{ $summaryMoney($summaryService) }}</dd></div>
        @endif
    </dl>
    <div class="receipt-grand-total"><div><span>Order total</span><p>{{ $summaryInclusive ? 'Tax included in the recorded total' : 'Total payable for this order' }}</p></div><strong>{{ $summaryMoney($summaryTotal) }}</strong></div>
    @if(!empty($event_guest_count) || ($isDelivery && (isset($delivery_distance_miles) || $duration !== null)))
        <dl class="receipt-delivery-details">
            @if(!empty($event_guest_count))<div><dt>Event guests</dt><dd>{{ $event_guest_count }}</dd></div>@endif
            @if($isDelivery && isset($delivery_distance_miles))<div><dt>Delivery distance</dt><dd>{{ number_format((float) $delivery_distance_miles, 2) }} miles</dd></div>@endif
            @if($isDelivery && $duration !== null)
                <div><dt>Estimated travel time</dt><dd>{{ $duration >= 60 ? intdiv($duration, 60).' hr ' : '' }}{{ $duration % 60 }} min</dd></div>
            @endif
        </dl>
    @endif
</section>
