<?php

namespace Modules\Notifications\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notifications\Models\Notification;
use Modules\Acl\Models\Customer;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated customer
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $customer = $request->user();
        \Log::info('Fetching notifications for customer ID: ' . ($customer ? $customer->id : 'null'));

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }


        $perPage = $request->input('per_page', 20);


        $status = $request->input('status'); // 'read' or 'unread'

        $notifications = Notification::where('notifiable_type', Customer::class)
            ->where('notifiable_id', $customer->id)
            ->when($status === 'read', function ($query) {
                $query->whereNotNull('read_at');
            })
            ->when($status === 'unread', function ($query) {
                $query->whereNull('read_at');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $unreadCount = Notification::where('notifiable_type', Customer::class)
            ->where('notifiable_id', $customer->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->items(),
                'unread_count' => $unreadCount,
                'pagination' => [
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Get unread notification count
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $count = Notification::where('notifiable_type', Customer::class)
            ->where('notifiable_id', $customer->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Mark notification as read
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $id)
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('notifiable_type', Customer::class)
            ->where('notifiable_id', $customer->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        if (is_null($notification->read_at)) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $updated = Notification::where('notifiable_type', Customer::class)
            ->where('notifiable_id', $customer->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data' => [
                'updated_count' => $updated
            ]
        ]);
    }

    /**
     * Delete a notification
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('notifiable_type', Customer::class)
            ->where('notifiable_id', $customer->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    public function clearAll(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        Notification::where('notifiable_type', Customer::class)
            ->where('notifiable_id', $customer->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }
}
