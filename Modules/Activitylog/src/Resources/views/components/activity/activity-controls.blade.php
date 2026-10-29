<div class="activity-log-controls bg-[var(--card-background)] border border-1 rounded-lg shadow-sm p-4" x-data="{ activeView: 'cards' }">
    <div class="flex justify-between items-center">
        <div class="flex space-x-4">
            <button
                @click="activeView = 'cards'; $dispatch('view-changed', 'cards')"
                :class="activeView === 'cards' ? 'text-gray-900 bg-gray-100' : 'text-gray-500'"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                Cards
            </button>
            <button
                @click="activeView = 'table'; $dispatch('view-changed', 'table')"
                :class="activeView === 'table' ? 'text-gray-900 bg-gray-100' : 'text-gray-500'"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 8h9"></path>
                </svg>
                Table
            </button>
            <button
                @click="activeView = 'timeline'; $dispatch('view-changed', 'timeline')"
                :class="activeView === 'timeline' ? 'text-gray-900 bg-gray-100' : 'text-gray-500'"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Timeline
            </button>
        </div>
        <div class="relative">
            <input type="text" id="activity-search-input" placeholder="Search activities, entities, actions..." class="w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-md ">
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>
</div>
