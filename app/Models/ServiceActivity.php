<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceActivity extends Model
{
    protected $fillable = [
        'service_id',
        'user_id',
        'activity_type',
        'ip_address',
        'country',
        'city',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public static function logActivity($serviceId, $activityType, $userId = null, $description = null, $metadata = [])
    {
        return self::create([
            'service_id' => $serviceId,
            'user_id' => $userId,
            'activity_type' => $activityType,
            'ip_address' => request()->ip(),
            'country' => $metadata['country'] ?? null,
            'city' => $metadata['city'] ?? null,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public static function getLiveActivityFeed($limit = 20)
    {
        return self::with(['service', 'user'])
                   ->latest()
                   ->limit($limit)
                   ->get()
                   ->map(function ($activity) {
                       return [
                           'id' => $activity->id,
                           'type' => $activity->activity_type,
                           'description' => $activity->getActivityDescription(),
                           'service' => [
                               'id' => $activity->service->id,
                               'title' => $activity->service->title,
                           ],
                           'user' => $activity->user ? [
                               'id' => $activity->user->user_id,
                               'name' => $activity->user->name ?? 'Anonymous',
                           ] : null,
                           'location' => $activity->country ? "{$activity->city}, {$activity->country}" : null,
                           'created_at' => $activity->created_at->diffForHumans(),
                       ];
                   });
    }

    public function getActivityDescription(): string
    {
        $descriptions = [
            'view' => 'Viewed a service',
            'inquiry' => 'Made an inquiry about a service',
            'order' => 'Ordered a service',
            'review' => 'Reviewed a service',
            'save' => 'Saved a service',
            'share' => 'Shared a service',
        ];

        return $this->description ?? $descriptions[$this->activity_type] ?? 'Interacted with a service';
    }
}
