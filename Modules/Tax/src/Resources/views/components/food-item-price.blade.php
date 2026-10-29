@props(['foodItem', 'showTaxInfo' => true, 'currency' => null, 'layout' => 'default', 'taxCategoryIds' => null])

@php
    $taxCalculation = $foodItem->getTaxCalculation($taxCategoryIds);
    $currency = $currency ?? fn_get_setting('general.currency', 'USD');
    $taxRate = $taxCalculation['tax_rate'];
    $isItemLevelTax = $taxCalculation['is_item_level_tax'] ?? true;
    $taxCategoriesText = $taxCalculation['tax_category_names_string'] ?? '';
@endphp

<div class="food-item-price">
    <!-- Main Price Display -->
    <div class="price-display">
        <span class="final-price text-lg font-semibold">
            {{ number_format($taxCalculation['display_price'], 2) }} {{ $currency }}
        </span>
        
        @if($showTaxInfo)
            <div class="tax-info text-sm text-gray-600 mt-1">
                @if(!$isItemLevelTax)
                    <span class="subtotal-tax text-orange-600">
                        (tax calculated at checkout)
                    </span>
                @elseif($taxRate > 0)
                    @if($taxCalculation['is_tax_inclusive'])
                        <span class="inclusive-tax">
                            (includes {{ $taxCategoriesText }}: {{ number_format($taxCalculation['tax_amount'], 2) }} {{ $currency }})
                        </span>
                    @else
                        <span class="exclusive-tax">
                            Base: {{ number_format($taxCalculation['base_price'], 2) }} {{ $currency }} + 
                            {{ $taxCategoriesText }}: {{ number_format($taxCalculation['tax_amount'], 2) }} {{ $currency }}
                        </span>
                    @endif
                @else
                    <span class="no-tax text-gray-500">
                        (no tax)
                    </span>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Detailed Tax Breakdown (if requested) -->
    @if($layout === 'detailed')
        <div class="tax-breakdown mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Tax Breakdown
            </h4>
            
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Application Level:</span>
                    <span class="font-medium {{ $isItemLevelTax ? 'text-blue-700' : 'text-orange-700' }}">
                        {{ $isItemLevelTax ? 'Item Level' : 'Subtotal Level' }}
                    </span>
                </div>
                
                @if($isItemLevelTax && $taxRate > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Calculation Method:</span>
                        <span class="font-medium {{ $taxCalculation['is_tax_inclusive'] ? 'text-green-700' : 'text-blue-700' }}">
                            {{ $taxCalculation['is_tax_inclusive'] ? 'Tax Inclusive' : 'Tax Exclusive' }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax Categories:</span>
                        <span class="font-medium">{{ $taxCategoriesText ?: 'None' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax Rate:</span>
                        <span class="font-medium">{{ number_format($taxCalculation['tax_rate'], 2) }}%</span>
                    </div>
                    
                    @if($taxCalculation['is_tax_inclusive'])
                        <div class="flex justify-between">
                            <span class="text-gray-600">Price Without Tax:</span>
                            <span class="font-medium">{{ number_format($taxCalculation['price_without_tax'], 2) }} {{ $currency }}</span>
                        </div>
                    @else
                        <div class="flex justify-between">
                            <span class="text-gray-600">Base Price:</span>
                            <span class="font-medium">{{ number_format($taxCalculation['base_price'], 2) }} {{ $currency }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax Amount:</span>
                        <span class="font-medium">{{ number_format($taxCalculation['tax_amount'], 2) }} {{ $currency }}</span>
                    </div>
                @else
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base Price:</span>
                        <span class="font-medium">{{ number_format($taxCalculation['base_price'], 2) }} {{ $currency }}</span>
                    </div>
                @endif
                
                <div class="flex justify-between pt-2 border-t border-gray-300">
                    <span class="font-semibold text-gray-900">Display Price:</span>
                    <span class="font-bold text-lg">{{ number_format($taxCalculation['display_price'], 2) }} {{ $currency }}</span>
                </div>
            </div>
            
            <div class="mt-3 p-2 bg-blue-50 rounded text-xs text-blue-800">
                @if(!$isItemLevelTax)
                    <strong>Note:</strong> Tax will be calculated on the order subtotal at checkout, not per individual item.
                @elseif($taxRate > 0)
                    @if($taxCalculation['is_tax_inclusive'])
                        <strong>Note:</strong> Tax from {{ $taxCategoriesText }} is already included in the displayed price.
                    @else
                        <strong>Note:</strong> Tax from {{ $taxCategoriesText }} will be added to the base price at checkout.
                    @endif
                @else
                    <strong>Note:</strong> No tax is configured for this item.
                @endif
            </div>
        </div>
    @elseif($layout === 'compact')
        <div class="tax-compact mt-2 text-xs text-gray-500">
            @if(!$isItemLevelTax)
                Tax at checkout
            @elseif($taxRate > 0)
                @if($taxCalculation['is_tax_inclusive'])
                    Incl. {{ $taxCategoriesText }}
                @else
                    + {{ $taxCategoriesText }}
                @endif
            @else
                No tax
            @endif
        </div>
    @endif
</div>