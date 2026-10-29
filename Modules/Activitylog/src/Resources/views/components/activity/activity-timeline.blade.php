@props(['activities'])

@php
$groupedActivities = $activities->groupBy(function($activity) {
    return $activity->date_group ?? 'Tuesday, May 6, 2025';
});
@endphp

<div class="space-y-8 bg-[var(--card-background)] ">
    @foreach($groupedActivities as $date => $dateActivities)
    <div>
        <!-- Date Header -->
        <div class="flex items-center mb-6">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium" style="color: var(--foreground);">{{ $date }}</h3>
            <span class="ml-3 text-sm" style="color: var(--muted-foreground);">{{ $dateActivities->count() }} {{ Str::plural('activity', $dateActivities->count()) }}</span>
        </div>

        <!-- Timeline Items -->
        <div class="space-y-6" id="activity-timeline">
            @foreach($dateActivities as $activity)
            <div class="flex items-start space-x-4">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    @if($activity->user_avatar)
                        <img src="{{ $activity->user_avatar }}" alt="{{ $activity->user_name }}" class="w-12 h-12 rounded-full">
                    @else
                        <div class="w-12 h-12 {{ $activity->avatar_color ?? 'bg-blue-500' }} rounded-full flex items-center justify-center">
                            <span class="text-white font-medium">{{ $activity->user_initials }}</span>
                        </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0 rounded-lg bg-[var(--card-background)] border border-1 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-medium" style="color: var(--foreground);">{{ $activity->user_name }}</span>
                            @foreach($activity->tags as $tag)
                                 @include('activitylog::components.activity.activity-tag', ['tag' => $tag])
                            @endforeach
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium" style="color: var(--foreground);">{{ $activity->timeline_time ?? '12:30 AM' }}</div>
                            <div class="text-xs " style="color: var(--muted-foreground);">{{ $activity->relative_time ?? '21 days ago' }}</div>
                        </div>
                    </div>
                    
                    <h4 class="text-sm font-medium mb-1" style="color: var(--muted-foreground);">{{ $activity->title }}</h4>
                    <p class="text-sm mb-3" style="color: var(--muted-foreground);">{{ $activity->description }}</p>
                    
                    <!-- Timeline Footer -->
                    <div class="flex items-center justify-between text-xs" style="color: var(--muted-foreground);">
                        <div class="flex items-center space-x-4">
                            @if($activity->ip_address)
                            <div class="flex items-center" style="color: var(--muted-foreground);">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $activity->ip_address }}
                            </div>
                            @endif
                            @if($activity->location)
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $activity->location }}
                            </div>
                            @endif
                        </div>
                        @if($activity->category)
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded">{{ $activity->category }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>