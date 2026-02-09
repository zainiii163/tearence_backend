<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingAnalytics extends Model
{
    use HasFactory;

    protected $primaryKey = 'analytics_id';
    protected $table = 'listing_analytics';

    protected $fillable = [
        'listing_id',
        'customer_id',
        'event_type',
        'ip_address',
        'user_agent',
        'referrer',
        'source',
        'metadata',
        'event_date',
    ];

    protected $casts = [
        'metadata' => 'array',
        'event_date' => 'datetime',
    ];

    /**
     * Get the listing
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }

    /**
     * Get the customer (if logged in)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Scope for a specific event type
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for a specific listing
     */
    public function scopeForListing($query, int $listingId)
    {
        return $query->where('listing_id', $listingId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }
}
