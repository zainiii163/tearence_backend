<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsoredAdvertAnalytic extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sponsored_advert_analytics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sponsored_advert_id',
        'event_type',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'user_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the sponsored advert that owns the analytic.
     */
    public function sponsoredAdvert()
    {
        return $this->belongsTo(SponsoredAdvert::class, 'sponsored_advert_id');
    }

    /**
     * Get the user that owns the analytic.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
