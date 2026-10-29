@props(['activities'])

<div class="activity-log-pagination px-6 py-4 border-t bg-[var(--card-background)] border border-1">
    <div class="flex justify-center">
        <nav class="flex items-center space-x-1">
            {{-- Previous Button --}}
            @if($activities->onFirstPage())
                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-[var(--card-background)] border border-1 rounded-md cursor-not-allowed">
                    Previous
                </span>
            @else
                <a href="{{ $activities->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-[var(--card-background)] border border-1 rounded-md hover:bg-gray-50 cursor-pointer">
                    Previous
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach($activities->getUrlRange(max(1, $activities->currentPage() - 2), min($activities->lastPage(), $activities->currentPage() + 2)) as $page => $url)
                @if($page == $activities->currentPage())
                    <span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-md">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Next Button --}}
            @if($activities->hasMorePages())
                <a href="{{ $activities->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-[var(--card-background)] border border-1 rounded-md hover:bg-gray-50 cursor-pointer">
                    Next
                </a>
            @else
                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-[var(--card-background)] border border-1 rounded-md cursor-not-allowed">
                    Next
                </span>
            @endif
        </nav>
    </div>
</div>
