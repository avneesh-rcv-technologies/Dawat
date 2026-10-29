@extends('backend::layouts.app')

@section('title', $eventId ? 'Edit Food Event' : 'Create Food Event')

@section('breadcrumbs')
    @php
        $title = $eventId ? 'Edit Food Event' : 'Create Food Event';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Food Events', 'url' => route('admin.foodmanagements.events.index')],
            ['label' => $title],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

    @php
        $activeTab = session('tab', $eventId ? 'items' : 'general');
    @endphp

    <div class="mx-auto">

        {{-- ===================== TABS HEADER ===================== --}}
        <div class="bg-white mb-6">
            <div class="flex border-b border-gray-200">
                <button class="tab-btn {{ $activeTab === 'general' ? 'tab-active' : '' }}" data-tab="general">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Event Details
                </button>

                @if ($eventId)
                    <button class="tab-btn {{ $activeTab === 'items' ? 'tab-active' : '' }}" data-tab="items">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Categories & Food Items
                    </button>
                @endif
            </div>
        </div>

        {{-- ===================== TAB CONTENT ===================== --}}

        {{-- TAB 1 : EVENT DETAILS --}}
        <div class="tab-content {{ $activeTab !== 'general' ? 'hidden' : '' }}" id="tab-general">
            <div class="bg-white p-8">
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Event Information</h3>
                    <p class="text-gray-600">Enter the basic details for your food event</p>
                </div>

                <form method="POST"
                    action="{{ $eventId
                        ? route('admin.foodmanagements.events.update', $eventId)
                        : route('admin.foodmanagements.events.store') }}">
                    @csrf

                    @if ($eventId)
                        @method('PUT')
                    @endif

                    <div class="">
                        <div class="space-y-6">
                            <div class="form-group">
                                <label class="form-label">Event Name <span class="text-red-500">*</span></label>
                                <input name="name" type="text" class="input-field @error('name') input-error @enderror"
                                    value="{{ old('name', $event->name ?? '') }}" placeholder="Enter event name"
                                    onkeyup="generateSlug()">
                                @error('name')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Slug</label>
                                <input name="slug" type="text" readonly class="input-field @error('slug') input-error @enderror"
                                    value="{{ old('slug', $event->slug ?? '') }}" placeholder="Auto-generated from name">
                                <p class="text-sm text-gray-500 mt-1">Leave empty to auto-generate from name</p>
                                @error('slug')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" editor="false"
                                    class="input-field @error('description') input-error @enderror" 
                                    placeholder="Enter event description">{{ old('description', $event->description ?? '') }}</textarea>
                                @error('description')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="input-field @error('status') input-error @enderror">
                                    <option value="1" @selected(old('status', $event->status ?? 1) == 1)>Active</option>
                                    <option value="0" @selected(old('status', $event->status ?? 1) == 0)>Inactive</option>
                                </select>
                                @error('status')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-10 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.foodmanagements.events.index') }}" class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ $eventId ? 'Update & Continue' : 'Save & Continue' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TAB 2 : CATEGORIES & ITEMS --}}
        @if ($eventId)
            <div class="tab-content {{ $activeTab !== 'items' ? 'hidden' : '' }}" id="tab-items">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Categories & Food Items</h3>
                        <p class="text-gray-600">Select categories and individual food items for this event</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- FOOD CATEGORIES SECTION --}}
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Food Categories</h4>
                                <p class="text-gray-600 text-sm mb-6">Select categories to include all their items</p>
                            </div>
                            
                            <form method="POST" action="{{ route('admin.foodmanagements.events.update-categories', $eventId) }}">
                                @csrf
                                
                                <div class="form-group">
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
                                                       data-type="{{ $category['food_type'] ?? 'all' }}">
                                                    <input type="checkbox" 
                                                           name="food_categories[]" 
                                                           value="{{ $category['id'] }}"
                                                           id="cat_{{ $category['id'] }}"
                                                           {{ isset($event) && $event->foodCategories->contains($category['id']) ? 'checked' : '' }}>
                                                    <label for="cat_{{ $category['id'] }}" class="dropdown-label">
                                                        <span class="category-badge category-{{ $category['food_type'] ?? 'all' }}">
                                                            {{ ucfirst(str_replace('_', ' ', $category['food_type'] ?? 'all')) }}
                                                        </span>
                                                        {{ $category['name'] }}
                                                    </label>
                                                </div>
                                                
                                                @if(count($category['subcategories']) > 0)
                                                    @foreach($category['subcategories'] as $subcategory)
                                                        <div class="dropdown-option subcategory-option" 
                                                                     data-value="{{ $subcategory['id'] }}"
                                                                     data-name="{{ strtolower($subcategory['name']) }}"
                                                                     data-type="{{ $subcategory['food_type'] ?? 'all' }}">
                                                            <input type="checkbox" 
                                                                   name="food_categories[]" 
                                                                   value="{{ $subcategory['id'] }}"
                                                                   id="cat_{{ $subcategory['id'] }}"
                                                                   {{ isset($event) && $event->foodCategories->contains($subcategory['id']) ? 'checked' : '' }}>
                                                            <label for="cat_{{ $subcategory['id'] }}" class="dropdown-label subcategory-label">
                                                                <span class="category-badge category-{{ $subcategory['food_type'] ?? 'all' }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $subcategory['food_type'] ?? 'all')) }}
                                                                </span>
                                                                └─ {{ $subcategory['name'] }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </div>
                                        
                                        <div class="selected-items" id="selectedCategories">
                                            @if(isset($event))
                                                @foreach($event->foodCategories as $category)
                                                    <span class="selected-tag" data-value="{{ $category->id }}">
                                                        {{ $category->name }}
                                                        <button type="button" class="remove-tag" data-target="cat_{{ $category->id }}">×</button>
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center justify-between">
                                    <button type="submit" class="btn btn-primary">
                                        Update Categories
                                    </button>
                                    <div class="text-sm text-gray-600">
                                        <span id="selectedCategoriesCount">{{ isset($event) ? $event->foodCategories->count() : 0 }}</span> categories selected
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- INDIVIDUAL FOOD ITEMS SECTION --}}
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Individual Food Items</h4>
                                <p class="text-gray-600 text-sm mb-6">Select specific food items for this event</p>
                            </div>
                            
                            <form method="POST" action="{{ route('admin.foodmanagements.events.update-items', $eventId) }}">
                                @csrf
                                
                                <div class="form-group">
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
                                            @php
                                            @endphp
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
                                                           {{ $event->foodItems->contains($item->id) ? 'checked' : '' }}>
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
                                            @foreach($event->foodItems as $item)
                                                <span class="selected-tag" data-value="{{ $item->id }}">
                                                    {{ $item->name }} - ${{ number_format($item->sale_price, 2) }}
                                                    <button type="button" class="remove-tag" data-target="item_{{ $item->id }}">×</button>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center justify-between">
                                    <button type="submit" class="btn btn-primary">
                                        Update Food Items
                                    </button>
                                    <div class="text-sm text-gray-600">
                                        <span id="selectedItemsCount">{{ $event->foodItems->count() }}</span> items selected
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- EVENT SUMMARY SECTION --}}
                    <div class="mt-10 pt-8 border-t border-gray-200">
                        <div class="flex justify-end">
                            <a href="{{ route('admin.foodmanagements.events.index') }}" class="btn btn-primary btn-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Complete Event
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- ===================== SCRIPTS ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const activeTab = "{{ $activeTab }}";

            /* ================= TAB INIT ================= */
            document.querySelectorAll('.tab-content')
                .forEach(c => c.classList.add('hidden'));

            document.getElementById('tab-' + activeTab)
                ?.classList.remove('hidden');

            document.querySelectorAll('.tab-btn')
                .forEach(b => b.classList.remove('tab-active'));

            document.querySelector(`.tab-btn[data-tab="${activeTab}"]`)
                ?.classList.add('tab-active');

            /* ================= TAB SWITCH ================= */
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const tab = btn.dataset.tab;

                    document.querySelectorAll('.tab-content')
                        .forEach(c => c.classList.add('hidden'));

                    document.getElementById('tab-' + tab)
                        ?.classList.remove('hidden');

                    document.querySelectorAll('.tab-btn')
                        .forEach(b => b.classList.remove('tab-active'));

                    btn.classList.add('tab-active');
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
                
                // Toggle dropdown visibility
                categorySearch.addEventListener('focus', function() {
                    categoryOptions.style.display = 'block';
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.dropdown-container')) {
                        categoryOptions.style.display = 'none';
                        itemOptions.style.display = 'none';
                    }
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
                
                // Toggle dropdown visibility
                itemSearch.addEventListener('focus', function() {
                    itemOptions.style.display = 'block';
                });
            }

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

            // Initialize dropdown functionality
            handleDropdownSelection();

            /* ================= COUNTER UPDATES ================= */
            function updateCounters() {
                const selectedCategories = document.querySelectorAll('#categoryOptions input[type="checkbox"]:checked').length;
                const selectedItems = document.querySelectorAll('#itemOptions input[type="checkbox"]:checked').length;
                
                const categoriesCounter = document.getElementById('selectedCategoriesCount');
                const itemsCounter = document.getElementById('selectedItemsCount');
                
                if (categoriesCounter) categoriesCounter.textContent = selectedCategories;
                if (itemsCounter) itemsCounter.textContent = selectedItems;
            }
        });

        /* ================= SLUG GENERATION ================= */
        function generateSlug() {
            @if (isset($event) && !empty($event->slug))
                return;
            @endif

            const nameInput = document.querySelector('input[name="name"]');
            const slugInput = document.querySelector('input[name="slug"]');

            if (!nameInput || !slugInput) return;

            const slug = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            slugInput.value = slug;
        }
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

        /* Custom Dropdown Styles */
        .dropdown-container {
            position: relative;
            width: 100%;
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
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            margin-top: 0.25rem;
        }

        .dropdown-option {
            display: flex;
            align-items: flex-start;
            padding: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
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

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1rem;
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

        .btn-success {
            background-color: #059669;
            color: #ffffff;
        }

        .btn-success:hover {
            background-color: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        /* Tab Styles */
        .tab-btn {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            background: none;
            border: none;
            border-radius: 0;
        }

        .tab-btn:hover {
            color: #374151;
            background-color: #f9fafb;
        }

        .tab-btn.tab-active {
            color: #621F09;
            border-bottom-color: #621F09;
            background-color: #fffbf5;
        }

        .tab-content {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .grid.lg\\:grid-cols-2 {
                grid-template-columns: 1fr;
            }

            .tab-btn {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }

            .tab-btn svg {
                width: 1rem;
                height: 1rem;
            }

            .btn-lg {
                padding: 0.875rem 1.5rem;
                font-size: 0.875rem;
            }

            .dropdown-options {
                max-height: 250px;
            }

            .dropdown-option {
                padding: 0.625rem;
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
    </style>

@endsection