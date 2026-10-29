@props(['total', 'currentPage', 'lastPage'])

<div class="activity-results-heading px-6 py-4 border-b border-gray-200 bg-[var(--card-background)]">
    <div class="flex justify-between items-center">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
     xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
        clip-rule="evenodd"/>
</svg>

            <h3 class="text-lg font-medium " style="color: var(--foreground);">
                Activity Log 
                <span style="color: var(--muted-foreground);">({{ $total }} results)</span>
            </h3>
        </div>
        <span class="text-sm text-gray-500">Page {{ $currentPage }} of {{ $lastPage }}</span>
    </div>
</div>
