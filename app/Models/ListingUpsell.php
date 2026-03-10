<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingUpsell extends Model
{
    protected $primaryKey = 'upsell_id';
    protected $table = 'listing_upsells';
    
    protected $fillable = [
        'listing_id',
        'customer_id',
        'upsell_type',
        'price',
        'duration_days',
        'starts_at',
        'expires_at',
        'status',
        'payment_status',
        'payment_details',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'payment_details' => 'array',
    ];

    // Upsell types
    const TYPE_PRIORITY = 'priority';     // Appears first in search
    const TYPE_FEATURED = 'featured';    // Featured badge
    const TYPE_SPONSORED = 'sponsored';  // Sponsored badge
    const TYPE_PREMIUM = 'premium';      // Premium placement

    // Statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Payment statuses
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';

    /**
     * Get the listing that owns this upsell
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }

    /**
     * Get the customer that owns this upsell
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Check if the upsell is currently active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE 
            && $this->expires_at > now()
            && $this->starts_at <= now();
    }

    /**
     * Check if the upsell is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    /**
     * Mark the upsell as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Get the priority score for search ranking
     */
    public function getPriorityScore(): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        return match($this->upsell_type) {
            self::TYPE_PREMIUM => 1000,
            self::TYPE_SPONSORED => 800,
            self::TYPE_FEATURED => 600,
            self::TYPE_PRIORITY => 400,
            default => 0,
        };
    }

    /**
     * Scope to get only active upsells
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope to get upsells by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('upsell_type', $type);
    }

    /**
     * Scope to get paid upsells
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }
}
