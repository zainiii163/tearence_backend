<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderFollower extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'follower_id',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    // Check if user is following provider
    public static function isFollowing($providerId, $followerId)
    {
        return self::where('provider_id', $providerId)
                   ->where('follower_id', $followerId)
                   ->exists();
    }

    // Follow a provider
    public static function follow($providerId, $followerId)
    {
        if (!self::isFollowing($providerId, $followerId)) {
            return self::create([
                'provider_id' => $providerId,
                'follower_id' => $followerId,
            ]);
        }
        return null;
    }

    // Unfollow a provider
    public static function unfollow($providerId, $followerId)
    {
        return self::where('provider_id', $providerId)
                   ->where('follower_id', $followerId)
                   ->delete();
    }

    // Get follower count for provider
    public static function getFollowerCount($providerId)
    {
        return self::where('provider_id', $providerId)->count();
    }

    // Get following count for user
    public static function getFollowingCount($followerId)
    {
        return self::where('follower_id', $followerId)->count();
    }
}
