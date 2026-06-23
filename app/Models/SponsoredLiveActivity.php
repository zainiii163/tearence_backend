<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsoredLiveActivity extends Model
{
    use HasFactory;

    protected $table = 'sponsored_live_activity';

    protected $fillable = [
        'advert_id',
        'type',
        'message',
        'user_id',
        'category',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Get the advert for this activity.
     */
    public function advert()
    {
        return $this->belongsTo(SponsoredAdvert::class, 'advert_id');
    }

    /**
     * Get the user for this activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include recent activities.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('timestamp', '>=', now()->subHours($hours));
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
