@php
    $receiptMoney = $formatMoney ?? fn ($amount) => fn_convert_currency((float) $amount, $currency ?? 'USD');
    $snapshot = $item['selected_option_snapshot'] ?? [];
    if (is_string($snapshot)) $snapshot = json_decode($snapshot, true) ?: [];
    $serveType = $item['serve_type'] ?? $snapshot['serve_type'] ?? '';
    $perPerson = ($snapshot['pricing_basis'] ?? $serveType) === 'per_person';
    $servingName = $item['serving_name'] ?? $snapshot['serving_name'] ?? null;
    $quantity = (int) ($item['quantity'] ?? 1);
    $guests = $item['guest_count'] ?? null;
    $units = $perPerson ? $guests : $quantity;
    $unitPrice = (float) ($item['price'] ?? $item['unit_price'] ?? 0);
    $lineSubtotal = (float) ($item['total_price'] ?? $unitPrice * ($units ?? 1));
    $lineDiscount = (float) ($item['discount_amount'] ?? 0);
    $lineTax = (float) ($item['tax_amount'] ?? $item['tax'] ?? ($lineSubtotal * (float) ($item['tax_rate'] ?? 0) / 100));
    $inclusive = (bool) ($item['is_tax_inclusive'] ?? false);
    $lineTotal = (float) ($item['final_price'] ?? ($lineSubtotal + ($inclusive ? 0 : $lineTax)));
    $taxRate = rtrim(rtrim(number_format((float) ($item['tax_rate'] ?? 0), 2, '.', ''), '0'), '.');
    $size = isset($snapshot['quantity'], $snapshot['unit'])
        ? rtrim(rtrim(number_format((float) $snapshot['quantity'], 2, '.', ''), '0'), '.').' '.$snapshot['unit'] : null;
    $image = $item['item_image'] ?? $item['food_item_image'] ?? null;
    if (is_array($image)) $image = $image['url'] ?? null;
@endphp
<article class="receipt-item" aria-label="{{ $item['item_name'] ?? $item['food_item_name'] ?? 'Order item' }}">
    <div class="receipt-item-heading">
        <div class="receipt-image-shell">
            <span class="receipt-image-fallback" aria-hidden="true"><i class="fas fa-utensils"></i></span>
            @if($image)
                <img src="{{ $image }}" alt="{{ $item['item_name'] ?? $item['food_item_name'] ?? 'Food item' }}" class="receipt-image" loading="lazy" onerror="this.hidden = true;">
            @endif
        </div>
        <div class="receipt-item-title">
            @if(!empty($item['categories']))
                <div class="receipt-categories">
                    @foreach($item['categories'] as $category)
                        <span>{{ !empty($category['parent_name']) ? $category['parent_name'].' / ' : '' }}{{ $category['name'] }}</span>
                    @endforeach
                </div>
            @endif
            <h3>
                @if(!empty($item['item_url']))
                    <a href="{{ $item['item_url'] }}" class="receipt-detail-link">{{ $item['item_name'] ?? $item['food_item_name'] ?? 'Order item' }} <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                @else
                    {{ $item['item_name'] ?? $item['food_item_name'] ?? 'Order item' }}
                @endif
            </h3>
            @if(!empty($item['sku']))<p class="receipt-muted">SKU: {{ $item['sku'] }}</p>@endif
            <div class="receipt-tags">
                @if($servingName)<span>{{ $servingName }}</span>@endif
                @if($serveType)<span class="receipt-type">{{ \Illuminate\Support\Str::headline($serveType) }}</span>@endif
            </div>
        </div>
        <div class="receipt-item-amount">
            <span class="receipt-label">Item total</span>
            <strong>{{ $receiptMoney($lineTotal) }}</strong>
            @if($lineTax > 0)<span class="receipt-muted">{{ $inclusive ? 'Tax included' : 'Including tax' }}</span>@endif
        </div>
    </div>

    <dl class="receipt-serving-grid">
        @if($size && !$perPerson)
            <div><dt>Size per option</dt><dd>{{ $size }}</dd></div>
        @endif
        <div><dt>{{ $perPerson ? 'Guests' : 'Quantity ordered' }}</dt><dd>{{ $units ?? 'Not recorded' }}{{ !$perPerson ? ($servingName ? ' × '.$servingName : ((int) $units === 1 ? ' item' : ' items')) : '' }}</dd></div>
        @if(!$perPerson && !empty($snapshot['serves']))
            <div><dt>Serves per option</dt><dd>{{ $snapshot['serves'] }} {{ (int) $snapshot['serves'] === 1 ? 'person' : 'people' }}</dd></div>
            <div><dt>Total serves</dt><dd>{{ (int) $snapshot['serves'] * $quantity }} people</dd></div>
        @endif
        <div><dt>{{ $perPerson ? 'Price per guest' : ($servingName ? 'Price per option' : 'Unit price') }}</dt><dd>{{ $receiptMoney($unitPrice) }}</dd></div>
    </dl>

    @include('orders::orders.components.selected_options', ['displayOptions' => $displayOptions ?? []])

    <div class="receipt-item-pricing">
        <p class="receipt-label">Price breakdown</p>
        <dl class="receipt-rows">
            @if($lineDiscount > 0)
                <div><dt>Before item discount</dt><dd>{{ $receiptMoney($lineSubtotal + $lineDiscount) }}</dd></div>
                <div class="receipt-saving"><dt>Item discount</dt><dd>−{{ $receiptMoney($lineDiscount) }}</dd></div>
            @endif
            <div><dt>Item subtotal @if($lineDiscount > 0)<span class="receipt-muted">(after discount)</span>@endif</dt><dd>{{ $receiptMoney($lineSubtotal) }}</dd></div>
            <div><dt>Tax @if($lineTax > 0 && (float) $taxRate > 0) ({{ $taxRate }}%)@endif{{ $inclusive ? ' · included' : '' }}</dt><dd>{{ $receiptMoney($lineTax) }}</dd></div>
            <div class="receipt-row-total"><dt>Item total</dt><dd>{{ $receiptMoney($lineTotal) }}</dd></div>
        </dl>
        @if((float) ($item['delivery_fee'] ?? 0) > 0)
            <p class="receipt-footnote">Delivery allocation: {{ $receiptMoney($item['delivery_fee']) }} · included in the order delivery charge below.</p>
        @endif
    </div>
    @if(!empty($item['special_instructions']))
        <div class="receipt-instructions"><i class="fas fa-sticky-note" aria-hidden="true"></i><div><strong>Special instructions</strong><p>{{ $item['special_instructions'] }}</p></div></div>
    @endif
</article>
