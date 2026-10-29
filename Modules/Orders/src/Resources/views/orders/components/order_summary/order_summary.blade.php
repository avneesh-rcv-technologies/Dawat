<div id="order_summary" class="mt-6 pt-4 border-t border-gray-100">
    <div class="flex justify-between py-2">
        <span class="text-gray-600">Subtotal</span>
        <span
            class="text-gray-800 font-medium">{{ !empty($order_summary['subtotal']) ? fn_convert_currency($order_summary['subtotal'] ?? 0, $currency) : 0 }}</span>
    </div>
    @if(!empty($order_summary) && $order_summary['discount'] > 0)
        <div class="flex justify-between py-2">
            <span class="text-gray-600">Discount</span>
            <span
                class="text-red-600 font-medium">-{{ fn_convert_currency($order_summary['discount'] ?? 0, $currency) }}</span>
        </div>
    @endif
    <!-- <div class="flex justify-between py-2">
        <span class="text-gray-600">Labour Charge</span>
        <div class="flex items-center gap-2">
            <span
                class="text-gray-800 font-medium">{{ !empty($order_summary['labour_charge']) ? fn_convert_currency($order_summary['labour_charge'] ?? 0, $currency) : 0 }}</span>
        </div>
    </div> -->
    <div class="flex justify-between items-start py-2">
        <span class="text-gray-600">Tax</span>

        <div class="flex flex-col items-end">
            @if(!empty($order_summary['taxes']))
                <div class="mb-2 space-y-1 text-right">
                    @foreach($order_summary['taxes'] as $tax)
                        <div class="text-sm text-gray-500 flex justify-between w-full">
                            <span class="mr-3">{{ $tax['name'] }} ({{ number_format($tax['rate'], 2) }}%)</span>
                            <span>{{ fn_convert_currency($tax['amount'], $currency) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex items-center gap-2">
                <input type="text"
                    value="{{ !empty($order_summary['tax']) ? fn_convert_currency($order_summary['tax'] ?? 0, $currency) : 0 }}"
                    class="w-20 p-1 border border-gray-300 rounded-md text-sm text-right">
                <!-- <button class="text-gray-400 hover:text-blue-600">
                                <i class="fas fa-check text-xs"></i>
                            </button> -->
            </div>
        </div>
    </div>
    <div class="flex justify-between py-3 mt-2 border-t border-gray-100">
        <span class="text-gray-800 font-semibold">Total</span>
        <span
            class="text-gray-800 font-bold text-lg">{{ !empty($order_summary['total']) ? fn_convert_currency($order_summary['total'] ?? 0, $currency) : 0 }}</span>
    </div>
</div>