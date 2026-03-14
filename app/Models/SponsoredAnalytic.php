<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsoredAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'advert_id',
        'user_id',
        'event_type',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the advert for this analytic.
     */
    public function advert()
    {
        return $this->belongsTo(SponsoredAdvert::class, 'advert_id');
    }

    /**
     * Get the user for this analytic.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
