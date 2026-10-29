<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parts</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if(!empty($transaction->order->items))
                @foreach($transaction->order->items ?? [] as $item)
                <tr class="">
                    <td class="px-6 py-4">
                        <div class="flex items-start">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900">{{ $item['part_name'] }}</div>
                                <div class="text-sm text-gray-500 mt-1">SKU: #{{ $item['sku'] ?? 'N/A' }}</div>

                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 align-top">
                        {{ fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format(fn_convert_currency_rate($item['price'] ?? 0, $transaction->order->currency), 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 align-top">
                        {{ $item['quantity'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 align-top">
                        {{ fn_get_currency_data($transaction->order->currency)->symbol }}{{ number_format(fn_convert_currency_rate(($item['price'] ?? 0) * ($item['quantity'] ?? 1), $transaction->order->currency), 2) }}
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                        No items found
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>