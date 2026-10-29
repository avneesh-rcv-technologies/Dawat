<?php

namespace Modules\Notifications\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notifications\Models\Notification;

class SystemNotificationController extends Controller
{
    /**
     * Show all notifications for the current user
     */
    public function index()
    {
        $current = app('acl.currentUser');
        $user = $current['user'];
        $guardUsed = $current['guard'];
        if (!$user) {
            abort(401);
        }
        $notifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('notifications::all', compact('notifications', 'guardUsed'));
    }

    /**
     * Show a single notification and mark as read
     */
    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        $current = app('acl.currentUser');
        $user = $current['user'];
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
     * Mark a notification as read (AJAX)
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $current = app('acl.currentUser');
        $user = $current['user'];
        if (!$user || $notification->notifiable_id !== $user->id || $notification->notifiable_type !== get_class($user)) {
            abort(403);
        }
        if (is_null($notification->read_at)) {
            $notification->read_at = now();
            $notification->save();
        }
        return response()->json(['success' => true]);
    }
}
