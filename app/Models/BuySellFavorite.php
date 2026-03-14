<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(BuySellItem::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
