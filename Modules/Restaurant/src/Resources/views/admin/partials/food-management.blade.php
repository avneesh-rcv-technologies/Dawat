{{-- Food Management Tab --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6">
        @if($restaurantId)
        {{-- Restaurant Context Indicator --}}
        @php
        $restaurant = \Modules\Restaurant\Models\Restaurant::find($restaurantId);
        @endphp
        @if($restaurant)
        <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-blue-800">
                    Managing food items for: <strong>{{ $restaurant->name }}</strong>
                </span>
            </div>
        </div>
        @endif

        {{-- Header with Menu --}}
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Food Items</h3>

            {{-- Food Management Menu --}}
            <div class="relative" id="foodMenuContainer">
                <button id="foodMenuBtn"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-black hover:bg-black text-white transition-colors"
                    title="Food Management Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>

                {{-- Dropdown Menu --}}
                <div id="foodDropdown"
                    class="absolute right-0 mt-3 w-56 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible transition-all duration-200 z-50"
                    style="transform: translateY(-10px);">
                    <ul class="py-1">
                        <li>
                            <a href="{{ route('admin.foodmanagements.category.index', ['restaurant_id' => $restaurantId, 'from_restaurant' => 1]) }}"
                                class="block px-4 py-3 hover:bg-blue-50 text-gray-700 text-sm font-medium hover:text-blue-600 transition-colors">
                                Manage Food Categories
                            </a>
                        </li>
                        {{-- <li class="border-t border-gray-100">
                            <a href="{{ route('admin.foodmanagements.food-items.index') }}"
                                class="block px-4 py-3 hover:bg-blue-50 text-gray-700 text-sm font-medium hover:text-blue-600 transition-colors">
                                Manage Food Items
                            </a>
                        </li> --}}
                         @if(bouncer()->hasPermission('admin.foodmanagements.food-tags.index'))
                        <li class="border-t border-gray-100">
                            <a href="{{ route('admin.foodmanagements.food-tags.index') }}"
                                class="block px-4 py-3 hover:bg-blue-50 text-gray-700 text-sm font-medium hover:text-blue-600 transition-colors">
                                Manage Food Tags
                            </a>
                        </li>
                        @endif
                         @if(bouncer()->hasPermission('admin.foodmanagements.food-option-groups.index'))
                        <li class="border-t border-gray-100">
                            <a href="{{ route('admin.foodmanagements.food-option-groups.index') }}"
                                class="block px-4 py-3 hover:bg-blue-50 text-gray-700 text-sm font-medium hover:text-blue-600 transition-colors">
                                Manage Food Options
                            </a>
                        </li>
                        @endif
                        <li class="border-t border-gray-100">
                            <a href="{{ route('admin.foodmanagements.events.index') }}"
                                class="block px-4 py-3 hover:bg-blue-50 text-gray-700 text-sm font-medium hover:text-blue-600 transition-colors">
                                Manage Events
                            </a>
                        </li>
                        
                        {{-- <li class="border-t border-gray-100">
                            <a href="{{ route('admin.foodmanagements.featured-items.index') }}"
                                class="block px-4 py-3 hover:bg-blue-50 text-gray-700 text-sm font-medium hover:text-blue-600 transition-colors">
                                Manage Featured Items
                            </a>
                        </li> --}}
                        @if(bouncer()->hasPermission('admin.foodmanagements.features.index'))
                        <li class="border-t border-gray-100">
                            <a href="{{ route('admin.foodmanagements.features.index') }}"
                                class="block px-4 py-3 hover:bg-blue-50 text-gray-700 text-sm font-medium hover:text-blue-600 transition-colors">
                                Food Features
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Food Items DataGrid --}}
        <div class="overflow-x-auto">
            <x-data-view :data="$foodItems" title="" />
        </div>

        @else
        {{-- Empty State --}}
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            <p class="text-gray-500 text-lg font-medium">Save the restaurant first</p>
            <p class="text-gray-400 text-sm mt-2">Complete the general information section to add food items</p>
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const foodMenuBtn = document.getElementById('foodMenuBtn');
        const foodDropdown = document.getElementById('foodDropdown');
        const foodMenuContainer = document.getElementById('foodMenuContainer');

        // Toggle dropdown on button click
        foodMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (foodDropdown.classList.contains('opacity-0')) {
                // Show dropdown
                foodDropdown.classList.remove('opacity-0', 'invisible');
                foodDropdown.classList.add('opacity-100', 'visible');
                foodDropdown.style.transform = 'translateY(0)';
            } else {
                // Hide dropdown
                foodDropdown.classList.add('opacity-0', 'invisible');
                foodDropdown.classList.remove('opacity-100', 'visible');
                foodDropdown.style.transform = 'translateY(-10px)';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!foodMenuContainer.contains(e.target)) {
                foodDropdown.classList.add('opacity-0', 'invisible');
                foodDropdown.classList.remove('opacity-100', 'visible');
                foodDropdown.style.transform = 'translateY(-10px)';
            }
        });

        // Close dropdown when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                foodDropdown.classList.add('opacity-0', 'invisible');
                foodDropdown.classList.remove('opacity-100', 'visible');
                foodDropdown.style.transform = 'translateY(-10px)';
            }
        });

       
        // Override search input inside food tab to always keep tab=food
        const foodManagementSection = document.getElementById('tab-food');
        if (foodManagementSection) {
            const searchInput = foodManagementSection.querySelector('input[name="all"]');
            if (searchInput) {
                // Remove the inline onkeypress handler and replace with our own
                searchInput.removeAttribute('onkeypress');
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const value = e.target.value;
                        const params = new URLSearchParams(window.location.search);
                        params.set('filters[all][]', value);
                        params.set('pagination[page]', 1);
                       
                        params.set('tab', 'food');
                        window.location.href = window.location.pathname + '?' + params.toString();
                    }
                });
            }

            const observer = new MutationObserver(function() {
                addTabParameterToLinks();
                overrideSearchInFoodTab();
                overrideFilterSidebarInFoodTab();
            });

            
            observer.observe(foodManagementSection, {
                childList: true,
                subtree: true
            });

         
            addTabParameterToLinks();
            overrideFilterSidebarInFoodTab();
        }

        function addTabParameterToLinks() {
            const foodManagementSection = document.getElementById('tab-food');
            if (!foodManagementSection) return;
            const allLinks = foodManagementSection.querySelectorAll('a[href]');
            
            allLinks.forEach(link => {
                try {
                    const url = new URL(link.href);
                 
                    if (url.pathname === window.location.pathname) {
                        if (!url.searchParams.has('tab')) {
                            url.searchParams.set('tab', 'food');
                            link.href = url.toString();
                        }
                   
                        const currentId = new URLSearchParams(window.location.search).get('id');
                        if (currentId && !url.searchParams.has('id')) {
                            url.searchParams.set('id', currentId);
                            link.href = url.toString();
                        }
                    }
                } catch(e) {
                   
                }
            });
        }

        function overrideSearchInFoodTab() {
            const foodManagementSection = document.getElementById('tab-food');
            if (!foodManagementSection) return;

        
            const searchInputs = foodManagementSection.querySelectorAll('input[name="all"]');
            searchInputs.forEach(input => {
                if (!input.dataset.tabOverridden) {
                    input.dataset.tabOverridden = 'true';
                    input.removeAttribute('onkeypress');
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const value = e.target.value;
                            const params = new URLSearchParams(window.location.search);
                            params.set('filters[all][]', value);
                            params.set('pagination[page]', 1);
                            params.set('tab', 'food');
                            window.location.href = window.location.pathname + '?' + params.toString();
                        }
                    });
                }
            });
        }

        // Override filter sidebar form & reset link to keep tab=food
        
        function overrideFilterSidebarInFoodTab() {
            
            const filterForm = document.getElementById('filterForm');
            if (filterForm && !filterForm.dataset.tabOverridden) {
                filterForm.dataset.tabOverridden = 'true';
                const currentParams = new URLSearchParams(window.location.search);
                const restaurantId = currentParams.get('id');
                const tabInput = document.createElement('input');
                tabInput.type = 'hidden';
                tabInput.name = 'tab';
                tabInput.value = 'food';
                filterForm.appendChild(tabInput);
                if (restaurantId) {
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    idInput.value = restaurantId;
                    filterForm.appendChild(idInput);
                }
            }

            const filterSidebar = document.getElementById('filterSidebar');
            if (filterSidebar) {
                const resetLinks = filterSidebar.querySelectorAll('a.btn-cancel');
                resetLinks.forEach(link => {
                    if (!link.dataset.tabOverridden) {
                        link.dataset.tabOverridden = 'true';
                        try {
                            const url = new URL(link.href);
                            const currentParams = new URLSearchParams(window.location.search);
                            const restaurantId = currentParams.get('id');
                            url.searchParams.set('tab', 'food');
                            if (restaurantId) {
                                url.searchParams.set('id', restaurantId);
                            }
                            link.href = url.toString();
                        } catch(e) {
                           
                        }
                    }
                });
            }
        }
    });

</script>