@props(['activity'])

<div id="activity-list" class="activity-log-card">
    <div class="activity-log-card-content p-6 bg-[var(--card-background)] border border-1 transition-colors" x-data="{ expanded: false }">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                @if($activity->user_avatar)
                <img src="{{ $activity->user_avatar }}" alt="{{ $activity->user_name }}" class="w-10 h-10 rounded-full">
                @else
                <div class="w-10 h-10 {{ $activity->avatar_color ?? 'bg-blue-500' }} rounded-full flex items-center justify-center">
                    <span class="text-white font-medium">{{ $activity->user_initials }}</span>
                </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                    <span class="font-medium truncate" style="color: var(--foreground);">{{ $activity->user_name }}</span>
                    @foreach($activity->tags as $tag)
                    @include('activitylog::components.activity.activity-tag', ['tag' => $tag])
                    @endforeach
                </div>
                <h4 class="text-sm font-medium mb-1" style="color: var(--muted-foreground);">{{ $activity->title }}</h4>
                <p class="text-sm mb-2 truncate" style="color: var(--muted-foreground);">{{ $activity->description }}</p>
                <div class="flex items-center text-xs" style="color: var(--muted-foreground);">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $activity->full_date }}
                    @if($activity->category)
                    <span class="mx-2">•</span>
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">{{ $activity->category }}</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="expanded = !expanded" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5 transform transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Expanded Details -->
        <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-6 border-t pt-6">

            <!-- Full Description -->
            <div class="mb-6">
                <h3 class="text-sm font-medium mb-2" style="color: var(--foreground);">Full Description</h3>
                <p class="text-sm truncate" style="color: var(--muted-foreground);">{{ $activity->full_description ?? $activity->description }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Timestamp Details -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-blue-900 mb-3">Timestamp Details</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-blue-700">Full Date:</span>
                            <span class="text-sm font-medium text-blue-900">{{ $activity->full_date ?? 'Tuesday, May 6, 2025 at 12:30:38 AM' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-blue-700">Relative:</span>
                            <span class="text-sm text-blue-600">{{ $activity->relative_time ?? '21 days ago' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Entity Information -->
                <div class="bg-purple-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-purple-900 mb-3">Errors</h3>
                    <div class="space-y-2 rounded-md text-sm overflow-y-auto max-h-64">
                        @php
                            $errors = ['errors', 'error', 'trace'];
                        @endphp
                        @foreach($errors as $erroKey)
                            @if(!empty($activity->properties[$erroKey]))

                                {!! json_encode($activity->properties[$erroKey] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}

                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Additional Metadata -->
            <div class="mt-6 bg-orange-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-orange-900 mb-3">Additional Metadata</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-orange-700">IP:</span>
                        <span class="ml-1 font-medium text-orange-900">{{ $activity->ip_address ?? '192.168.46.45' }}</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-orange-700">Location:</span>
                        <span class="ml-1 font-medium text-orange-900">{{ $activity->location ?? 'New York, NY' }}</span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex justify-start items-center text-sm">
                        <span class="text-orange-700 mr-2">User Agent:</span>
                        <span class="text-orange-600 text-xs mt-1 break-all">{{ $activity->user_agent ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
