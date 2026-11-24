@php
    // The Details query returns a plain object; use model casts and policy defaults.
    $policyRestaurant = $restaurant instanceof \Modules\Restaurant\Models\Restaurant
        ? $restaurant
        : (new \Modules\Restaurant\Models\Restaurant())->setRawAttributes((array) $restaurant, true);
    $cancellationEnabled = $policyRestaurant->allowsOrderCancellation();
    $cancellableStatuses = $policyRestaurant->getCancellableStatuses();
    $deliveryEnabled = (bool) $policyRestaurant->is_delivery;
    $radius = $policyRestaurant->delivery_radius_miles;
    $rate = (float) ($policyRestaurant->delivery_cost_per_mile ?? 0);
    $freeDeliveryEnabled = (bool) $policyRestaurant->free_delivery_enabled;
    $freeThreshold = $policyRestaurant->free_delivery_threshold;
@endphp
<style>
    .restaurant-order-policies { margin-top: 24px; }
    .restaurant-order-policies .policy-heading { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 16px; }
    .restaurant-order-policies .policy-heading h3 { color: #172033; font-size: 18px; font-weight: 600; }
    .restaurant-order-policies .policy-heading p { color: #64748b; font-size: 12px; }
    .restaurant-order-policies .policy-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
    .restaurant-order-policies .policy-card { padding: 24px; border: 1px solid #e5e7eb; border-radius: 10px; background: #f8fafc; min-width: 0; }
    .restaurant-order-policies .policy-card-heading { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
    .restaurant-order-policies .policy-card h4 { color: #172033; font-size: 16px; font-weight: 600; }
    .restaurant-order-policies .policy-badge { display: inline-block; padding: 4px 9px; border-radius: 6px; font-size: 12px; font-weight: 500; background: #ecfdf3; color: #166534; }
    .restaurant-order-policies .policy-badge.is-disabled { background: #f1f5f9; color: #64748b; }
    .restaurant-order-policies dl { font-size: 13px; }
    .restaurant-order-policies dl > div { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; padding: 9px 0; }
    .restaurant-order-policies dt { color: #596579; }
    .restaurant-order-policies dd { text-align: right; color: #172033; font-weight: 600; font-variant-numeric: tabular-nums; }
    .restaurant-order-policies .policy-status-label { font-size: 12px; color: #64748b; margin: 14px 0 8px; }
    .restaurant-order-policies .policy-statuses { display: flex; flex-wrap: wrap; gap: 6px; }
    .restaurant-order-policies .policy-status { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; color: #334155; font-size: 12px; }
    .restaurant-order-policies .policy-status small { color: #64748b; font-size: 10px; }
    .restaurant-order-policies .policy-note { border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 16px; color: #64748b; font-size: 12px; line-height: 1.7; }
    .restaurant-order-policies .policy-empty { font-size: 13px; color: #64748b; line-height: 1.7; }
    @media (max-width: 900px) { .restaurant-order-policies .policy-grid { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 640px) { .restaurant-order-policies .policy-card { padding: 18px; } }
</style>
<section class="restaurant-order-policies" aria-label="Current order policies">
    <div class="policy-heading">
        <h3>Current Order Policies</h3>
        <p>Current restaurant settings</p>
    </div>
    <div class="policy-grid">
        <article class="policy-card" aria-label="Cancellation policy">
            <div class="policy-card-heading">
                <h4>Cancellation Policy</h4>
                <span class="policy-badge {{ $cancellationEnabled ? '' : 'is-disabled' }}">{{ $cancellationEnabled ? 'Allowed' : 'Not allowed' }}</span>
            </div>
            @if($cancellationEnabled)
                <dl>
                    <div><dt>Eligibility</dt><dd>Based on order status</dd></div>
                    <div><dt>Time restriction</dt><dd>No time limit enforced</dd></div>
                    <div><dt>Cancellation fee</dt><dd>{{ fn_format_site_currency($policyRestaurant->calculateCancellationFee(0)) }}</dd></div>
                </dl>
                <p class="policy-status-label">Customers can cancel in these statuses</p>
                <div class="policy-statuses">
                    @foreach($cancellableStatuses as $statusCode)
                        @php($workflowStatus = \Modules\Orders\Enums\OrderStatus::fromCode($statusCode))
                        <span class="policy-status">{{ $workflowStatus?->label() ?? $statusCode }} <small>{{ $statusCode }}</small></span>
                    @endforeach
                </div>
                <p class="policy-note">Cancellation must match an allowed status. Orders that are delivered, cancelled, failed, or refunded cannot be cancelled again.</p>
            @else
                <p class="policy-empty">Customer order cancellation is disabled. Customers should contact the restaurant for assistance.</p>
            @endif
        </article>
        <article class="policy-card" aria-label="Delivery policy">
            <div class="policy-card-heading">
                <h4>Delivery Policy</h4>
                <span class="policy-badge {{ $deliveryEnabled ? '' : 'is-disabled' }}">{{ $deliveryEnabled ? 'Available' : 'Disabled' }}</span>
            </div>
            <dl>
                <div><dt>Delivery radius</dt><dd>{{ $radius !== null ? number_format((float) $radius, 2).' miles' : 'No radius limit configured' }}</dd></div>
                <div><dt>Rate per mile</dt><dd>{{ fn_format_site_currency($rate) }}</dd></div>
                <div><dt>Free delivery offer</dt><dd>{{ $freeDeliveryEnabled ? 'Enabled' : 'Disabled' }}</dd></div>
                @if($freeDeliveryEnabled)
                    <div><dt>Minimum for free delivery</dt><dd>{{ $freeThreshold !== null ? fn_format_site_currency((float) $freeThreshold) : 'Not configured' }}</dd></div>
                @endif
                <div><dt>Pickup delivery charge</dt><dd>{{ fn_format_site_currency(0) }}</dd></div>
            </dl>
            <p class="policy-note">
                @if(!$deliveryEnabled)
                    Delivery is currently disabled. The configured rates apply when delivery is enabled.
                @else
                    Delivery charge = distance in miles × rate per mile. Distance uses a straight-line estimate.
                    @if($radius !== null) Orders outside the delivery radius are unavailable for delivery. @endif
                    @if($freeDeliveryEnabled && $freeThreshold !== null) Free delivery applies within the service area when the food subtotal after discounts, before tax, meets the minimum. @endif
                @endif
            </p>
        </article>
    </div>
</section>
