<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueTracking extends Model
{
    use HasFactory;

    protected $primaryKey = 'revenue_id';
    protected $table = 'revenue_tracking';

    protected $fillable = [
        'revenue_type',
        'related_id',
        'customer_id',
        'upsell_type',
        'amount',
        'currency',
        'payment_method',
        'payment_transaction_id',
        'payment_status',
        'payment_date',
        'notes',
        'ad_type',
        'banner_id',
        'affiliate_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    /**
     * Get the customer that made the payment.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the banner associated with this revenue record.
     */
    public function banner()
    {
        return $this->belongsTo(Banner::class, 'banner_id');
    }

    /**
     * Get the affiliate associated with this revenue record.
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_id');
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope for a specific revenue type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('revenue_type', $type);
    }

    /**
     * Scope for ad-related revenue
     */
    public function scopeAdRevenue($query)
    {
        return $query->whereIn('revenue_type', ['banner_ad', 'affiliate_ad']);
    }

    /**
     * Scope for specific ad type
     */
    public function scopeByAdType($query, string $adType)
    {
        return $query->where('ad_type', $adType);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'completed' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}

