@php
    $pagination = request('pagination', []);
    $currentPage = $pagination['page'] ?? $data['meta']['current_page'];
    $perPage = $pagination['per_page'] ?? $data['meta']['per_page'];
    $activeFilterCount = collect((array) request()->input('filters', []))
        ->except('all')
        ->filter(fn ($value) => collect(\Illuminate\Support\Arr::flatten((array) $value))->contains(fn ($item) => $item !== null && $item !== ''))
        ->count();
@endphp


<div class="flex flex-col md:flex-row md:items-center mt-6 md:justify-between mb-6 gap-4">
    <div class="relative max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-[var(--color-text-secondary)]" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                    clip-rule="evenodd"></path>
            </svg>
        </div>
        <input type="text" name="all" id="all" value="{{ request('filters')['all'][0] ?? '' }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-[var(--color-white)] 
               placeholder-[var(--color-text-secondary)] text-[var(--color-text-primary)] sm:text-sm 
               focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 transition-all duration-200"
            placeholder="Search..." onkeypress="handleKeySearch(event)">
    </div>

    <div class="flex-1 flex flex-col sm:flex-row justify-end gap-4">
        <!-- Mass Action Button Group (hidden by default) -->
        <div class="flex flex-wrap gap-2 items-center space-x-2">
            @foreach($data['mass_actions'] as $action)

                @if(!empty($action['options']))

                    <div @if($action['method'] === 'POST') data-mass-action class="relative text-left hidden" @else
                    class="relative inline-block text-left" @endif>
                        <div class="relative">
                            <button type="button" onclick="toggleDropdown({{ $loop->index }})" class="btn-status !flex"
                                id="menu-button-{{ $loop->index }}" aria-expanded="false" aria-haspopup="true"
                                aria-controls="dropdown-menu-{{ $loop->index }}">
                                <span> {{ $action['title'] }}</span>
                                <span><svg class="-mr-1 ml-2 h-5 w-5 transition-transform duration-200 transform"
                                        id="dropdown-arrow-{{ $loop->index }}" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg></span>
                            </button>

                            <div id="dropdown-menu-{{ $loop->index }}"
                                class="hidden origin-top-right absolute left-0 mt-2 w-56 rounded-md shadow-lg 
                                                                                                                                                                                                                                                                           bg-[var(--color-white)] ring-1 ring-[var(--color-gray-300)] focus:outline-none 
                                                                                                                                                                                                                                                                           z-10 transition-all duration-200 ease-out"
                                role="menu" aria-orientation="vertical" aria-labelledby="menu-button-{{ $loop->index }}">
                                <div class="py-1" role="none">
                                    @foreach($action['options'] as $option)
                                        @php
                                            // Resolve action URL: allow either named route or full URL string
                                            $actionUrl = null;
                                            try {
                                                if (!empty($action['url']) && filter_var($action['url'], FILTER_VALIDATE_URL)) {
                                                    $actionUrl = $action['url'];
                                                } else {
                                                    $actionUrl = route($action['url']);
                                                }
                                            } catch (\Exception $e) {
                                                // Fallback: try to use as-is
                                                $actionUrl = $action['url'];
                                            }

                                            // Resolve option value URL when used as href (could be route name or URL)
                                            $optionUrl = null;
                                            try {
                                                if (!empty($option['value']) && filter_var($option['value'], FILTER_VALIDATE_URL)) {
                                                    $optionUrl = $option['value'];
                                                } else {
                                                    $optionUrl = route($option['value']);
                                                }
                                            } catch (\Exception $e) {
                                                $optionUrl = $option['value'];
                                            }
                                        @endphp

                                        @if($action['method'] === 'POST')
                                            <a href="javascript:;"
                                                onclick="handleMassAction('{{ $actionUrl }}', '{{ $action['method'] }}', '{{ $option['value'] }}')"
                                                class="block px-4 py-2 text-sm text-[var(--color-gray-700)] hover:bg-[var(--color-gray-100)]"
                                                role="menuitem">{{ $option['label'] }}</a>
                                        @else
                                            @if(!empty($action['is_popup']))
                                                @php
                                                    $title = isset($action['icon'])
                                                        ? "<span class='material-icons-outlined'>" . $action['icon'] . "</span> " . $action['title']
                                                        : $action['title'];
                                                @endphp
                                                <x-modal buttonText="{!! $title !!}" modalTitle="{{ $action['title'] }}"
                                                    id="massaction_{{rand(100, 2000)}}" ajaxUrl="{{ $actionUrl }}" color="green"
                                                    modalSize="2xl" />
                                            @else
                                                <a href="{{ $optionUrl }}"
                                                    class="block px-4 py-2 text-sm text-[var(--color-gray-700)] hover:bg-[var(--color-gray-100)]"
                                                    role="menuitem">{{ $option['label'] }}</a>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    @if($action['method'] === 'POST')
                        {{-- Regular button --}}
                        {{-- delete button also --}}
                        @php
                            try {
                                if (!empty($action['url']) && filter_var($action['url'], FILTER_VALIDATE_URL)) {
                                    $actionUrl = $action['url'];
                                } else {
                                    $actionUrl = route($action['url']);
                                }
                            } catch (\Exception $e) {
                                $actionUrl = $action['url'];
                            }
                        @endphp
                        <button data-mass-action type="button"
                            onclick="handleMassAction('{{ $actionUrl }}', '{{ $action['method'] }}')"
                            class="{{ $action['icon'] === 'delete' ? 'btn-danger' : 'btn-cancel' }} hidden">

                            @if($action['icon'] === 'delete')
                                <span class="material-icons-outlined mr-2 text-red-500">{{ $action['icon'] }}</span>
                            @endif

                            {{ $action['title'] }}
                        </button>

                    @else
                        {{-- Get Button --}}

                        @if(!empty($action['is_popup']))
                            @php
                                try {
                                    if (!empty($action['url']) && filter_var($action['url'], FILTER_VALIDATE_URL)) {
                                        $actionUrl = $action['url'];
                                    } else {
                                        $actionUrl = route($action['url']);
                                    }
                                } catch (\Exception $e) {
                                    $actionUrl = $action['url'];
                                }
                            @endphp
                            @php
                                $title = isset($action['icon']) ? $action['title'] : $action['title'];
                            @endphp
                            <x-modal buttonText="{!! $title !!}" modalTitle="{{ $action['title'] }}" id="massaction_{{rand(100, 2000)}}"
                                ajaxUrl="{{ $actionUrl }}" color="btn-primary" modalSize="2xl" />
                        @else

                            @php
                                try {
                                    if (!empty($action['url']) && filter_var($action['url'], FILTER_VALIDATE_URL)) {
                                        $actionUrl = $action['url'];
                                    } else {
                                        $actionUrl = route($action['url']);
                                    }
                                } catch (\Exception $e) {
                                    $actionUrl = $action['url'];
                                }
                            @endphp

                            <a href="{{ $actionUrl }}" class="btn-primary">

                                {{ $action['title'] }}
                            </a>
                        @endif
                    @endif
                @endif
            @endforeach
            @if(!empty($is_export) && $is_export == 'true')
                <x-modal buttonText="<span class='material-icons-outlined mr-1'>file_upload</span>Export"
                    modalTitle="Export" id="export_data_view" ajaxUrl="{{route('dataview.export')}}" color="green"
                    modalSize="sm" />
            @endif

            <button id="filterToggle" type="button" aria-label="Open filters{{ $activeFilterCount ? ', '.$activeFilterCount.' active' : '' }}" aria-controls="filterSidebar" aria-expanded="false"
                class="filter-toggle-button">
                <svg class="filter-toggle-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                @if($activeFilterCount > 0)
                    <span class="filter-toggle-count">{{ $activeFilterCount }}</span>
                @endif
            </button>
        </div>


    </div>
</div>




<script>
    updateMassActionsVisibility();

    // Toggle all checkboxes
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateMassActionsVisibility();
    }

    // Show/hide mass action buttons based on selection
    function updateMassActionsVisibility() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const selectAll = document.getElementById('select-all');
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }

        // Select all mass action containers
        const massActionContainers = document.querySelectorAll('[data-mass-action]');

        massActionContainers.forEach(container => {
            container.style.display = checkedCount > 0 ? 'inline-flex' : 'none';
        });
    }

    // Handle mass actions with confirmation
    function handleMassAction(url, method, optionValue = null) {
        const selectedIds = [...new Set(Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .map(checkbox => checkbox.value))];

        if (selectedIds.length === 0) {
            alert('Please select at least one item');
            return;
        }

        // Create confirmation modal
        const modal = document.createElement('div');
        modal.className = "fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50"
;
        modal.innerHTML = `
            <div class="bg-white p-8 rounded-2xl shadow-[0_4px_20px_var(--color-shadow)] w-full max-w-md transform transition-all duration-300 ease-out scale-95 hover:scale-100">
                <div class="flex  justify-between gap-4 mb-2">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center"
                style="border: 8px solid #FEF3F2">
                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M0.75 1.92241H8.37069M0.75 16.5776H8.37069M0.75 9.25H3.68103M12.7671 1.92241H17.7498M12.7671 16.5776H17.7498M8.07744 9.25H17.7499M12.4744 0.75V3.68103M12.4744 14.819V17.75M7.78476 7.78449V10.7155" stroke="#170F49" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

            </div>

          
        </div>

        <div class="mb-3">
            <h2 class="text-xl font-semibold text-[#181D27]">Need Confirmation</h2>
            <p class="text-[#535862] text-sm mt-1">
                Are you sure you want to make these changes?
            </p>
        </div>
                
                <div class="w-full grid grid-cols-2 gap-4">
                    <button id="confirmAction" name="button" class="btn-cancel">Confirm</button>
                    <button  id="cancelAction" class="btn-primary">Cancel</button>

                </div>


            </div>`;

        // Add modal to body
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';

        // Handle cancel button
        document.getElementById('cancelAction').addEventListener('click', () => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
        });

        // Handle confirm button
        document.getElementById('confirmAction').addEventListener('click', () => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';

            // For POST/PUT/DELETE, use a form submission
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            // Add CSRF token if using Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
            }

            // Add method spoofing for PUT/DELETE
            if (method !== 'POST') {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = method;
                form.appendChild(methodInput);
            }

            // Add selected IDs
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            // Add option value if provided
            if (optionValue) {
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = optionValue;
                form.appendChild(actionInput);
            }

            document.body.appendChild(form);
            form.submit();

        });
    }

    // Toggle dropdown function
    function toggleDropdown(index) {
        const dropdown = document.getElementById(`dropdown-menu-${index}`);
        const button = document.getElementById(`menu-button-${index}`);
        const arrow = document.getElementById(`dropdown-arrow-${index}`);

        // Toggle visibility
        dropdown.classList.toggle('hidden');

        // Update aria-expanded
        const isExpanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', !isExpanded);

        // Rotate arrow
        arrow.classList.toggle('rotate-180');

        // Close other dropdowns if needed
        document.querySelectorAll('[id^="dropdown-menu-"]').forEach((menu) => {
            if (menu.id !== `dropdown-menu-${index}`) {
                menu.classList.add('hidden');
                const otherButton = document.getElementById(menu.getAttribute('aria-labelledby'));
                if (otherButton) {
                    otherButton.setAttribute('aria-expanded', 'false');
                    const otherArrow = document.getElementById(`dropdown-arrow-${menu.id.split('-').pop()}`);
                    if (otherArrow) otherArrow.classList.remove('rotate-180');
                }
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[id^="menu-button-"]') && !event.target.closest('[id^="dropdown-menu-"]')) {
            document.querySelectorAll('[id^="dropdown-menu-"]').forEach((menu) => {
                menu.classList.add('hidden');
                const button = document.getElementById(menu.getAttribute('aria-labelledby'));
                if (button) button.setAttribute('aria-expanded', 'false');
                const arrow = document.getElementById(`dropdown-arrow-${menu.id.split('-').pop()}`);
                if (arrow) arrow.classList.remove('rotate-180');
            });
        }
    });

    function handleKeySearch(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = e.target.value.trim();
            const params = new URLSearchParams(window.location.search);
            for (const key of [...params.keys()]) {
                if (key === 'filters[all]' || key.startsWith('filters[all][')) {
                    params.delete(key);
                }
            }
            if (value) params.set('filters[all][]', value);
            params.set('pagination[page]', 1);
            window.location.href = window.location.pathname + '?' + params.toString();
        }
    }
</script>
