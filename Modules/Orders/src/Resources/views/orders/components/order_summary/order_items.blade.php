<div class="bg-white rounded-lg border divide-gray-100 overflow-x-auto overflow-y-hidden">
    <table class="min-w-full divide-y divide-gray-100">
       @php
        $mode_setting = fn_get_setting('general.line_item_type') ?? 'L';
    @endphp
        @include('orders::orders.components.order_summary.table.head')
        <tbody id="order_items" class="bg-white divide-y divide-gray-100">
            @if(!empty($order_items))
                @foreach($order_items ?? [] as $item)
                    <tr class="hover:bg-gray-50 group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-16 w-16 bg-gray-200 rounded-md flex items-center justify-center">
                                    <i class="fas fa-box text-gray-400"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="flex text-sm font-medium text-black-100">
                                        <a href="">{{ $item['food_item_name'] ?? $item['part_name'] ?? 'N/A' }}</a>
                                        @if($mode === 'create' || $mode === 'edit')
                                            <span
                                                class="ml-2 flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="javascript:;" class="text-gray-600 hover:text-red-600 remove-item"
                                                    data-item-id="{{ $item['id'] }}" data-food-item-id="{{ $item['food_item_id'] ?? $item['product_id'] ?? '' }}">
                                                    <i class="fas fa-times-circle"></i>
                                                </a>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-sm text-gray-500">
                                        <a href="">FOOD SKU: #{{ $item['sku'] ?? 'N/A' }}</a>
                                    </div>

                                    @if(!empty($item['selected_options']))
                                        <div class="text-xs text-gray-400 mt-1">
                                            Options: {{ is_array($item['selected_options']) ? implode(', ', array_keys($item['selected_options'])) : $item['selected_options'] }}
                                        </div>
                                    @endif

                                    @if(!empty($item['special_instructions']))
                                        <div class="text-xs text-gray-400 mt-1">
                                            Instructions: {{ $item['special_instructions'] }}
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="relative w-24">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">{{fn_get_currency_data($currency)->symbol}}</span>
                                </div>
                                <input type="text" value="{{ fn_convert_currency_rate($item['price'] ?? 0, $currency) }}"
                                    class="block w-full pl-7 pr-2 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </td>
                        
                         @if ($mode_setting !== 'S')
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            @php
                                $taxAmount = 0;
                                if (isset($item['tax_rate']) && isset($item['price']) && isset($item['quantity'])) {
                                    $taxAmount = ($item['price'] * $item['quantity']) * ($item['tax_rate'] / 100);
                                } elseif (isset($item['tax'])) {
                                    $taxAmount = $item['tax'];
                                }
                            @endphp
                            {{ fn_convert_currency($taxAmount, $currency) }}
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($mode === 'create' || $mode === 'edit')
                                    <a class="p-1 text-gray-500 hover:text-gray-700 quantity-decrease"
                                        data-item-id="{{ $item['id'] }}">
                                        <i class="fas fa-minus-circle"></i>
                                    </a>
                                @endif
                                <input type="text" value="{{ $item['quantity'] }}"
                                    class="w-12 mx-2 text-center border border-gray-300 rounded-md py-1 text-sm quantity-input"
                                    data-item-id="{{ $item['id'] }}" data-price="{{ $item['price'] }}"
                                    data-original-quantity="{{ $item['quantity'] }}">
                                @if($mode === 'create' || $mode === 'edit')
                                    <a class="p-1 text-gray-500 hover:text-gray-700 quantity-increase"
                                        data-item-id="{{ $item['id'] }}">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 line-total">
                            @php
                                $lineTotal = $item['line_total'] ?? $item['total_price'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 0));
                            @endphp
                            {{ fn_convert_currency($lineTotal, $currency) }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7">
                        <div class="text-sm text-gray-500 text-center p-6">No Items</div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>