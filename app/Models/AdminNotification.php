<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Notification types
    const TYPE_NEW_POST = 'new_post';
    const TYPE_POST_PENDING = 'post_pending';
    const TYPE_HARMFUL_CONTENT = 'harmful_content';
    const TYPE_BULK_PENDING = 'bulk_pending';
    const TYPE_SYSTEM_ALERT = 'system_alert';

    /**
     * Get the user who owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Create a new admin notification
     */
    public static function createNotification(int $userId, string $type, string $message, array $data = []): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Create notification for all admins
     */
    public static function notifyAllAdmins(string $type, string $message, array $data = [])
    {
        $adminUsers = User::where('can_manage_listings', true)
            ->orWhere('is_super_admin', true)
            ->get();

        $notifications = [];
        foreach ($adminUsers as $admin) {
            $notifications[] = [
                'user_id' => $admin->user_id,
                'type' => $type,
                'message' => $message,
                'data' => $data,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        self::insert($notifications);
    }

    /**
     * Get unread notifications for user
     */
    public static function getUnreadForUser(int $userId)
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all notifications for user
     */
    public static function getAllForUser(int $userId, int $limit = 50)
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark all notifications as read for user
     */
    public static function markAllAsReadForUser(int $userId)
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Delete old notifications
     */
    public static function deleteOldNotifications(int $daysOld = 30)
    {
        return self::where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
