@props(['stats'])

<div class="activity-stats-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
    <!-- Total Activities -->
    <div class="activity-stat-card bg-blue-50 rounded-lg p-4 border border-blue-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-blue-700">Total Activities</span>
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-bold">A</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-blue-900">{{ $stats['total'] ?? 150 }}</div>
        <div class="text-xs text-blue-600">All time activities</div>
    </div>

    <!-- Today's Activities -->
    <div class="activity-stat-card bg-green-50 rounded-lg p-4 border border-green-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-green-700">Today's Activities</span>
            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"
     xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
        clip-rule="evenodd"/>
</svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-green-900">{{ $stats['today'] ?? 3 }}</div>
        <div class="text-xs text-green-600">Activities today</div>
    </div>

    <!-- This Week -->
    <div class="activity-stat-card bg-purple-50 rounded-lg p-4 border border-purple-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-purple-700">This Week</span>
            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"
     xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
        clip-rule="evenodd"/>
</svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-purple-900">{{ $stats['week'] ?? 34 }}</div>
        <div class="text-xs text-purple-600">Past 7 days</div>
    </div>

    <!-- Critical Issues -->
    <div class="activity-stat-card bg-red-50 rounded-lg p-4 border border-red-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-red-700">Critical Issues</span>
            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-red-900">{{ $stats['critical'] ?? 33 }}</div>
        <div class="text-xs text-red-600">Requires attention</div>
    </div>

    <!-- High Priority -->
    <div class="activity-stat-card bg-orange-50 rounded-lg p-4 border border-orange-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-orange-700">High Priority</span>
            <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                </svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-orange-900">{{ $stats['high'] ?? 00 }}</div>
        <div class="text-xs text-orange-600">High & Critical</div>
    </div>

    <!-- Most Active -->
    <div class="activity-stat-card bg-indigo-50 rounded-lg p-4 border border-indigo-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-indigo-700">Most Active</span>
            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"
     xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
        clip-rule="evenodd"/>
</svg>
            </div>
        </div>
        <div class="text-lg font-bold text-indigo-900">{{ $stats['most_active_type'] ?? 'Admin' }}</div>
        <div class="text-xs text-indigo-600">{{ $stats['most_active_count'] ?? 00 }} activities</div>
    </div>
</div>
