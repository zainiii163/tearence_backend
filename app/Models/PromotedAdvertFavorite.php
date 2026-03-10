<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotedAdvertFavorite extends Model
{
    use HasFactory;

    protected $table = 'promoted_advert_favorites';

    protected $fillable = [
        'promoted_advert_id',
        'user_id',
    ];

    /**
     * Get the promoted advert that owns the favorite.
     */
    public function promotedAdvert(): BelongsTo
    {
        return $this->belongsTo(PromotedAdvert::class, 'promoted_advert_id');
    }

    /**
     * Get the user that owns the favorite.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
