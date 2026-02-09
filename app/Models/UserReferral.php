<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReferral extends Model
{
    use HasFactory;

    protected $guarded = ['user_referral_id'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_referral_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'referral_id',
        'referred_user_id',
        'referrer_user_id',
        'status',
        'registered_at',
        'completed_at',
        'referrer_discount_amount',
        'referred_discount_amount',
        'referrer_discount_type',
        'referred_discount_type',
        'referrer_discount_used',
        'referred_discount_used',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'registered_at' => 'datetime',
        'completed_at' => 'datetime',
        'referrer_discount_amount' => 'decimal:2',
        'referred_discount_amount' => 'decimal:2',
        'referrer_discount_used' => 'boolean',
        'referred_discount_used' => 'boolean',
    ];

    /**
     * Get the referral that created this user referral
     */
    public function referral()
    {
        return $this->belongsTo(Referral::class, 'referral_id', 'referral_id');
    }

    /**
     * Get the user who was referred
     */
    public function referredUser()
    {
        return $this->belongsTo(Customer::class, 'referred_user_id', 'customer_id');
    }

    /**
     * Get the user who referred
     */
    public function referrerUser()
    {
        return $this->belongsTo(Customer::class, 'referrer_user_id', 'customer_id');
    }

    /**
     * Mark as registered
     */
    public function markAsRegistered(): void
    {
        $this->status = 'pending';
        $this->registered_at = now();
        $this->save();
    }

    /**
     * Mark as completed (when referred user posts first listing)
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): void
    {
        $this->status = 'expired';
        $this->save();
    }

    /**
     * Check if referral is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if referral is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if referral is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Get referrer discount info
     */
    public function getReferrerDiscountInfo(): array
    {
        return [
            'amount' => $this->referrer_discount_amount,
            'type' => $this->referrer_discount_type,
            'used' => $this->referrer_discount_used,
            'description' => $this->referrer_discount_type === 'percentage' 
                ? "{$this->referrer_discount_amount}% discount"
                : "Fixed ${$this->referrer_discount_amount} discount",
        ];
    }

    /**
     * Get referred user discount info
     */
    public function getReferredDiscountInfo(): array
    {
        return [
            'amount' => $this->referred_discount_amount,
            'type' => $this->referred_discount_type,
            'used' => $this->referred_discount_used,
            'description' => $this->referred_discount_type === 'percentage' 
                ? "{$this->referred_discount_amount}% discount"
                : "Fixed ${$this->referred_discount_amount} discount",
        ];
    }

    /**
     * Use referrer discount
     */
    public function useReferrerDiscount(): bool
    {
        if ($this->referrer_discount_used || !$this->isCompleted()) {
            return false;
        }

        $this->referrer_discount_used = true;
        $this->save();
        return true;
    }

    /**
     * Use referred user discount
     */
    public function useReferredDiscount(): bool
    {
        if ($this->referred_discount_used) {
            return false;
        }

        $this->referred_discount_used = true;
        $this->save();
        return true;
    }

    /**
     * Scope to get pending referrals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get completed referrals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get expired referrals
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope to get referrals where discount is available
     */
    public function scopeWithAvailableDiscount($query, $userType = 'referrer')
    {
        if ($userType === 'referrer') {
            return $query->where('referrer_discount_used', false)
                        ->where('status', 'completed');
        } else {
            return $query->where('referred_discount_used', false);
        }
    }
}
