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
            <span id="notification-unread-indicator" class="absolute -top-1 -right-1 relative flex" style="width: 8px; height: 8px; display: {{ $notifCount > 0 ? 'flex' : 'none' }};">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full bg-blue-500" style="width: 8px; height: 8px;"></span>
            </span>
    </button>

    <!-- Dropdown -->
    <div id="notifications-dropdown-menu" style="width: 350px;"
        class="hidden absolute right-0 top-9 rounded-lg bg-white shadow-lg border border-neutral-200 z-50">
        <!-- Arrow -->
        <div class="absolute right-4 -top-1 h-3 w-3 rotate-45 bg-white border-l border-t border-neutral-200"></div>
        <div class="p-1 max-h-[400px] overflow-y-auto">
            <div id="notification-bell-content">
                @include('notifications::components.bell-content')
            </div>

            <script>
                (function() {
                    const notificationsBtn = document.getElementById('notifications-btn');
                    const notificationsDropdown = document.getElementById('notifications-dropdown-menu');
                    const notificationsContainer = document.getElementById('notifications-dropdown');
                    if (notificationsBtn && notificationsDropdown) {
                        let loading = false;
                        const refreshNotifications = async () => {
                            if (loading || document.hidden) return;
                            loading = true;
                            try {
                                const response = await fetch(@js(route('notifications.bell.recent', ['fragment' => 1])), {
                                    credentials: 'same-origin', headers: { 'Accept': 'application/json' }
                                });
                                if (response.status === 401) {
                                    const data = await response.json();
                                    if (data.redirect_url) window.location.assign(data.redirect_url);
                                    return;
                                }
                                if (!response.ok) return;
                                const data = await response.json();
                                if (typeof data.html !== 'string') return;
                                document.getElementById('notification-bell-content').innerHTML = data.html;
                                document.getElementById('notification-unread-indicator').style.display = data.count > 0 ? 'flex' : 'none';
                            } catch (error) {
                                // Keep existing notifications visible if the connection is interrupted.
                            } finally {
                                loading = false;
                            }
                        };
                        setInterval(refreshNotifications, 30000);
                        document.addEventListener('visibilitychange', () => {
                            if (!document.hidden) refreshNotifications();
                        });
                        notificationsBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            notificationsDropdown.classList.toggle('hidden');
                            if (!notificationsDropdown.classList.contains('hidden')) refreshNotifications();
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
