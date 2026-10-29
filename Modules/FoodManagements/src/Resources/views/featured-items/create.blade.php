@extends('backend::layouts.app')

@section('title', 'Create Featured Item')

@section('breadcrumbs')
    @php
        $title = 'Create Featured Item';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Food Management', 'url' => '#'],
            ['label' => 'Featured Items', 'url' => route('admin.foodmanagements.featured-items.index')],
            ['label' => $title],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <div class="mx-auto">
        <div class="bg-white p-8">
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Create Featured Item</h3>
                <p class="text-gray-600">Create a new featured item to showcase food items or categories to customers</p>
            </div>

            <form method="POST" action="{{ route('admin.foodmanagements.featured-items.store') }}">
                @csrf

                <div class="space-y-8">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Basic Information</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Title <span class="text-red-500">*</span></label>
                            <input name="title" type="text" class="input-field @error('title') input-error @enderror"
                                value="{{ old('title') }}" placeholder="Enter featured item title">
                            @error('title')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4" 
                                class="input-field @error('description') input-error @enderror" 
                                placeholder="Enter description for this featured item">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="input-field @error('status') input-error @enderror">
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Featured Type Selection -->
                    <div class="space-y-6">
                        <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Featured Type</h4>
                        
                        <div class="form-group">
                            <label class="form-label">What do you want to feature? <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="type-option" data-type="food_item">
                                    <input type="radio" name="featured_type" value="food_item" id="type_food_item" 
                                           class="sr-only" {{ old('featured_type') === 'food_item' ? 'checked' : '' }}>
                                    <label for="type_food_item" class="type-label">
                                        <div class="type-content">
                                            <div class="type-icon">🍕</div>
                                            <h4 class="type-title">Food Items</h4>
                                            <p class="type-description">Feature specific food items</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="type-option" data-type="category">
                                    <input type="radio" name="featured_type" value="category" id="type_category" 
                                           class="sr-only" {{ old('featured_type') === 'category' ? 'checked' : '' }}>
                                    <label for="type_category" class="type-label">
                                        <div class="type-content">
                                            <div class="type-icon">📂</div>
                                            <h4 class="type-title">Categories</h4>
                                            <p class="type-description">Feature entire categories</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('featured_type')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Selection Method -->
                    <div class="space-y-6" id="selection-method-section" style="display: none;">
                        <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Selection Method</h4>
                        
                        <div class="form-group">
                            <label class="form-label">How should items be selected? <span class="text-red-500">*</span></label>
                            <select name="selection_method" id="selection_method" class="input-field @error('selection_method') input-error @enderror">
                                <option value="">Choose selection method...</option>
                                @foreach(\Modules\FoodManagements\Models\FeaturedItem::getSelectionMethods() as $key => $label)
                                    <option value="{{ $key }}" {{ old('selection_method') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selection_method')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Item Limit for Automatic Methods -->
                        <div class="form-group" id="item-limit-section" style="display: none;">
                            <label class="form-label">Number of Items to Show</label>
                            <input name="item_limit" type="number" min="1" max="20" 
                                class="input-field @error('item_limit') input-error @enderror"
                                value="{{ old('item_limit', 5) }}" placeholder="5">
                            <p class="text-sm text-gray-500 mt-1">Maximum 20 items</p>
                            @error('item_limit')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Custom Selection Section -->
                    <div class="space-y-6" id="custom-selection-section" style="display: none;">
                        <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Custom Selection</h4>
                        
                        <!-- Food Items Selection -->
                        <div class="form-group" id="food-items-selection" style="display: none;">
                            <label class="form-label">Search & Select Food Items</label>
                            <div class="dropdown-container">
                                <div class="dropdown-search-box">
                                    <input type="text" 
                                           class="dropdown-search" 
                                           id="itemSearch" 
                                           placeholder="Search food items..."
                                           autocomplete="off">
                                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                
                                <div class="dropdown-options" id="itemOptions">
                                    @foreach($foodItems as $item)
                                        <div class="dropdown-option item-option" 
                                             data-value="{{ $item->id }}"
                                             data-name="{{ strtolower($item->name) }}"
                                             data-price="{{ $item->sale_price }}"
                                             data-categories="{{ $item->categories->pluck('name')->join(', ') }}">
                                            <input type="checkbox" 
                                                   name="food_items[]" 
                                                   value="{{ $item->id }}"
                                                   id="item_{{ $item->id }}"
                                                   {{ in_array($item->id, old('food_items', [])) ? 'checked' : '' }}>
                                            <label for="item_{{ $item->id }}" class="dropdown-label">
                                                <div class="item-info">
                                                    <span class="item-name">{{ $item->name }}</span>
                                                    <span class="item-price">${{ number_format($item->sale_price, 2) }}</span>
                                                </div>
                                                @if($item->categories->count() > 0)
                                                    <div class="item-categories">
                                                        {{ $item->categories->pluck('name')->join(', ') }}
                                                    </div>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="selected-items" id="selectedItems">
                                    @if(old('food_items'))
                                        @foreach(old('food_items') as $itemId)
                                            @php
                                                $item = $foodItems->firstWhere('id', $itemId);
                                            @endphp
                                            @if($item)
                                                <span class="selected-tag" data-value="{{ $item->id }}">
                                                    {{ $item->name }} - ${{ number_format($item->sale_price, 2) }}
                                                    <button type="button" class="remove-tag" data-target="item_{{ $item->id }}">×</button>
                                                </span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @error('food_items')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                            <div class="text-sm text-gray-600 mt-2">
                                <span id="selectedItemsCount">{{ count(old('food_items', [])) }}</span> items selected
                            </div>
                        </div>

                        <!-- Food Categories Selection -->
                        <div class="form-group" id="food-categories-selection" style="display: none;">
                            <label class="form-label">Select Categories</label>
                            <div class="dropdown-container">
                                <div class="dropdown-search-box">
                                    <input type="text" 
                                           class="dropdown-search" 
                                           id="categorySearch" 
                                           placeholder="Search categories..."
                                           autocomplete="off">
                                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                
                                <div class="dropdown-options" id="categoryOptions">
                                    @foreach($categories as $category)
                                        <div class="dropdown-option category-option" 
                                             data-value="{{ $category['id'] }}"
                                             data-name="{{ strtolower($category['name']) }}"
                                             data-type="{{ $category['type'] }}">
                                            <input type="checkbox" 
                                                   name="food_categories[]" 
                                                   value="{{ $category['id'] }}"
                                                   id="cat_{{ $category['id'] }}"
                                                   {{ in_array($category['id'], old('food_categories', [])) ? 'checked' : '' }}>
                                            <label for="cat_{{ $category['id'] }}" class="dropdown-label">
                                                <span class="category-badge category-{{ $category['type'] }}">
                                                    {{ ucfirst(str_replace('_', ' ', $category['type'])) }}
                                                </span>
                                                {{ $category['name'] }}
                                            </label>
                                        </div>
                                        
                                        @if(count($category['subcategories']) > 0)
                                            @foreach($category['subcategories'] as $subcategory)
                                                <div class="dropdown-option subcategory-option" 
                                                     data-value="{{ $subcategory['id'] }}"
                                                     data-name="{{ strtolower($subcategory['name']) }}"
                                                     data-type="{{ $subcategory['type'] }}">
                                                    <input type="checkbox" 
                                                           name="food_categories[]" 
                                                           value="{{ $subcategory['id'] }}"
                                                           id="cat_{{ $subcategory['id'] }}"
                                                           {{ in_array($subcategory['id'], old('food_categories', [])) ? 'checked' : '' }}>
                                                    <label for="cat_{{ $subcategory['id'] }}" class="dropdown-label subcategory-label">
                                                        <span class="category-badge category-{{ $subcategory['type'] }}">
                                                            {{ ucfirst(str_replace('_', ' ', $subcategory['type'])) }}
                                                        </span>
                                                        └─ {{ $subcategory['name'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                                
                                <div class="selected-items" id="selectedCategories">
                                    @if(old('food_categories'))
                                        @foreach(old('food_categories') as $categoryId)
                                            @php
                                                $category = collect($categories)->flatten(1)->firstWhere('id', $categoryId);
                                            @endphp
                                            @if($category)
                                                <span class="selected-tag" data-value="{{ $category['id'] }}">
                                                    {{ $category['name'] }}
                                                    <button type="button" class="remove-tag" data-target="cat_{{ $category['id'] }}">×</button>
                                                </span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @error('food_categories')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                            <div class="text-sm text-gray-600 mt-2">
                                <span id="selectedCategoriesCount">{{ count(old('food_categories', [])) }}</span> categories selected
                            </div>
                        </div>
                    </div>

                    @error('selection')
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <div class="flex justify-between items-center mt-10 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.foodmanagements.featured-items.index') }}" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create Featured Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== SCRIPTS ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            /* ================= TYPE SELECTION ================= */
            const typeOptions = document.querySelectorAll('input[name="featured_type"]');
            const selectionMethodSection = document.getElementById('selection-method-section');
            
            typeOptions.forEach(option => {
                option.addEventListener('change', function() {
                    updateTypeSelection();
                    if (this.checked) {
                        selectionMethodSection.style.display = 'block';
                    }
                });
            });

            function updateTypeSelection() {
                document.querySelectorAll('.type-option').forEach(option => {
                    option.classList.remove('selected');
                });
                
                const selectedType = document.querySelector('input[name="featured_type"]:checked');
                if (selectedType) {
                    selectedType.closest('.type-option').classList.add('selected');
                }
            }

            /* ================= SELECTION METHOD ================= */
            const selectionMethodSelect = document.getElementById('selection_method');
            const itemLimitSection = document.getElementById('item-limit-section');
            const customSelectionSection = document.getElementById('custom-selection-section');
            const foodItemsSelection = document.getElementById('food-items-selection');
            const foodCategoriesSelection = document.getElementById('food-categories-selection');

            selectionMethodSelect.addEventListener('change', function() {
                const method = this.value;
                const featuredType = document.querySelector('input[name="featured_type"]:checked')?.value;

                // Hide all sections first
                itemLimitSection.style.display = 'none';
                customSelectionSection.style.display = 'none';
                foodItemsSelection.style.display = 'none';
                foodCategoriesSelection.style.display = 'none';

                if (method === 'custom') {
                    customSelectionSection.style.display = 'block';
                    if (featuredType === 'food_item') {
                        foodItemsSelection.style.display = 'block';
                    } else if (featuredType === 'category') {
                        foodCategoriesSelection.style.display = 'block';
                    }
                } else if (method) {
                    itemLimitSection.style.display = 'block';
                }
            });

            // Also trigger when featured type changes
            typeOptions.forEach(option => {
                option.addEventListener('change', function() {
                    if (selectionMethodSelect.value === 'custom') {
                        selectionMethodSelect.dispatchEvent(new Event('change'));
                    }
                });
            });

            /* ================= SEARCH FUNCTIONALITY ================= */
            const categorySearch = document.getElementById('categorySearch');
            const categoryOptions = document.getElementById('categoryOptions');
            const itemSearch = document.getElementById('itemSearch');
            const itemOptions = document.getElementById('itemOptions');
            
            // Category search functionality
            if (categorySearch && categoryOptions) {
                const allCategoryOptions = Array.from(categoryOptions.children);
                
                categorySearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    allCategoryOptions.forEach(option => {
                        const name = option.dataset.name || '';
                        if (name.includes(searchTerm) || searchTerm === '') {
                            option.style.display = 'flex';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                });
                
                categorySearch.addEventListener('focus', function() {
                    categoryOptions.style.display = 'block';
                    // Ensure dropdown is positioned correctly
                    setTimeout(() => {
                        const rect = categorySearch.getBoundingClientRect();
                        const spaceBelow = window.innerHeight - rect.bottom;
                        const dropdownHeight = 300; // max-height
                        
                        if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                            categoryOptions.style.top = 'auto';
                            categoryOptions.style.bottom = '100%';
                            categoryOptions.style.marginTop = '0';
                            categoryOptions.style.marginBottom = '0.25rem';
                        } else {
                            categoryOptions.style.top = '100%';
                            categoryOptions.style.bottom = 'auto';
                            categoryOptions.style.marginTop = '0.25rem';
                            categoryOptions.style.marginBottom = '0';
                        }
                    }, 10);
                });
            }
            
            // Item search functionality
            if (itemSearch && itemOptions) {
                const allItemOptions = Array.from(itemOptions.children);
                
                itemSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    allItemOptions.forEach(option => {
                        const name = option.dataset.name || '';
                        const categories = option.dataset.categories || '';
                        if (name.includes(searchTerm) || categories.toLowerCase().includes(searchTerm) || searchTerm === '') {
                            option.style.display = 'flex';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                });
                
                itemSearch.addEventListener('focus', function() {
                    itemOptions.style.display = 'block';
                    // Ensure dropdown is positioned correctly
                    setTimeout(() => {
                        const rect = itemSearch.getBoundingClientRect();
                        const spaceBelow = window.innerHeight - rect.bottom;
                        const dropdownHeight = 300; // max-height
                        
                        if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                            itemOptions.style.top = 'auto';
                            itemOptions.style.bottom = '100%';
                            itemOptions.style.marginTop = '0';
                            itemOptions.style.marginBottom = '0.25rem';
                        } else {
                            itemOptions.style.top = '100%';
                            itemOptions.style.bottom = 'auto';
                            itemOptions.style.marginTop = '0.25rem';
                            itemOptions.style.marginBottom = '0';
                        }
                    }, 10);
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-container')) {
                    if (categoryOptions) categoryOptions.style.display = 'none';
                    if (itemOptions) itemOptions.style.display = 'none';
                }
            });

            /* ================= DROPDOWN SELECTION HANDLING ================= */
            function handleDropdownSelection() {
                // Handle category selection
                document.querySelectorAll('#categoryOptions input[type="checkbox"]').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateSelectedTags('categories');
                        updateCounters();
                    });
                });
                
                // Handle item selection
                document.querySelectorAll('#itemOptions input[type="checkbox"]').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateSelectedTags('items');
                        updateCounters();
                    });
                });
                
                // Handle tag removal
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-tag')) {
                        const targetId = e.target.dataset.target;
                        const checkbox = document.getElementById(targetId);
                        if (checkbox) {
                            checkbox.checked = false;
                            e.target.parentElement.remove();
                            updateCounters();
                        }
                    }
                });
            }
            
            function updateSelectedTags(type) {
                const container = type === 'categories' ? 
                    document.getElementById('selectedCategories') : 
                    document.getElementById('selectedItems');
                
                const checkboxes = type === 'categories' ? 
                    document.querySelectorAll('#categoryOptions input[type="checkbox"]:checked') :
                    document.querySelectorAll('#itemOptions input[type="checkbox"]:checked');
                
                // Clear existing tags
                container.innerHTML = '';
                
                // Add new tags
                checkboxes.forEach(checkbox => {
                    const label = checkbox.nextElementSibling;
                    const text = type === 'categories' ? 
                        label.textContent.trim() :
                        label.querySelector('.item-name').textContent + ' - $' + 
                        label.closest('.dropdown-option').dataset.price;
                    
                    const tag = document.createElement('span');
                    tag.className = 'selected-tag';
                    tag.dataset.value = checkbox.value;
                    tag.innerHTML = `
                        ${text}
                        <button type="button" class="remove-tag" data-target="${checkbox.id}">×</button>
                    `;
                    container.appendChild(tag);
                });
            }

            function updateCounters() {
                const selectedCategories = document.querySelectorAll('#categoryOptions input[type="checkbox"]:checked').length;
                const selectedItems = document.querySelectorAll('#itemOptions input[type="checkbox"]:checked').length;
                
                const categoriesCounter = document.getElementById('selectedCategoriesCount');
                const itemsCounter = document.getElementById('selectedItemsCount');
                
                if (categoriesCounter) categoriesCounter.textContent = selectedCategories;
                if (itemsCounter) itemsCounter.textContent = selectedItems;
            }

            // Initialize dropdown functionality
            handleDropdownSelection();
            updateCounters();
            updateTypeSelection();

            // Initialize on page load if old values exist
            const selectedType = document.querySelector('input[name="featured_type"]:checked');
            if (selectedType) {
                selectionMethodSection.style.display = 'block';
                if (selectionMethodSelect.value) {
                    selectionMethodSelect.dispatchEvent(new Event('change'));
                }
            }
        });
    </script>

    {{-- ===================== STYLES ===================== --}}
    <style>
        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .input-field {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            background-color: #ffffff;
        }

        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-field.input-error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Type Selection */
        .type-option {
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }

        .type-option:hover {
            border-color: #d1d5db;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .type-option.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .type-label {
            display: block;
            padding: 1.5rem;
            cursor: pointer;
        }

        .type-content {
            text-align: center;
        }

        .type-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }

        .type-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .type-description {
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Custom Dropdown Styles */
        .dropdown-container {
            position: relative;
            width: 100%;
            z-index: 10;
        }

        .dropdown-search-box {
            position: relative;
            width: 100%;
        }

        .dropdown-search {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            background-color: #ffffff;
        }

        .dropdown-search:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1rem;
            height: 1rem;
            color: #9ca3af;
            pointer-events: none;
        }

        .dropdown-options {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            margin-top: 0.25rem;
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f9fafb;
        }

        .dropdown-options::-webkit-scrollbar {
            width: 6px;
        }

        .dropdown-options::-webkit-scrollbar-track {
            background: #f9fafb;
            border-radius: 3px;
        }

        .dropdown-options::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .dropdown-options::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        .dropdown-option {
            display: flex;
            align-items: flex-start;
            padding: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            min-height: 60px;
        }

        .dropdown-option:hover {
            background-color: #f9fafb;
        }

        .dropdown-option:last-child {
            border-bottom: none;
        }

        .dropdown-option input[type="checkbox"] {
            margin-right: 0.75rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .dropdown-label {
            flex: 1;
            cursor: pointer;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        .subcategory-label {
            color: #6b7280;
            font-style: italic;
        }

        .category-badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 0.5rem;
            text-transform: uppercase;
        }

        .category-badge.category-veg {
            background-color: #d1fae5;
            color: #065f46;
        }

        .category-badge.category-non_veg {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .category-badge.category-vegan {
            background-color: #ecfdf5;
            color: #047857;
        }

        .category-badge.category-all {
            background-color: #fef3c7;
            color: #92400e;
        }

        .item-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.25rem;
        }

        .item-name {
            font-weight: 500;
            color: #374151;
        }

        .item-price {
            font-weight: 600;
            color: #059669;
        }

        .item-categories {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .selected-items {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            min-height: 3rem;
        }

        .selected-tag {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            background-color: #3b82f6;
            color: white;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .remove-tag {
            margin-left: 0.5rem;
            background: none;
            border: none;
            color: white;
            font-size: 1.125rem;
            font-weight: bold;
            cursor: pointer;
            padding: 0;
            width: 1.25rem;
            height: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s ease-in-out;
        }

        .remove-tag:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #621F09;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #4c1a07;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(98, 31, 9, 0.3);
        }

        .btn-secondary {
            background-color: #f9fafb;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .grid.grid-cols-2 {
                grid-template-columns: 1fr;
            }

            .dropdown-options {
                max-height: 250px;
            }

            .dropdown-option {
                padding: 0.625rem;
                min-height: 50px;
            }

            .selected-items {
                padding: 0.5rem;
                min-height: 2.5rem;
            }

            .selected-tag {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }

        @media (max-height: 600px) {
            .dropdown-options {
                max-height: 200px;
            }
        }
    </style>
@endsection