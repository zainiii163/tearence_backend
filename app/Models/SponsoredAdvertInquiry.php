<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsoredAdvertInquiry extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sponsored_advert_inquiries';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sponsored_advert_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'inquiry_type',
        'status',
        'admin_response',
        'responded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'inquiry_type' => 'string',
        'status' => 'string',
        'responded_at' => 'datetime',
    ];

    /**
     * Get the sponsored advert for this inquiry.
     */
    public function sponsoredAdvert()
    {
        return $this->belongsTo(SponsoredAdvert::class, 'sponsored_advert_id');
    }

    /**
     * Get the user that made this inquiry.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include pending inquiries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include responded inquiries.
     */
    public function scopeResponded($query)
    {
        return $query->where('status', 'responded');
    }

    /**
     * Scope a query to only include closed inquiries.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
