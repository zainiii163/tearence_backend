<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryMoneyFlow extends Model
{
    protected $guarded = [];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'platform_amount' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];

    public const BUCKET_PLATFORM = 'platform';
    public const BUCKET_SELLER = 'seller_payout';
    public const BUCKET_OTHER = 'other';

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForCategory($query, string $categoryKey)
    {
        return $query->where('category_key', $categoryKey);
    }

    public function scopeBucket($query, string $bucket)
    {
        return $query->where('bucket', $bucket);
    }
}
