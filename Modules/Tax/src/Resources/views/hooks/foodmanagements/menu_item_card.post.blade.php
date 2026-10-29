@if(isset($foodItem) && $foodItem)
    @php
        $taxCalculation = $foodItem->getTaxCalculation();
        $showTaxDetails = fn_get_setting('tax.show_in_menu', true);
    @endphp
    
    @if($showTaxDetails && $taxCalculation['tax_rate'] > 0)
        <div class="tax-info-menu mt-2 text-xs text-gray-500">
            @if($taxCalculation['is_tax_inclusive'])
                <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-700">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $taxCalculation['tax_category_name'] }} Included
                </span>
            @else
                <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-700">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    +{{ $taxCalculation['tax_category_name'] }}
                </span>
            @endif
        </div>
    @endif
@endif