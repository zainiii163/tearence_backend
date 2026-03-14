<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'video_path',
        'thumbnail_path',
        'duration',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(BuySellItem::class, 'item_id');
    }
}
