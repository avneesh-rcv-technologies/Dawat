@extends('backend::layouts.app')

@section('title', 'All Notifications')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold">All Notifications</h2>
            @if ($hasUnreadNotifications)
                <button type="button" class="notification-mark-all-read text-sm font-medium text-blue-600 hover:text-blue-700"
                    data-url="{{ route('notifications.bell.mark-all-as-read') }}">
                    Mark all read
                </button>
            @endif
        </div>
        @if($notifications->isEmpty())
            <div class="text-center text-gray-500 py-10">
                <span class="material-icons-outlined text-4xl mb-2">notifications_none</span>
                <div>No notifications found.</div>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($notifications as $notification)
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
                    <div class="flex items-start space-x-3 px-2 py-4 hover:bg-gray-50 transition-colors group {{ is_null($notification->read_at) ? 'bg-blue-50/40' : '' }}">
                            <span class="material-icons-outlined text-blue-500">notifications</span>
                            <a href="{{ route('system.notification-show', $notification->id) }}" class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                                    {{ $title }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ \Illuminate\Support\Str::limit($body, 150) }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">{{ $notification->created_at->format('M d, g:i A') }}</div>
                            </a>
                            <button type="button" class="notification-read-toggle flex-shrink-0 p-1 text-gray-400 hover:text-blue-600"
                                data-url="{{ route('notifications.bell.toggle-read-status', $notification->id) }}"
                                aria-label="{{ is_null($notification->read_at) ? 'Mark as read' : 'Mark as unread' }}"
                                title="{{ is_null($notification->read_at) ? 'Mark as read' : 'Mark as unread' }}">
                                <span class="material-icons-outlined text-base">{{ is_null($notification->read_at) ? 'mark_email_read' : 'mark_email_unread' }}</span>
                            </button>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

@include('notifications::components.read_actions')
@endsection
