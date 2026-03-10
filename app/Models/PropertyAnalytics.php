<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'event_type',
        'user_id',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public static function getEventTypes(): array
    {
        return [
            'view' => 'Property View',
            'inquiry' => 'Inquiry Sent',
            'save' => 'Property Saved',
            'share' => 'Property Shared',
            'contact_agent' => 'Agent Contacted',
            'map_view' => 'Map Viewed',
            'video_play' => 'Video Played',
            'gallery_view' => 'Gallery Viewed',
            'phone_click' => 'Phone Number Clicked',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByEvent($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }
}
