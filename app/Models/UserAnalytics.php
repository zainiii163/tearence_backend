<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnalytics extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     */
    protected $table = 'user_analytics';
    
    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'analytics_id';

    protected $casts = [
        'metadata' => 'array',
        'event_date' => 'datetime',
    ];

    /**
     * Get the user that owns the analytics record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Scope a query to only include analytics for a specific event type.
     */
    public function scopeEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope a query to only include analytics within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include analytics from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('event_date', today());
    }

    /**
     * Scope a query to only include analytics from this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('event_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope a query to only include analytics from this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('event_date', now()->month)
                    ->whereYear('event_date', now()->year);
    }

    /**
     * Get event counts grouped by event type for a user.
     */
    public static function getEventCountsForUser($userId, $days = 30)
    {
        return self::where('user_id', $userId)
            ->where('event_date', '>=', now()->subDays($days))
            ->groupBy('event_type')
            ->selectRaw('event_type, COUNT(*) as count')
            ->pluck('count', 'event_type')
            ->toArray();
    }

    /**
     * Get daily activity for a user.
     */
    public static function getDailyActivityForUser($userId, $days = 30)
    {
        return self::where('user_id', $userId)
            ->where('event_date', '>=', now()->subDays($days))
            ->groupByRaw('DATE(event_date)')
            ->selectRaw('DATE(event_date) as date, COUNT(*) as count')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }
}
