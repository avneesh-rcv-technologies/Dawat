@php
    use Modules\Notifications\Models\Notification;
    $admin = auth($guard)->user();
    $notifications = collect([]);
    $notifCount = 0;
    if ($admin) {
        $notifications = Notification::where('notifiable_type', get_class($admin))
            ->where('notifiable_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $notifCount = Notification::where('notifiable_type', get_class($admin))
            ->where('notifiable_id', $admin->id)
            ->whereNull('read_at')
            ->count();
    }
@endphp

<div class="relative" id="notifications-dropdown">
    <button id="notifications-btn"
        class="relative inline-flex items-center px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <span class="material-icons-outlined text-gray-700 dark:text-gray-200 text-xl">notifications</span>
        @if ($notifCount > 0)
            <span class="absolute -top-1 -right-1 relative flex" style="width: 8px; height: 8px;">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full bg-blue-500" style="width: 8px; height: 8px;"></span>
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div id="notifications-dropdown-menu" style="width: 350px;"
        class="hidden absolute right-0 top-9 rounded-lg bg-white shadow-lg border border-neutral-200 z-50">
        <!-- Arrow -->
        <div class="absolute right-4 -top-1 h-3 w-3 rotate-45 bg-white border-l border-t border-neutral-200"></div>
        <div class="p-1 max-h-[400px] overflow-y-auto">
            <div class="px-3 py-3 border-b border-neutral-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Recent Notifications</h3>
                <div class="flex items-center gap-3">
                    @if ($notifCount > 0)
                        <button type="button" class="notification-mark-all-read text-xs text-blue-600 hover:text-blue-700 font-medium"
                            data-url="{{ route('notifications.bell.mark-all-as-read') }}">
                            Mark all read
                        </button>
                    @endif
                    <a href="{{ route('system.notifications') }}"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium">View All</a>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @if ($notifications->isEmpty())
                    <div class="px-4 py-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-3">
                            <span class="material-icons-outlined text-gray-400 text-3xl">notifications_none</span>
                        </div>
                        <p class="text-sm text-gray-600 font-medium">No notifications</p>
                        <p class="text-xs text-gray-400 mt-1">You're all caught up</p>
                    </div>
                @else
                    @foreach ($notifications as $notification)
                        @php
                            // Decode data if it's a string
                            $notifData = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
                            if (!is_array($notifData)) {
                                $notifData = [];
                            }
                            
                            // Extract title and body
                            $title = $notifData['title'] ?? 'Notification';
                            $body = $notifData['body'] ?? $notifData['message'] ?? $notifData['content'] ?? '';
                            
                            // If still empty, try to extract from notification type
                            if ($title === 'Notification') {
                                $type = class_basename($notification->type);
                                $title = preg_replace('/Notification$/', '', $type);
                                $title = preg_replace('/([a-z])([A-Z])/', '$1 $2', $title);
                            }
                            
                            // If body is still empty, use the data as string
                            if (empty($body) && is_string($notification->data)) {
                                $body = strip_tags($notification->data);
                            }
                        @endphp
                        <div class="flex items-start space-x-3 px-4 py-3 hover:bg-gray-50 transition-colors group {{ is_null($notification->read_at) ? 'bg-blue-50/40' : '' }}">
                            <span class="material-icons-outlined text-blue-500">notifications</span>
                            <a href="{{ route('system.notification-show', $notification->id) }}" class="flex-1 min-w-0 notification-link" data-id="{{ $notification->id }}">
                                <div
                                    class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                                    {{ $title }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ \Illuminate\Support\Str::limit($body, 100) }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $notification->created_at->format('M d, g:i A') }}</div>
                            </a>
                            <div class="flex-shrink-0 flex items-center gap-1">
                                <button type="button" class="notification-read-toggle p-1 text-gray-400 hover:text-blue-600"
                                    data-url="{{ route('notifications.bell.toggle-read-status', $notification->id) }}"
                                    aria-label="{{ is_null($notification->read_at) ? 'Mark as read' : 'Mark as unread' }}"
                                    title="{{ is_null($notification->read_at) ? 'Mark as read' : 'Mark as unread' }}">
                                    <span class="material-icons-outlined text-base">{{ is_null($notification->read_at) ? 'mark_email_read' : 'mark_email_unread' }}</span>
                                </button>
                                <span class="material-icons-outlined text-blue-500">arrow_forward</span>
                            </div>
                        </div>
                        {{-- JS moved below --}}
                    @endforeach
                @endif
                <!-- Footer -->
                @if ($notifications->isNotEmpty())
                    <div class="py-2 border-t border-neutral-200">
                        <a href="{{ route('system.notifications') }}"
                            class="flex items-center justify-center px-2 py-2 hover:bg-neutral-100 rounded-md text-sm text-blue-600 hover:text-blue-700 font-medium">
                            View All Notifications
                        </a>
                    </div>
                @endif
            </div>

            <script>
                (function() {
                    const notificationsBtn = document.getElementById('notifications-btn');
                    const notificationsDropdown = document.getElementById('notifications-dropdown-menu');
                    const notificationsContainer = document.getElementById('notifications-dropdown');
                    if (notificationsBtn && notificationsDropdown) {
                        notificationsBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            notificationsDropdown.classList.toggle('hidden');
                        });
                        document.addEventListener('click', function(e) {
                            if (!notificationsContainer.contains(e.target)) {
                                notificationsDropdown.classList.add('hidden');
                            }
                        });
                        notificationsDropdown.addEventListener('click', function(e) {
                            e.stopPropagation();
                        });
                    }
                })();
            </script>
        </div>
    </div>
</div>

@include('notifications::components.read_actions')
