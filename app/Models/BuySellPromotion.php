<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'promotion_type',
        'price',
        'currency',
        'status',
        'starts_at',
        'expires_at',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'string',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'features' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(BuySellItem::class, 'item_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('promotion_type', $type);
    }
}
