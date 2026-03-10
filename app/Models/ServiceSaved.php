<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSaved extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public static function toggleSave($userId, $serviceId)
    {
        $saved = self::where('user_id', $userId)
                     ->where('service_id', $serviceId)
                     ->first();

        if ($saved) {
            $saved->delete();
            return false; // Unsaved
        } else {
            self::create([
                'user_id' => $userId,
                'service_id' => $serviceId,
            ]);
            return true; // Saved
        }
    }

    public static function isSaved($userId, $serviceId): bool
    {
        return self::where('user_id', $userId)
                   ->where('service_id', $serviceId)
                   ->exists();
    }

    public static function getUserSavedServices($userId, $perPage = 12)
    {
        return self::with('service.user', 'service.category', 'service.reviews')
                   ->where('user_id', $userId)
                   ->latest()
                   ->paginate($perPage);
    }
}
