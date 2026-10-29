@props(['activities'])
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 ">
        <thead class="bg-[var(--card-background)] border border-1">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider" style="color: var(--foreground);">Entity</th>
                <th class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider" style="color: var(--foreground);">Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider" style="color: var(--foreground);">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider" style="color: var(--foreground);">Severity</th>
                <th class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider" style="color: var(--foreground);">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider" style="color: var(--foreground);">Timestamp</th>
            </tr>
        </thead>
        <tbody class="bg-[var(--card-background)] border border-1 divide-y divide-gray-200" id="activity-list-table">
            @forelse($activities as $activity)
                <tr class="">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($activity->user_avatar)
                                <img src="{{ $activity->user_avatar }}" alt="{{ $activity->user_name }}" class="w-8 h-8 rounded-full mr-3">
                            @else
                                <div class="w-8 h-8 {{ $activity->avatar_color ?? 'bg-blue-500' }} rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white text-xs font-medium">{{ $activity->user_initials }}</span>
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium " style="color: var(--foreground);">{{ $activity->user_name }}</div>
                                <div class="text-sm text-gray-500">{{ $activity->user_role }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium" style="color: var(--foreground);">{{ $activity->description }}</div>
                        <div class="text-sm text-gray-500 max-w-xs truncate">{{ $activity->description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $typeTag = collect($activity->tags)->first(function($tag) {
                                return in_array($tag, ['approve', 'create', 'assign', 'update', 'reject', 'login', 'user', 'system']);
                            });
                        @endphp
                        @if($typeTag)
                            @include('activitylog::components.activity.activity-tag', ['tag' => $typeTag])
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $severityTag = collect($activity->tags)->first(function($tag) {
                                return in_array($tag, ['critical', 'high', 'medium', 'low', 'info']);
                            });
                        @endphp
                        @if($severityTag)
                            @include('activitylog::components.activity.activity-tag', ['tag' => $severityTag])
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">{{ $activity->category_name }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $activity->table_date }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No activities found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
