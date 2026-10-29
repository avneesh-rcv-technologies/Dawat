@props([
    'name' => 'items',
    'table' => '',
    'searchColumn' => 'name',
    'valueColumn' => 'id',
    'displayColumn' => 'name',
    'required' => false,
    'multiple' => true,
    'selected' => [],
    'placeholder' => 'Select items...',
    'label' => 'Select Items',
    'apiRoute' => '',
    'additionalColumns' => [],
    'filters' => [],
    'orderBy' => 'name',
    'orderDirection' => 'asc',
    'ajax' => false
])

@php
    $componentId = 'selector_' . uniqid();
    $selectedItems = is_array($selected) ? $selected : (is_string($selected) ? explode(',', $selected) : []);
    $selectedItems = array_filter($selectedItems);

@endphp

<div class="advanced-selector-wrapper" data-component-id="{{ $componentId }}">
    <label for="{{ $componentId }}_input" class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    <div class="relative">
        <!-- Main Input Field -->
        <div class="selector-input-container relative">
            <input 
                type="text" 
                id="{{ $componentId }}_input"
                class="input-field"
                placeholder="{{ $placeholder }}"
                onkeyup="searchInInput('{{ $componentId }}')"
                onfocus="showSearchSuggestions('{{ $componentId }}')"
                autocomplete="off"
            >
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <button type="button" onclick="openSelectorModal('{{ $componentId }}')" class="p-1.5 hover:bg-gray-100 rounded cursor-pointer">
                     <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <!-- Search Suggestions Dropdown -->
        <div id="{{ $componentId }}_suggestions" class="suggestions-dropdown absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden max-h-60 overflow-y-auto">
            <div id="{{ $componentId }}_suggestions_loading" class="text-center py-4 hidden">
                <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                <span class="ml-2 text-sm text-gray-600">Searching...</span>
            </div>
            <div id="{{ $componentId }}_suggestions_list" class="suggestions-list"></div>
            <div id="{{ $componentId }}_suggestions_empty" class="text-center py-4 text-gray-500 text-sm hidden">
                No results found
            </div>
        </div>

        <!-- Selected Items Display -->
        <div id="{{ $componentId }}_selected_display" class="selected-items-display mt-2 hidden">
            <div class="flex flex-wrap gap-2" id="{{ $componentId }}_selected_tags"></div>
        </div>

        <!-- Hidden Inputs for Form Submission -->
        <div id="{{ $componentId }}_hidden_inputs"></div>
    </div>

    <!-- Modal -->
    <div id="{{ $componentId }}_modal" class="selector-modal fixed inset-0 bg-gray-600/20 bg-opacity-50 hidden {{ $ajax ? 'z-[9999]' : 'z-50' }}">
        <div class="flex items-center justify-center {{ $ajax ? 'min-h-auto' : 'min-h-screen' }}  p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full {{ $ajax ? 'max-h-[550px]' : 'max-h-[80vh]' }} flex flex-col">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">{{ $label }}</h3>
                    <button type="button" onclick="closeSelectorModal('{{ $componentId }}')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="{{ $componentId }}_search"
                            class="input-field"
                            placeholder="Search {{ strtolower($label) }}..."
                            onkeyup="searchItems('{{ $componentId }}')"
                        >
                       
                    </div>
                </div>

                <!-- Items List -->
                <div class="flex-1 overflow-y-auto px-6 py-4" id="{{ $componentId }}_scroll_container" onscroll="handleScroll('{{ $componentId }}')">
                    <div id="{{ $componentId }}_loading" class="text-center py-8 hidden">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        <p class="mt-2 text-gray-600">Loading...</p>
                    </div>
                    
                    <div id="{{ $componentId }}_items_container" class="space-y-2">
                        <!-- Items will be loaded here -->
                    </div>
                    
                    <div id="{{ $componentId }}_infinite_loading" class="text-center py-4 hidden">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                        <p class="mt-2 text-gray-600 text-sm">Loading more...</p>
                    </div>
                    
                    <div id="{{ $componentId }}_no_results" class="text-center py-8 text-gray-500 hidden">
                        No items found
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <span id="{{ $componentId }}_selected_count">0</span> selected
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" onclick="clearSelection('{{ $componentId }}')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Clear All
                        </button>
                        <button type="button" onclick="applySelectorSelection('{{ $componentId }}')" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Apply Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Component configuration
window.selectorConfigs = window.selectorConfigs || {};
window.selectorConfigs['{{ $componentId }}'] = {
    name: '{{ $name }}',
    table: '{{ $table }}',
    searchColumn: '{{ $searchColumn }}',
    valueColumn: '{{ $valueColumn }}',
    displayColumn: '{{ $displayColumn }}',
    multiple: {{ $multiple ? 'true' : 'false' }},
    apiRoute: '{{ $apiRoute }}',
    additionalColumns: @json($additionalColumns),
    filters: @json($filters),
    orderBy: '{{ $orderBy }}',
    orderDirection: '{{ $orderDirection }}',
    selectedItems: @json($selectedItems).map(item => item.toString()),
    selectedItemsDetails: {},
    currentPage: 1,
    hasMore: true,
    isLoading: false,
    searchTimeout: null,
    inputSearchTimeout: null,
    ajax: {{ $ajax ? 'true' : 'false' }}
};

// Debug logging
console.log('Selector Config for {{ $componentId }}:', window.selectorConfigs['{{ $componentId }}']);
</script>


<style>
.selector-modal {
    backdrop-filter: blur(4px);
}

.selected-items-display .selected-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background-color: #3B82F6;
    color: white;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.selected-tag .remove-btn {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.selected-tag .remove-btn:hover {
    opacity: 1;
}

.item-checkbox {
    transition: all 0.2s;
}

.item-row {
    transition: background-color 0.2s;
}

.item-row:hover {
    background-color: #F3F4F6;
}

.item-row.selected {
    background-color: #EBF8FF;
    border-color: #3B82F6;
}

.suggestions-dropdown {
    border-top: none;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

.suggestion-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #E5E7EB;
    transition: background-color 0.2s;
}

.suggestion-item:hover {
    background-color: #F3F4F6;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.selector-text-wrap {
    min-width: 0;
    overflow-wrap: anywhere;
    word-break: break-word;
    white-space: normal;
}

.selector-meta-wrap {
    min-width: 0;
    overflow-wrap: anywhere;
    word-break: break-word;
    white-space: normal;
}
</style>



<script>
function initializeSelector(componentId) {
    const config = window.selectorConfigs[componentId];
    if (!config) return;
    
    // Initialize selectedItemsDetails if not exists
    config.selectedItemsDetails = config.selectedItemsDetails || {};


    
    // Load selected items details first if we have pre-selected items
    if (config.selectedItems.length > 0) {
        loadSelectedItemsDetails(componentId).then(() => {
            updateSelectedDisplay(componentId);
            updateHiddenInputs(componentId);
        });
    } else {
        updateSelectedDisplay(componentId);
        updateHiddenInputs(componentId);
    }
    
    // Load initial items for modal
    loadItems(componentId);
    
    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        const suggestions = document.getElementById(componentId + '_suggestions');
        const input = document.getElementById(componentId + '_input');
        if (suggestions && !suggestions.contains(e.target) && e.target !== input) {
            suggestions.classList.add('hidden');
        }
    });
}

function openSelectorModal(componentId) {
    const config = window.selectorConfigs[componentId];
    const modal = document.getElementById(componentId + '_modal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Reset modal state
    config.currentPage = 1;
    config.hasMore = true;
    document.getElementById(componentId + '_search').value = '';
    
    // Load fresh items and update selection states
    loadItems(componentId).then(() => {
        updateSelectedCount(componentId);
        updateItemCheckboxes(componentId);
    });
    
    // Focus search input
    setTimeout(() => {
        document.getElementById(componentId + '_search').focus();
    }, 100);
}

function closeSelectorModal(componentId) {
    const modal = document.getElementById(componentId + '_modal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function searchItems(componentId) {
    const config = window.selectorConfigs[componentId];
    const searchInput = document.getElementById(componentId + '_search');
    
    clearTimeout(config.searchTimeout);
    config.searchTimeout = setTimeout(() => {
        config.currentPage = 1;
        config.hasMore = true;
        loadItems(componentId, searchInput.value);
    }, 300);
}

async function loadItems(componentId, search = '', append = false) {
    const config = window.selectorConfigs[componentId];
    
    if (config.isLoading || (!append && !config.hasMore && config.currentPage > 1)) return;
    
    config.isLoading = true;
    
    if (append) {
        document.getElementById(componentId + '_infinite_loading').classList.remove('hidden');
    } else {
        showLoading(componentId, true);
    }
    
    try {
        const params = new URLSearchParams({
            table: config.table,
            search_column: config.searchColumn,
            value_column: config.valueColumn,
            display_column: config.displayColumn,
            search: search,
            page: config.currentPage,
            per_page: 20,
            order_by: config.orderBy,
            order_direction: config.orderDirection,
            additional_columns: JSON.stringify(config.additionalColumns),
            filters: JSON.stringify(config.filters)
        });
        
        const response = await fetch(`/api/advanced-selector-data?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        if (data.success) {
            displayItems(componentId, data.items, append);
            config.hasMore = data.has_more;
            config.currentPage = Number(data.current_page) + 1;
        } else {
            showError(componentId, data.message || 'Failed to load items');
        }
    } catch (error) {
        console.error('Error loading items:', error);
        showError(componentId, 'Failed to load items');
    } finally {
        config.isLoading = false;
        if (!append) {
            showLoading(componentId, false);
        }
    }
}

function displayItems(componentId, items, append = false) {
    const config = window.selectorConfigs[componentId];
    
    
    const container = document.getElementById(componentId + '_items_container');
    const infiniteLoading = document.getElementById(componentId + '_infinite_loading');
    const noResults = document.getElementById(componentId + '_no_results');
    
    if (!append) {
        container.innerHTML = '';
    }
    
    if (items.length === 0 && !append) {
        noResults.classList.remove('hidden');
        infiniteLoading.classList.add('hidden');
        return;
    }
    
    noResults.classList.add('hidden');
    

    items.forEach(item => {
     
        
        // const itemValue = item[config.valueColumn].toString();
        
        
        const itemValue = item?.[config.valueColumn];

        if (itemValue != null) {
            const valueStr = String(itemValue);
        }

        const isSelected = config.selectedItems.includes(itemValue);
        const itemElement = createItemElement(componentId, item, isSelected);
        container.appendChild(itemElement);
        
        // Store item details for selected items
        if (isSelected && !config.selectedItemsDetails[itemValue]) {
            config.selectedItemsDetails = config.selectedItemsDetails || {};
            config.selectedItemsDetails[itemValue] = item;
        }
    });
    
    // Hide infinite loading indicator
    infiniteLoading.classList.add('hidden');
}

function createItemElement(componentId, item, isSelected) {
    const config = window.selectorConfigs[componentId];
    const div = document.createElement('div');
    div.className = `item-row flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer ${isSelected ? 'selected' : ''}`;
    div.setAttribute('data-value', item[config.valueColumn]);
    div.onclick = () => toggleItemSelection(componentId, item[config.valueColumn], item);
    
    const checkbox = config.multiple ? 
        `<input type="checkbox" class="item-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded mr-3" ${isSelected ? 'checked' : ''} onchange="event.stopPropagation()">` :
        `<input type="radio" name="${componentId}_radio" class="item-checkbox h-4 w-4 text-blue-600 border-gray-300 mr-3" ${isSelected ? 'checked' : ''} onchange="event.stopPropagation()">`;
    
    let additionalInfo = '';
    if (config.additionalColumns.length > 0) {
        const additionalData = config.additionalColumns.map(col => item[col] || '').filter(val => val).join(' • ');
        if (additionalData) {
            additionalInfo = `<div class="text-xs text-gray-400 mt-1">${additionalData}</div>`;
        }
    }
    
    div.innerHTML = `
        ${checkbox}
        <div class="flex-1 min-w-0 selector-text-wrap">
            <div class="font-medium text-gray-900 leading-snug selector-text-wrap">${item[config.displayColumn]}</div>
            ${additionalInfo}
        </div>
    `;
    
    return div;
}

function toggleItemSelection(componentId, value, item) {
    const config = window.selectorConfigs[componentId];
    const valueStr = value.toString();
    
    // Initialize selectedItemsDetails if not exists
    config.selectedItemsDetails = config.selectedItemsDetails || {};
    
    if (config.multiple) {
        const index = config.selectedItems.indexOf(valueStr);
        if (index > -1) {
            // Remove from selection
            config.selectedItems.splice(index, 1);
            delete config.selectedItemsDetails[valueStr];
        } else {
            // Add to selection
            config.selectedItems.push(valueStr);
            config.selectedItemsDetails[valueStr] = item;
        }
    } else {
        // Single selection
        if (config.selectedItems.includes(valueStr)) {
            // Deselect if already selected
            config.selectedItems = [];
            config.selectedItemsDetails = {};
        } else {
            // Select new item
            config.selectedItems = [valueStr];
            config.selectedItemsDetails = {};
            config.selectedItemsDetails[valueStr] = item;
        }
    }
    
    updateSelectedCount(componentId);
    updateItemCheckboxes(componentId);
    
    // Update display in real-time
    updateSelectedDisplay(componentId);
    updateHiddenInputs(componentId);
}

function updateItemCheckboxes(componentId) {
    const config = window.selectorConfigs[componentId];
    const container = document.getElementById(componentId + '_items_container');
    const items = container.querySelectorAll('.item-row');
    
    items.forEach(itemRow => {
        const checkbox = itemRow.querySelector('.item-checkbox');
        const value = itemRow.getAttribute('data-value');
        
        if (value) {
            const isSelected = config.selectedItems.includes(value.toString());
            checkbox.checked = isSelected;
            itemRow.classList.toggle('selected', isSelected);
        }
    });
}

function updateSelectedCount(componentId) {
    const config = window.selectorConfigs[componentId];
    const countElement = document.getElementById(componentId + '_selected_count');
    countElement.textContent = config.selectedItems.length;
}

function clearSelection(componentId) {
    const config = window.selectorConfigs[componentId];
    config.selectedItems = [];
    config.selectedItemsDetails = {};
    updateSelectedCount(componentId);
    updateItemCheckboxes(componentId);
    updateSelectedDisplay(componentId);
    updateHiddenInputs(componentId);
}

function handleScroll(componentId) {
    const config = window.selectorConfigs[componentId];
    const container = document.getElementById(componentId + '_scroll_container');
    
    // Check if scrolled to bottom
    if (container.scrollTop + container.clientHeight >= container.scrollHeight - 5) {
        if (config.hasMore && !config.isLoading) {
            const searchValue = document.getElementById(componentId + '_search').value;
            loadItems(componentId, searchValue, true);
        }
    }
}

function searchInInput(componentId) {
    const config = window.selectorConfigs[componentId];
    const input = document.getElementById(componentId + '_input');
    const suggestions = document.getElementById(componentId + '_suggestions');
    
    clearTimeout(config.inputSearchTimeout);
    config.inputSearchTimeout = setTimeout(() => {
        if (input.value.length >= 1) {
            loadSuggestions(componentId, input.value);
            suggestions.classList.remove('hidden');
        } else {
            suggestions.classList.add('hidden');
        }
    }, 300);
}

function showSearchSuggestions(componentId) {
    const input = document.getElementById(componentId + '_input');
    const suggestions = document.getElementById(componentId + '_suggestions');
    
    if (input.value.length >= 1) {
        loadSuggestions(componentId, input.value);
        suggestions.classList.remove('hidden');
    }
}

async function loadSuggestions(componentId, search) {
    const config = window.selectorConfigs[componentId];
    const loadingEl = document.getElementById(componentId + '_suggestions_loading');
    const listEl = document.getElementById(componentId + '_suggestions_list');
    const emptyEl = document.getElementById(componentId + '_suggestions_empty');
    
    loadingEl.classList.remove('hidden');
    listEl.innerHTML = '';
    emptyEl.classList.add('hidden');
    
    try {

     
        
        const params = new URLSearchParams({
            table: config.table,
            search_column: config.searchColumn,
            value_column: config.valueColumn,
            display_column: config.displayColumn,
            search: search,
            page: 1,
            per_page: 10,
            order_by: config.orderBy,
            order_direction: config.orderDirection,
            additional_columns: JSON.stringify(config.additionalColumns),
            filters: JSON.stringify(config.filters)
        });
       
        
        
        const response = await fetch(`/api/advanced-selector-data?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();

        
        if (data.success && data.items.length > 0) {
            data.items.forEach(item => {
                const suggestionEl = createSuggestionElement(componentId, item);
                listEl.appendChild(suggestionEl);
            });
        } else {
            emptyEl.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading suggestions:', error);
        emptyEl.classList.remove('hidden');
    } finally {
        loadingEl.classList.add('hidden');
    }
}

function createSuggestionElement(componentId, item) {
    const config = window.selectorConfigs[componentId];
    const div = document.createElement('div');
    div.className = 'suggestion-item';
    div.onclick = () => selectFromSuggestion(componentId, item);
    
    let additionalInfo = '';
    if (config.additionalColumns.length > 0) {
        additionalInfo = config.additionalColumns.map(col => 
            item[col] || ''
        ).filter(val => val).join(' • ');
        if (additionalInfo) {
            additionalInfo = `<div class="text-xs text-gray-400 mt-1">${additionalInfo}</div>`;
        }
    }
    
    div.innerHTML = `
        <div class="font-medium text-gray-900 leading-snug selector-text-wrap">${item[config.displayColumn]}</div>
        ${additionalInfo}
    `;
    
    return div;
}

function selectFromSuggestion(componentId, item) {
    const config = window.selectorConfigs[componentId];
    const input = document.getElementById(componentId + '_input');
    const suggestions = document.getElementById(componentId + '_suggestions');
    const valueStr = item[config.valueColumn].toString();
    
    config.selectedItemsDetails = config.selectedItemsDetails || {};
    
    if (config.multiple) {
        if (!config.selectedItems.includes(valueStr)) {
            config.selectedItems.push(valueStr);
            config.selectedItemsDetails[valueStr] = item;
            updateSelectedDisplay(componentId);
            updateHiddenInputs(componentId);
            updateSelectedCount(componentId);
        }
        input.value = ``;
    } else {
        config.selectedItems = [valueStr];
        config.selectedItemsDetails = {};
        config.selectedItemsDetails[valueStr] = item;
        updateSelectedDisplay(componentId);
        updateHiddenInputs(componentId);
        updateSelectedCount(componentId);
    }
    
    suggestions.classList.add('hidden');
    
    // Update modal checkboxes if modal is open
    const modal = document.getElementById(componentId + '_modal');
    if (modal && !modal.classList.contains('hidden')) {
        updateItemCheckboxes(componentId);
    }
}

async function loadSelectedItemsDetails(componentId) {
    const config = window.selectorConfigs[componentId];
    
    if (config.selectedItems.length === 0) {
        return Promise.resolve();
    }
    
    try {
        const response = await fetch('/api/advanced-selector-selected', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                table: config.table,
                value_column: config.valueColumn,
                display_column: config.displayColumn,
                values: config.selectedItems,
                additional_columns: JSON.stringify(config.additionalColumns)
            })
        });
        
        const data = await response.json();

        
        
        if (data.success) {
            config.selectedItemsDetails = config.selectedItemsDetails || {};
            data.items.forEach(item => {
                config.selectedItemsDetails[item[config.valueColumn].toString()] = item;
            });
            return Promise.resolve();
        } else {
            console.error('Failed to load selected items:', data.message);
            return Promise.reject(new Error(data.message));
        }
    } catch (error) {
        console.error('Error loading selected items details:', error);
        return Promise.reject(error);
    }
}

function applySelectorSelection(componentId) {
    const config = window.selectorConfigs[componentId];
    updateSelectedDisplay(componentId);
    updateHiddenInputs(componentId);
    closeSelectorModal(componentId);
    
    // Clear the input search field
    const input = document.getElementById(componentId + '_input');
    if (config.multiple && config.selectedItems.length > 0) {
        input.value = ``;
    }
}

function updateSelectedDisplay(componentId) {
    const config = window.selectorConfigs[componentId];
    const input = document.getElementById(componentId + '_input');
    const display = document.getElementById(componentId + '_selected_display');
    const tagsContainer = document.getElementById(componentId + '_selected_tags');
    
    if (!input || !display || !tagsContainer) {
        console.warn('Required elements not found for', componentId);
        return;
    }
    
    if (config.selectedItems.length === 0) {
        input.value = '';
        display.classList.add('hidden');
        return;
    }
    
    if (config.multiple) {
        input.value = ``;
        display.classList.remove('hidden');
        
        // Create tags for selected items
        tagsContainer.innerHTML = '';
        config.selectedItems.forEach(value => {
            const itemDetails = config.selectedItemsDetails && config.selectedItemsDetails[value];
            const displayText = itemDetails ? itemDetails[config.displayColumn] : value;
            
            const tag = document.createElement('span');
            tag.className = 'selected-tag';
            tag.innerHTML = `
                ${displayText}
                <span class="remove-btn" onclick="removeSelectedItem('${componentId}', '${value}', event)">&times;</span>
            `;
            tagsContainer.appendChild(tag);
        });
    } else {
        const value = config.selectedItems[0];
        const itemDetails = config.selectedItemsDetails && config.selectedItemsDetails[value];
        const displayValue = itemDetails ? itemDetails[config.displayColumn] : value;
        input.value = displayValue;
        display.classList.add('hidden');
    }
}

function removeSelectedItem(componentId, value, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const config = window.selectorConfigs[componentId];
    const valueStr = value.toString();
    const index = config.selectedItems.indexOf(valueStr);
    
    if (index > -1) {
        config.selectedItems.splice(index, 1);
        if (config.selectedItemsDetails && config.selectedItemsDetails[valueStr]) {
            delete config.selectedItemsDetails[valueStr];
        }
        updateSelectedDisplay(componentId);
        updateHiddenInputs(componentId);
        updateSelectedCount(componentId);
        
        // Update checkboxes in modal if it's open
        const modal = document.getElementById(componentId + '_modal');
        if (modal && !modal.classList.contains('hidden')) {
            updateItemCheckboxes(componentId);
        }
    }
}

function updateHiddenInputs(componentId) {
    const config = window.selectorConfigs[componentId];
    const container = document.getElementById(componentId + '_hidden_inputs');
    
    container.innerHTML = '';
    
    if (config.multiple) {
        config.selectedItems.forEach(value => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = config.name + '[]';
            input.value = value;
            container.appendChild(input);
        });
    } else if (config.selectedItems.length > 0) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = config.name;
        input.value = config.selectedItems[0];
        container.appendChild(input);
    }
}

function showLoading(componentId, show) {
    const loading = document.getElementById(componentId + '_loading');
    if (show) {
        loading.classList.remove('hidden');
    } else {
        loading.classList.add('hidden');
    }
}

function showError(componentId, message) {
    console.error('Selector Error:', message);
    // You can implement error display here
}

// Debug function
function debugSelector(componentId) {
    const config = window.selectorConfigs[componentId];
    console.log('=== Selector Debug Info ===');
    console.log('Component ID:', componentId);
    console.log('Selected Items:', config.selectedItems);
    console.log('Selected Items Details:', config.selectedItemsDetails);
    console.log('Config:', config);
    
    const input = document.getElementById(componentId + '_input');
    console.log('Input Value:', input ? input.value : 'Input not found');
    
    const tags = document.getElementById(componentId + '_selected_tags');
    console.log('Tags HTML:', tags ? tags.innerHTML : 'Tags container not found');
    
    const hiddenInputs = document.getElementById(componentId + '_hidden_inputs');
    console.log('Hidden Inputs:', hiddenInputs ? hiddenInputs.innerHTML : 'Hidden inputs container not found');
    
    console.log('=== End Debug Info ===');
}

// Make debug function globally available
window.debugSelector = debugSelector;



// Initialize component - must be after all function definitions
@if($ajax)
    // For AJAX-loaded components, initialize immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeSelector('{{ $componentId }}');
        });
    } else {
        // DOM already loaded, initialize now
        initializeSelector('{{ $componentId }}');
    }
@else
    // For regular page loads, wait for DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeSelector('{{ $componentId }}');
    });
@endif
</script>
