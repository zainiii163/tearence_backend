<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNotification extends Model
{
    protected $table = 'customer_notifications';

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public const TYPE_ADMIN = 'admin';
    public const TYPE_ADVERT_EXPIRING = 'advert_expiring';
    public const TYPE_PROMOTION_ENDING = 'promotion_ending';
    public const TYPE_FEATURED_ENDING = 'featured_ending';
    public const TYPE_SPONSORED_ENDING = 'sponsored_ending';
    public const TYPE_SUBSCRIPTION = 'subscription';
    public const TYPE_MESSAGE = 'message';
    public const TYPE_SALE = 'sale';
    public const TYPE_SYSTEM = 'system';

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public static function notify(
        int $customerId,
        string $type,
        string $message,
        ?string $title = null,
        array $data = []
    ): self {
        return self::create([
            'customer_id' => $customerId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function unreadCount(int $customerId): int
    {
        return self::where('customer_id', $customerId)->whereNull('read_at')->count();
    }

    public static function markAllRead(int $customerId): int
    {
        return self::where('customer_id', $customerId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
