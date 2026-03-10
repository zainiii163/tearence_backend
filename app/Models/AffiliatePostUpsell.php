<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliatePostUpsell extends Model
{
    use HasFactory;

    protected $table = 'affiliate_post_upsells';

    protected $fillable = [
        'affiliate_post_id',
        'upsell_plan_id',
        'customer_id',
        'amount_paid',
        'currency',
        'payment_method',
        'transaction_id',
        'payment_status',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the affiliate post that owns the upsell.
     */
    public function affiliatePost(): BelongsTo
    {
        return $this->belongsTo(AffiliatePost::class);
    }

    /**
     * Get the upsell plan for this post upsell.
     */
    public function upsellPlan(): BelongsTo
    {
        return $this->belongsTo(AffiliateUpsellPlan::class, 'upsell_plan_id');
    }

    /**
     * Get the customer that owns the upsell.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope a query to only include active upsells.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('payment_status', 'paid')
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>', now());
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
               $this->ends_at && 
               $this->ends_at->isFuture();
    }

    /**
     * Get the formatted amount paid.
     */
    public function getFormattedAmountPaidAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount_paid, 2);
    }

    /**
     * Get the duration in days.
     */
    public function getDurationInDaysAttribute()
    {
        if (!$this->starts_at || !$this->ends_at) {
            return null;
        }
        
        return $this->starts_at->diffInDays($this->ends_at);
    }

    /**
     * Get the days remaining.
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->ends_at) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->ends_at));
    }

    /**
     * Check if the upsell is expiring soon (within 7 days).
     */
    public function getIsExpiringSoonAttribute()
    {
        if (!$this->ends_at) {
            return false;
        }
        
        return now()->diffInDays($this->ends_at) <= 7;
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
            
            if (!$upsell->ends_at && $upsell->upsellPlan) {
                $plan = $upsell->upsellPlan;
                $upsell->ends_at = now()->add("{$plan->duration_value} {$plan->duration_type}");
            }
        });

        static::updating(function ($upsell) {
            if ($upsell->isDirty('payment_status') && $upsell->payment_status === 'paid' && !$upsell->is_active) {
                $upsell->is_active = true;
                $upsell->starts_at = now();
                
                if ($upsell->upsellPlan && !$upsell->ends_at) {
                    $plan = $upsell->upsellPlan;
                    $upsell->ends_at = now()->add("{$plan->duration_value} {$plan->duration_type}");
                }
            }
        });
    }
}
