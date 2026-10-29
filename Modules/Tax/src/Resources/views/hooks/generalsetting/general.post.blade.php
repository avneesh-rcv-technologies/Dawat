<!-- Tax Configuration -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t">
    <div class="md:col-span-1">
        <label for="tax_calculation_type" class="block text-sm font-medium text-gray-700">Tax Calculation</label>
        <p class="mt-1 text-sm text-gray-500">Configure how tax is calculated for food items.</p>
    </div>
    <div class="md:col-span-2">
        <select id="tax_calculation_type" name="settings[tax.calculation_type]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            @foreach (\Modules\Tax\Enums\TaxCalculationType::cases() as $type)
            <option value="{{ $type->value }}" 
                    {{ old('tax.calculation_type', fn_get_setting('tax.calculation_type', 'exclusive')) == $type->value ? 'selected' : '' }}
                    title="{{ $type->description() }}">
                {{ $type->label() }}
            </option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-gray-500">
            <strong>Inclusive:</strong> Tax is already included in food prices. 
            <strong>Exclusive:</strong> Tax will be added on top of food prices.
        </p>
    </div>
</div>

<!-- Default Tax Categories -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t">
    <div class="md:col-span-1">
        <label for="default_tax_categories" class="block text-sm font-medium text-gray-700">Default Tax Categories</label>
        <p class="mt-1 text-sm text-gray-500">Select multiple tax categories for food items.</p>
    </div>
    <div class="md:col-span-2">
        @php
            $selectedCategories = fn_get_setting('tax.default_category_ids', []);
            if (is_string($selectedCategories)) {
                $selectedCategories = json_decode($selectedCategories, true) ?: [];
            }
            if (!is_array($selectedCategories)) {
                $selectedCategories = [];
            }
            $availableCategories = \Modules\Tax\Models\TaxCategory::active()->get();
        @endphp
        
        <!-- Modern Checkbox Grid Design -->
        <div class="border border-gray-300 rounded-lg p-4 bg-white">
            @if($availableCategories->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($availableCategories as $category)
                    <label class="flex items-start space-x-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition-all duration-200 {{ in_array($category->id, $selectedCategories) ? 'bg-blue-50 border-blue-300 ring-1 ring-blue-200' : 'bg-gray-50' }}">
                        <input type="checkbox" 
                               name="settings[tax.default_category_ids][]" 
                               value="{{ $category->id }}"
                               {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}
                               class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $category->name }}
                            </div>
                            @if($category->description)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $category->description }}
                                </div>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
                
                <!-- Selected Count Display -->
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">
                            <span id="selected-count">{{ count($selectedCategories) }}</span> of {{ $availableCategories->count() }} categories selected
                        </span>
                        <button type="button" 
                                onclick="clearAllTaxCategories()" 
                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                            Clear All
                        </button>
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-sm">No tax categories available.</p>
                    <a href="{{ route('admin.tax-category.index') }}" 
                       class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                        Create tax categories
                        <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
        
        <p class="mt-2 text-xs text-gray-500">
            Select the tax categories that will be applied to food items by default. 
            <a href="{{ route('admin.tax-category.index') }}" class="text-blue-600 hover:text-blue-800">Manage tax categories</a>
        </p>
    </div>
</div>

<script>
function clearAllTaxCategories() {
    const checkboxes = document.querySelectorAll('input[name="settings[tax.default_category_ids][]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        // Trigger change event to update styling
        checkbox.dispatchEvent(new Event('change'));
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('input[name="settings[tax.default_category_ids][]"]:checked');
    const countElement = document.getElementById('selected-count');
    if (countElement) {
        countElement.textContent = checkboxes.length;
    }
}

// Add event listeners for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="settings[tax.default_category_ids][]"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update visual styling
            const label = this.closest('label');
            if (this.checked) {
                label.classList.add('bg-blue-50', 'border-blue-300', 'ring-1', 'ring-blue-200');
                label.classList.remove('bg-gray-50');
            } else {
                label.classList.remove('bg-blue-50', 'border-blue-300', 'ring-1', 'ring-blue-200');
                label.classList.add('bg-gray-50');
            }
            updateSelectedCount();
        });
    });
});
</script>

<!-- Tax Application Level -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t">
    <div class="md:col-span-1">
        <label for="tax_application_level" class="block text-sm font-medium text-gray-700">Tax Apply</label>
        <p class="mt-1 text-sm text-gray-500">Choose where tax should be applied in the order.</p>
    </div>
    <div class="md:col-span-2">
        <select id="tax_application_level" name="settings[tax.application_level]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            @foreach (\Modules\Tax\Enums\TaxApplicationLevel::cases() as $level)
            <option value="{{ $level->value }}" 
                    {{ old('tax.application_level', fn_get_setting('tax.application_level', 'food_item')) == $level->value ? 'selected' : '' }}
                    title="{{ $level->description() }}">
                {{ $level->label() }}
            </option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-gray-500">
            <strong>Food Item Level:</strong> Tax calculated per item (recommended for detailed receipts). 
            <strong>Subtotal Level:</strong> Tax calculated on order total (simpler calculation).
        </p>
    </div>
</div>