<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedAdvert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'advert_id',
    ];

    /**
     * Get the user who saved the advert.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the saved advert.
     */
    public function advert()
    {
        return $this->belongsTo(SponsoredAdvert::class, 'advert_id');
    }
}
