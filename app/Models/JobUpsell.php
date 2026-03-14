<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JobUpsell extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pricing_plan_id',
        'upsellable_type',
        'upsellable_id',
        'upsell_type',
        'price',
        'currency',
        'duration_months',
        'status',
        'activated_at',
        'expires_at',
        'cancelled_at',
        'payment_status',
        'payment_method',
        'transaction_id',
        'payment_notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_months' => 'integer',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(JobPricingPlan::class, 'pricing_plan_id');
    }

    public function upsellable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere('expires_at', '<', now());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('upsell_type', $type);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'active' => 'Active',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'yellow',
            'active' => 'green',
            'cancelled' => 'red',
            'expired' => 'gray',
        ][$this->status] ?? 'gray';
    }

    public function getPaymentStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ][$this->payment_status] ?? $this->payment_status;
    }

    public function getFormattedPriceAttribute()
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    public function getUpsellTypeLabelAttribute()
    {
        return [
            'promoted' => 'Promoted',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            'network' => 'Network-Wide Boost',
        ][$this->upsell_type] ?? $this->upsell_type;
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
            'expires_at' => now()->addMonths($this->duration_months),
        ]);

        // Update the upsellable item's promotion status
        if ($this->upsellable) {
            $this->upsellable->update([
                'promotion_type' => $this->upsell_type,
                'promotion_expires_at' => $this->expires_at,
            ]);
        }
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Reset the upsellable item's promotion status
        if ($this->upsellable) {
            $this->upsellable->update([
                'promotion_type' => 'basic',
                'promotion_expires_at' => null,
            ]);
        }
    }

    public function markAsPaid($transactionId = null, $paymentMethod = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'transaction_id' => $transactionId,
            'payment_method' => $paymentMethod,
        ]);

        // Auto-activate if payment is successful
        $this->activate();
    }

    public function markAsFailed($notes = null)
    {
        $this->update([
            'payment_status' => 'failed',
            'payment_notes' => $notes,
        ]);
    }

    public function refund($notes = null)
    {
        $this->update([
            'payment_status' => 'refunded',
            'payment_notes' => $notes,
        ]);

        // Cancel the upsell
        $this->cancel();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($upsell) {
            if (empty($upsell->currency)) {
                $upsell->currency = 'USD';
            }
            if (empty($upsell->status)) {
                $upsell->status = 'pending';
            }
            if (empty($upsell->payment_status)) {
                $upsell->payment_status = 'pending';
            }
        });

        static::updated(function ($upsell) {
            // Check if upsell expired and update status
            if ($upsell->isExpired() && $upsell->status === 'active') {
                $upsell->update(['status' => 'expired']);
                
                // Reset the upsellable item's promotion status
                if ($upsell->upsellable) {
                    $upsell->upsellable->update([
                        'promotion_type' => 'basic',
                        'promotion_expires_at' => null,
                    ]);
                }
            }
        });
    }
}

