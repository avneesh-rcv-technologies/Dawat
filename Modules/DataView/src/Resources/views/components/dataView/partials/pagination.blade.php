@php
    $pagination = request('pagination', []);
    $currentPage = $pagination['page'] ?? $data['current_page'];
    $perPage = $pagination['per_page'] ?? $data['per_page'];
@endphp

<div class="flex items-center justify-between mt-4 px-4 py-3 bg-white  sm:px-6">

    <!-- Desktop view -->
    <div class="flex-1 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $data['from'] }}</span> to
                <span class="font-medium">{{ $data['to'] }}</span> of
                <span class="font-medium">{{ $data['total'] }}</span> results
            </p>
        </div>
    </div>

    <div class="flex items-center space-x-2">
        <nav class="flex items-center space-x-2">
            <!-- First page (<<) -->
            <a href="{{ request()->fullUrlWithQuery(['pagination[page]' => 1]) }}"
                class="p-2 rounded-md border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors duration-150 {{ $currentPage == 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                aria-label="First page">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
            </a>

            <!-- Previous page (<) -->
            <a href="{{ request()->fullUrlWithQuery(['pagination[page]' => max(1, $currentPage - 1)]) }}"
                class="p-2 rounded-md border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors duration-150 {{ $currentPage == 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                aria-label="Previous page">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>

            <!-- Per page dropdown -->
            <div class="relative">
                <select onchange="window.location.href = this.value"
                    class="appearance-none pl-3 pr-8 py-2 border border-gray-200 rounded-md bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm">
                    @foreach($data['per_page_options'] as $option)
                        <option
                            value="{{ request()->fullUrlWithQuery(['pagination[per_page]' => $option, 'pagination[page]' => 1]) }}"
                            {{ $perPage == $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <!-- Next page (>) -->
            <a href="{{ request()->fullUrlWithQuery(['pagination[page]' => min($data['last_page'], $currentPage + 1)]) }}"
                class="p-2 rounded-md border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors duration-150 {{ $currentPage == $data['last_page'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                aria-label="Next page">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>

            <!-- Last page (>>) -->
            <a href="{{ request()->fullUrlWithQuery(['pagination[page]' => $data['last_page']]) }}"
                class="p-2 rounded-md border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors duration-150 {{ $currentPage == $data['last_page'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                aria-label="Last page">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                    <path fill-rule="evenodd"
                        d="M4.293 15.707a1 1 0 010-1.414L8.586 10 4.293 5.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>
        </nav>
    </div>
</div>