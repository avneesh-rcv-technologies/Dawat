@php
    $available = $options->where('is_available', true);
    $prices = $available->map(fn ($option) => (float) ($option->discount_price ?? $option->price));
@endphp
@if ($prices->isNotEmpty())
    <span class="whitespace-nowrap font-medium">{{ number_format($prices->min(), 2) }}@if ($prices->max() > $prices->min()) – {{ number_format($prices->max(), 2) }}@endif</span>
    <p class="text-xs text-gray-500">{{ $available->every(fn ($option) => $option->serve_type === 'per_person') ? 'Per person' : 'Per option' }}</p>
@elseif ($options->isNotEmpty())
    <span>—</span>
    <p class="text-xs text-gray-500">No available options</p>
@else
    <span class="whitespace-nowrap">{{ number_format((float) $legacyPrice, 2) }}</span>
@endif
