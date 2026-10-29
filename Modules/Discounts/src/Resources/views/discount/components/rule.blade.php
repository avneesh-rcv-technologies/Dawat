<!-- Discount Rules -->
<div class="bg-white rounded-lg shadow p-6 space-y-4 mt-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Discount Rules</h3>
        <button type="button" id="add-rule-btn"
            class="text-sm bg-blue-100 text-blue-600 px-3 py-2 rounded-md hover:bg-blue-200">
            Add Rule
        </button>
    </div>

    <div class="overflow-x-auto overflow-y-visible">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rule Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action
                    </th>
                </tr>
            </thead>
            <tbody id="rules-container" class="bg-white divide-y divide-gray-100">
                @if(isset($discount) && $discount->rules)
                    @foreach($discount->rules as $rule)
                        <tr>
                            <!-- Rule Type -->
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <select data-base-name="rule_type" class="input-field rule-type-select w-full">
                                    <option value="product" @selected($rule->rule_type == 'product')>Product</option>
                                    <option value="category" @selected($rule->rule_type == 'category')>Category</option>
                                    <option value="subtotal" @selected($rule->rule_type == 'subtotal')>Subtotal</option>
                                    <option value="quantity" @selected($rule->rule_type == 'quantity')>Quantity</option>
                                </select>
                            </td>

                            <!-- Target -->
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap target-cell">
                                @if($rule->rule_type == 'category')
                                    <div class="custom-selector-wrapper" data-rule-type="category">
                                        <input type="hidden" data-base-name="rule_id" value="{{ $rule->rule_id }}">
                                        <div class="relative">
                                            <button type="button" class="selector-trigger w-full text-left bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <span class="selected-text {{ $rule->rule_id ? 'text-gray-900' : 'text-gray-500' }}">
                                                    {{ $rule->rule_id ? ($rule->category->name ?? 'Select category...') : 'Select category...' }}
                                                </span>
                                                <span class="float-right">▼</span>
                                            </button>
                                            <div class="selector-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                                <div class="p-2">
                                                    <input type="text" class="search-input w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Search categories...">
                                                </div>
                                                <div class="results-container max-h-60 overflow-y-auto">
                                                    <div class="loading-message p-4 text-center text-gray-500">Start typing to search...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($rule->rule_type == 'product')
                                    <div class="custom-selector-wrapper" data-rule-type="product">
                                        <input type="hidden" data-base-name="rule_id" value="{{ $rule->rule_id }}">
                                        <div class="relative">
                                            <button type="button" class="selector-trigger w-full text-left bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <span class="selected-text {{ $rule->rule_id ? 'text-gray-900' : 'text-gray-500' }}">
                                                    {{ $rule->rule_id ? ($rule->product->name ?? 'Select product...') : 'Select product...' }}
                                                </span>
                                                <span class="float-right">▼</span>
                                            </button>
                                            <div class="selector-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                                                <div class="p-2">
                                                    <input type="text" class="search-input w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Search products...">
                                                </div>
                                                <div class="results-container max-h-60 overflow-y-auto">
                                                    <div class="loading-message p-4 text-center text-gray-500">Start typing to search...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <input type="hidden" data-base-name="rule_id" value="">
                                @endif
                            </td>

                            <!-- Condition -->
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <select data-base-name="condition_type" class="input-field w-full">
                                    <option value="equals" @selected($rule->condition_type == 'equals')>Equals</option>
                                    <option value="not_equals" @selected($rule->condition_type == 'not_equals')>Not Equals
                                    </option>
                                    <option value="greater_than" @selected($rule->condition_type == 'greater_than')>Greater Than
                                    </option>
                                    <option value="less_than" @selected($rule->condition_type == 'less_than')>Less Than</option>
                                </select>
                            </td>

                            <!-- Value -->
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                <input type="text" data-base-name="rule_value" class="input-field w-full"
                                    value="{{ $rule->rule_value }}">
                            </td>

                            <!-- Remove -->
                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap text-center">
                                <button type="button" class="remove-rule text-red-600 hover:text-red-800">
                                    <span class="material-icons-outlined">delete</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Template for new rules -->
    <template id="rules-template">
        <tr>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <select data-base-name="rule_type" class="input-field rule-type-select w-full">
                    <option value="product">Product</option>
                    <option value="category">Category</option>
                    <option value="subtotal">Subtotal</option>
                    <option value="quantity">Quantity</option>
                </select>
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap target-cell">
                <input type="hidden" data-base-name="rule_id" value="">
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <select data-base-name="condition_type" class="input-field w-full">
                    <option value="equals">Equals</option>
                    <option value="not_equals">Not Equals</option>
                    <option value="greater_than">Greater Than</option>
                    <option value="less_than">Less Than</option>
                </select>
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                <input type="text" data-base-name="rule_value" class="input-field w-full">
            </td>
            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap text-center">
                <button type="button" class="remove-rule text-red-600 hover:text-red-800">
                    <span class="material-icons-outlined">delete</span>
                </button>
            </td>
        </tr>
    </template>
</div>




<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ruleContainer = document.getElementById('rules-container');
        const ruleTemplate = document.getElementById('rules-template');
        let ruleIndex = {{ isset($discount) && $discount->rules ? $discount->rules->count() : 0 }};

        function updateRuleIndexes() {
            ruleContainer.querySelectorAll('tr').forEach((tr, index) => {
                tr.querySelectorAll('[data-base-name]').forEach(el => {
                    const base = el.getAttribute('data-base-name');
                    el.name = `rules[${index}][${base}]`;
                });
            });
        }

        function attachRuleRemoveHandlers() {
            ruleContainer.querySelectorAll('.remove-rule').forEach(btn => {
                btn.onclick = () => {
                    btn.closest('tr').remove();
                    updateRuleIndexes();
                };
            });
        }

        function initRuleTypeHandler(tr, index) {
            const ruleTypeSelect = tr.querySelector('.rule-type-select');
            const targetCell = tr.querySelector('.target-cell');

            function updateTargetField() {
                const ruleType = ruleTypeSelect.value;
                const needsTarget = ['product', 'category'].includes(ruleType);

                if (needsTarget) {
                    const table = ruleType === 'category' ? 'food_categories' : 'food_items';
                    const statusColumn = ruleType === 'category' ? 'status' : 'is_active';
                    const placeholder = `Select ${ruleType}...`;
                    
                    // Create a unique component ID for this selector
                    const componentId = `rule_${index}_${ruleType}_selector`;
                    
                    targetCell.innerHTML = `
                        <div class="advanced-selector-wrapper" data-component-id="${componentId}">
                            <input type="hidden" name="rules[${index}][rule_id]" id="${componentId}_input" value="">
                            <div class="relative">
                                <button type="button" class="advanced-selector-trigger w-full text-left bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        data-component-id="${componentId}">
                                    <span class="selected-text text-gray-500">${placeholder}</span>
                                    <span class="float-right">▼</span>
                                </button>
                                <div class="advanced-selector-dropdown fixed z-50 w-auto mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden" 
                                     data-component-id="${componentId}">
                                    <div class="p-2">
                                        <input type="text" class="search-input w-full px-3 py-2 border border-gray-300 rounded-md text-sm" 
                                               placeholder="Search ${ruleType}s..." data-component-id="${componentId}">
                                    </div>
                                    <div class="results-container max-h-60 overflow-y-auto" data-component-id="${componentId}">
                                        <div class="loading-message p-4 text-center text-gray-500">Start typing to search...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Initialize the advanced selector functionality
                    initAdvancedSelectorReplacement(targetCell.querySelector('.advanced-selector-wrapper'), table, statusColumn);
                } else {
                    targetCell.innerHTML = `<input type="hidden" name="rules[${index}][rule_id]" value="">`;
                }
            }

            updateTargetField();
            ruleTypeSelect.addEventListener('change', updateTargetField);
        }

        function initAdvancedSelectorReplacement(wrapper, table, statusColumn) {
            const componentId = wrapper.dataset.componentId;
            const trigger = wrapper.querySelector('.advanced-selector-trigger');
            const dropdown = wrapper.querySelector('.advanced-selector-dropdown');
            const searchInput = wrapper.querySelector('.search-input');
            const resultsContainer = wrapper.querySelector('.results-container');
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            const selectedText = wrapper.querySelector('.selected-text');
            
            let searchTimeout;
            let currentPage = 1;
            let hasMore = true;

            // Toggle dropdown
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) {
                    searchInput.focus();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Search functionality
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    hasMore = true;
                    performSearch(this.value);
                }, 300);
            });

            function performSearch(searchTerm) {
                if (searchTerm.length < 1) {
                    resultsContainer.innerHTML = '<div class="loading-message p-4 text-center text-gray-500">Start typing to search...</div>';
                    return;
                }

                resultsContainer.innerHTML = '<div class="loading-message p-4 text-center text-gray-500">Searching...</div>';

                const params = new URLSearchParams({
                    table: table,
                    search_column: 'name',
                    value_column: 'id',
                    display_column: 'name',
                    search: searchTerm,
                    page: currentPage,
                    per_page: 10,
                    order_by: 'name',
                    order_direction: 'asc',
                    filters: JSON.stringify([{
                        column: statusColumn,
                        operator: '=',
                        value: 1
                    }])
                });

                fetch(`/api/advanced-selector-data?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.items) {
                        displayResults(data.items, currentPage === 1);
                        hasMore = data.has_more || false;
                    } else {
                        console.error('API Error:', data);
                        resultsContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No results found</div>';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    resultsContainer.innerHTML = '<div class="p-4 text-center text-red-500">Error loading results</div>';
                });
            }

            function displayResults(items, clearFirst = true) {
                if (clearFirst) {
                    resultsContainer.innerHTML = '';
                }

                items.forEach(item => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'result-item p-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100';
                    resultItem.textContent = item.name;
                    resultItem.dataset.value = item.id;
                    
                    resultItem.addEventListener('click', function() {
                        hiddenInput.value = item.id;
                        selectedText.textContent = item.name;
                        selectedText.classList.remove('text-gray-500');
                        selectedText.classList.add('text-gray-900');
                        dropdown.classList.add('hidden');
                    });
                    
                    resultsContainer.appendChild(resultItem);
                });
            }
        }

        document.getElementById('add-rule-btn').addEventListener('click', function () {
            const clone = ruleTemplate.content.cloneNode(true).querySelector('tr');
            ruleContainer.appendChild(clone);
            
            const newIndex = ruleIndex++;
            initRuleTypeHandler(clone, newIndex);
            updateRuleIndexes();
            attachRuleRemoveHandlers();
        });

        // Initialize existing rules
        ruleContainer.querySelectorAll('tr').forEach((tr, index) => {
            const ruleTypeSelect = tr.querySelector('.rule-type-select');
            if (ruleTypeSelect) {
                initRuleTypeHandler(tr, index);
            }
        });

        updateRuleIndexes();
        attachRuleRemoveHandlers();
    });
</script>