<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BuySellSavedAdvert extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'advert_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function advert(): BelongsTo
    {
        return $this->belongsTo(BuySellAdvert::class, 'advert_id');
    }
}
