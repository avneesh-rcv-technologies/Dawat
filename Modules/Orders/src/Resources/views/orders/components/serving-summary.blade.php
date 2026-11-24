@if(!empty($item['selected_option_snapshot']))
    <div class="mt-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-xs text-gray-600 space-y-1">
        <div class="font-medium text-gray-900">{{ $item['serving_name'] ?? $item['selected_option_snapshot']['serving_name'] ?? 'Serving selection' }}</div>
        <div>
            {{ ucfirst(str_replace('_', ' ', $item['serve_type'] ?? '')) }}
            @if(($item['selected_option_snapshot']['pricing_basis'] ?? '') === 'per_person')
                · {{ $item['guest_count'] ?? 0 }} guests · Price per guest
            @else
                · {{ $item['quantity'] ?? 1 }} selections
            @endif
        </div>
        <div>Tax: {{ fn_convert_currency($item['tax_amount'] ?? $item['tax'] ?? 0, $currency ?? 'USD') }}
            · Discount: {{ fn_convert_currency($item['discount_amount'] ?? 0, $currency ?? 'USD') }}</div>
        @if(isset($item['final_price']))
            <div>Food total including tax: {{ fn_convert_currency($item['final_price'], $currency ?? 'USD') }}</div>
        @endif
        @if(isset($item['delivery_fee']))
            <div>Delivery share: {{ fn_convert_currency($item['delivery_fee'], $currency ?? 'USD') }}</div>
        @endif
    </div>
@endif
