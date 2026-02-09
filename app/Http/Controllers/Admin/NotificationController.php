<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all notifications for the authenticated admin
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only admins can access notifications
        if (!$user->canManageListings() && !$user->is_super_admin) {
            return response()->json([
                'message' => 'You do not have permission to view admin notifications'
            ], 403);
        }

        $query = AdminNotification::where('user_id', $user->user_id);

        // Filter by read status
        if ($request->has('read')) {
            if ($request->boolean('read')) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        if (!$user->canManageListings() && !$user->is_super_admin) {
            return response()->json([
                'message' => 'You do not have permission to view notifications'
            ], 403);
        }

        $count = AdminNotification::getUnreadCount($user->user_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Unread count retrieved successfully',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        
        if (!$user->canManageListings() && !$user->is_super_admin) {
            return response()->json([
                'message' => 'You do not have permission to manage notifications'
            ], 403);
        }

        $notification = AdminNotification::where('id', $notificationId)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        if (!$user->canManageListings() && !$user->is_super_admin) {
            return response()->json([
                'message' => 'You do not have permission to manage notifications'
            ], 403);
        }

        $markedCount = AdminNotification::markAllAsReadForUser($user->user_id);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
            'data' => [
                'marked_count' => $markedCount
            ]
        ]);
    }

    /**
     * Delete a notification
     */
    public function delete($notificationId)
    {
        $user = Auth::user();
        
        if (!$user->canManageListings() && !$user->is_super_admin) {
            return response()->json([
                'message' => 'You do not have permission to delete notifications'
            ], 403);
        }

        $notification = AdminNotification::where('id', $notificationId)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Create a custom notification (for super admins)
     */
    public function createNotification(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_super_admin) {
            return response()->json([
                'message' => 'Only super admins can create notifications'
            ], 403);
        }

        $request->validate([
            'message' => 'required|string|max:500',
            'type' => 'required|in:system_alert,bulk_pending',
            'target_users' => 'nullable|array',
            'target_users.*' => 'integer',
            'data' => 'nullable|array',
        ]);

        try {
            if ($request->has('target_users') && count($request->target_users) > 0) {
                // Create notifications for specific users
                $notifications = [];
                foreach ($request->target_users as $userId) {
                    $notifications[] = [
                        'user_id' => $userId,
                        'type' => $request->type,
                        'message' => $request->message,
                        'data' => $request->data ?? [],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                AdminNotification::insert($notifications);
                $createdCount = count($notifications);
            } else {
                // Create notifications for all admins
                AdminNotification::notifyAllAdmins(
                    $request->type,
                    $request->message,
                    $request->data ?? []
                );
                $createdCount = User::where('can_manage_listings', true)
                    ->orWhere('is_super_admin', true)
                    ->count();
            }

            return response()->json([
                'status' => 'success',
                'message' => "Notification created for {$createdCount} admin(s)",
                'data' => [
                    'created_count' => $createdCount,
                    'type' => $request->type,
                    'message' => $request->message
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function getStats()
    {
        $user = Auth::user();
        
        if (!$user->canManageListings() && !$user->is_super_admin) {
            return response()->json([
                'message' => 'You do not have permission to view notification statistics'
            ], 403);
        }

        $stats = [
            'total_notifications' => AdminNotification::where('user_id', $user->user_id)->count(),
            'unread_notifications' => AdminNotification::getUnreadCount($user->user_id),
            'read_notifications' => AdminNotification::where('user_id', $user->user_id)
                ->whereNotNull('read_at')
                ->count(),
        ];

        // Breakdown by type
        $typeStats = AdminNotification::where('user_id', $user->user_id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        $stats['by_type'] = $typeStats;

        return response()->json([
            'status' => 'success',
            'message' => 'Notification statistics retrieved successfully',
            'data' => $stats
        ]);
    }

    /**
     * Clean up old notifications
     */
    public function cleanup(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_super_admin) {
            return response()->json([
                'message' => 'Only super admins can clean up notifications'
            ], 403);
        }

        $request->validate([
            'days_old' => 'required|integer|min:1|max:365'
        ]);

        $deletedCount = AdminNotification::deleteOldNotifications($request->days_old);

        return response()->json([
            'status' => 'success',
            'message' => "Cleaned up {$deletedCount} old notifications",
            'data' => [
                'deleted_count' => $deletedCount,
                'days_old' => $request->days_old
            ]
        ]);
    }
}
