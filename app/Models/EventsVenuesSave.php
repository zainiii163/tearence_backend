<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventsVenuesSave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'advert_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function advert(): BelongsTo
    {
        return $this->belongsTo(EventsVenuesAdvert::class, 'advert_id');
    }
}
