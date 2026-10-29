<?php

namespace Modules\Notifications\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Notifications\Models\Notification;

class NotificationBellController extends Controller
{
    /**
     * Show all notifications page
     */
    public function index()
    {
        $user = Auth::user();
        $guardUsed = Auth::getDefaultDriver();
        
        if (!$user) {
            abort(401);
        }
        
        $notifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $hasUnreadNotifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('deleted_at')
            ->whereNull('read_at')
            ->exists();
            
        return view('notifications::all', compact('notifications', 'guardUsed', 'hasUnreadNotifications'));
    }

    /**
     * Show single notification and mark as read
     */
    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        $user = Auth::user();
        
        if (!$user || $notification->notifiable_id !== $user->id || $notification->notifiable_type !== get_class($user)) {
            abort(403);
        }
        
        if (is_null($notification->read_at)) {
            $notification->read_at = now();
            $notification->save();
        }
        
        return view('notifications::show', compact('notification'));
    }

    /**
     * Get unread notification count for badge
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $count = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for dropdown (last 10)
     */
    public function recent()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['notifications' => []]);
        }

        $notifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $this->extractTitle($notification),
                    'message' => $this->extractMessage($notification),
                    'time' => $notification->created_at->diffForHumans(),
                    'read' => !is_null($notification->read_at),
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        if (is_null($notification->read_at)) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Toggle a notification's read state.
     */
    public function toggleReadStatus($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->read_at = is_null($notification->read_at) ? now() : null;
        $notification->save();

        return response()->json([
            'success' => true,
            'read' => !is_null($notification->read_at),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Extract title from notification data
     */
    private function extractTitle($notification): string
    {
        // Decode data if it's a string
        $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
        
        if (is_array($data) && isset($data['title'])) {
            return $data['title'];
        }
        
        // Try to extract from type
        $type = class_basename($notification->type);
        
        // Convert OrderCreatedNotification -> Order Created
        $title = preg_replace('/Notification$/', '', $type);
        $title = preg_replace('/([a-z])([A-Z])/', '$1 $2', $title);
        
        return $title ?: 'Notification';
    }

    /**
     * Extract message from notification data
     */
    private function extractMessage($notification): string
    {
        // Decode data if it's a string
        $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
        
        if (is_string($notification->data) && !is_array($data)) {
            // Plain string data
            return \Illuminate\Support\Str::limit(strip_tags($notification->data), 100);
        }
        
        if (is_array($data)) {
            // Try common keys
            $message = $data['body'] 
                ?? $data['message'] 
                ?? $data['content'] 
                ?? '';
            
            return \Illuminate\Support\Str::limit(strip_tags($message), 100);
        }
        
        return 'New notification';
    }
}
