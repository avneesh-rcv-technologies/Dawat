@if(!empty($displayOptions))
<div class="mt-3 border-t border-gray-100 pt-3">
    <p class="text-xs font-semibold text-gray-700 mb-2">Selected Options</p>
    <div class="space-y-3">
        @foreach(collect($displayOptions)->groupBy('group_name') as $groupName => $groupOptions)
        <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
            <p class="text-xs font-semibold text-blue-900 mb-2">{{ $groupName }}</p>
            <div class="space-y-2">
                @foreach($groupOptions as $option)
                <div class="flex flex-wrap items-start justify-between gap-2 text-sm">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 break-words">{{ $option['name'] }}</p>
                        @if($option['quantity'] > 1)
                        <p class="text-xs text-gray-600">Selection quantity: {{ $option['quantity'] }}</p>
                        @endif
                    </div>
                    <div class="text-right text-xs text-gray-600">
                        @if($option['price'] !== null)
                        <p>{{ $option['price_recorded'] ? 'Extra price' : 'Current extra price' }}</p>
                        <p class="font-medium text-gray-900 mt-1">
                            {{ $option['price'] > 0 ? '+' : '' }}{{ fn_convert_currency($option['price'], $currency ?? 'USD') }}
                        </p>
                        @else
                        <p>Extra price not recorded</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
