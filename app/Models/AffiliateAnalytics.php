<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AffiliateAnalytics extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'conversion_rate' => 'decimal:2',
        'revenue' => 'decimal:2',
        'country_breakdown' => 'array',
        'device_breakdown' => 'array',
        'traffic_sources' => 'array',
    ];

    /**
     * Get the affiliatable model (business offer or user post).
     */
    public function affiliatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope a query for the last N days.
     */
    public function scopeLastDays($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    /**
     * Get total views for this analytics record.
     */
    public function getTotalViewsAttribute(): int
    {
        return $this->views + $this->unique_views;
    }

    /**
     * Get total clicks for this analytics record.
     */
    public function getTotalClicksAttribute(): int
    {
        return $this->clicks + $this->unique_clicks;
    }

    /**
     * Calculate click-through rate (CTR).
     */
    public function getCtrAttribute(): float
    {
        if ($this->views == 0) {
            return 0;
        }

        return round(($this->clicks / $this->views) * 100, 2);
    }

    /**
     * Get top country from breakdown.
     */
    public function getTopCountryAttribute(): ?string
    {
        if (!$this->country_breakdown) {
            return null;
        }

        return array_keys($this->country_breakdown, max($this->country_breakdown))[0] ?? null;
    }

    /**
     * Get top device from breakdown.
     */
    public function getTopDeviceAttribute(): ?string
    {
        if (!$this->device_breakdown) {
            return null;
        }

        return array_keys($this->device_breakdown, max($this->device_breakdown))[0] ?? null;
    }

    /**
     * Get top traffic source from breakdown.
     */
    public function getTopTrafficSourceAttribute(): ?string
    {
        if (!$this->traffic_sources) {
            return null;
        }

        return array_keys($this->traffic_sources, max($this->traffic_sources))[0] ?? null;
    }
}
