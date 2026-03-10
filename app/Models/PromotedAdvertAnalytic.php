<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotedAdvertAnalytic extends Model
{
    use HasFactory;

    protected $table = 'promoted_advert_analytics';

    protected $fillable = [
        'promoted_advert_id',
        'event_type',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the promoted advert that owns the analytic.
     */
    public function promotedAdvert(): BelongsTo
    {
        return $this->belongsTo(PromotedAdvert::class, 'promoted_advert_id');
    }

    /**
     * Get the user that owns the analytic.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include views.
     */
    public function scopeViews($query)
    {
        return $query->where('event_type', 'view');
    }

    /**
     * Scope a query to only include clicks.
     */
    public function scopeClicks($query)
    {
        return $query->where('event_type', 'click');
    }

    /**
     * Scope a query to only include saves.
     */
    public function scopeSaves($query)
    {
        return $query->where('event_type', 'save');
    }

    /**
     * Scope a query to only include inquiries.
     */
    public function scopeInquiries($query)
    {
        return $query->where('event_type', 'inquiry');
    }

    /**
     * Scope a query to only include events from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope a query to only include events from this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope a query to only include events from this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }
}
