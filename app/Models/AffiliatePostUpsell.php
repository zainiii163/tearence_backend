<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AffiliatePostUpsell extends Model
{
    use HasFactory;

    protected $table = 'affiliate_post_upsells';

    protected $fillable = [
        'user_id',
        'affiliate_upsell_plan_id',
        'affiliatable_id',
        'affiliatable_type',
        'amount_paid',
        'payment_status',
        'payment_transaction_id',
        'payment_method',
        'paid_at',
        'starts_at',
        'expires_at',
        'is_active',
        'admin_notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the upsell.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the upsell plan for this post upsell.
     */
    public function affiliateUpsellPlan(): BelongsTo
    {
        return $this->belongsTo(AffiliateUpsellPlan::class);
    }

    /**
     * Get the affiliatable model (business offer or user post).
     */
    public function affiliatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include active upsells.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('payment_status', 'paid')
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope a query to only include paid upsells.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include pending payment upsells.
     */
    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Check if the upsell is currently active.
     */
    public function getIsCurrentlyActiveAttribute()
    {
        return $this->is_active && 
               $this->payment_status === 'paid' &&
               $this->starts_at && 
               $this->starts_at->isPast() &&
               $this->expires_at && 
               $this->expires_at->isFuture();
    }

    /**
     * Get the formatted amount paid.
     */
    public function getFormattedAmountPaidAttribute()
    {
        return '$' . number_format($this->amount_paid, 2);
    }

    /**
     * Get the duration in days.
     */
    public function getDurationInDaysAttribute()
    {
        if (!$this->starts_at || !$this->expires_at) {
            return null;
        }
        
        return $this->starts_at->diffInDays($this->expires_at);
    }

    /**
     * Get the days remaining.
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->expires_at));
    }

    /**
     * Check if the upsell is expiring soon (within 7 days).
     */
    public function getIsExpiringSoonAttribute()
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return now()->diffInDays($this->expires_at) <= 7;
    }

    /**
     * Boot the model and set default dates.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($upsell) {
            if (!$upsell->starts_at) {
                $upsell->starts_at = now();
            }
            
            if (!$upsell->expires_at && $upsell->affiliateUpsellPlan) {
                $plan = $upsell->affiliateUpsellPlan;
                $upsell->expires_at = now()->addDays($plan->duration_days);
            }
        });

        static::updating(function ($upsell) {
            if ($upsell->isDirty('payment_status') && $upsell->payment_status === 'paid' && !$upsell->is_active) {
                $upsell->is_active = true;
                $upsell->paid_at = now();
                $upsell->starts_at = now();
                
                if ($upsell->affiliateUpsellPlan && !$upsell->expires_at) {
                    $plan = $upsell->affiliateUpsellPlan;
                    $upsell->expires_at = now()->addDays($plan->duration_days);
                }
            }
        });
    }
}
