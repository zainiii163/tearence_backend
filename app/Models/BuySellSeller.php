<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellSeller extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'name',
        'company',
        'phone',
        'email',
        'website',
        'logo',
        'is_verified',
        'verification_data',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verification_data' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(BuySellItem::class, 'item_id');
    }
}
