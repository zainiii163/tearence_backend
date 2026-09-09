<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPromoCredit extends Model
{
    protected $fillable = [
        'customer_id',
        'business_id',
        'promo_reward_code_id',
        'code_snapshot',
        'platform',
        'tier',
        'quantity_total',
        'quantity_remaining',
        'duration_days',
        'source',
        'expires_at',
    ];

    protected $casts = [
        'quantity_total' => 'integer',
        'quantity_remaining' => 'integer',
        'duration_days' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function rewardCode(): BelongsTo
    {
        return $this->belongsTo(PromoRewardCode::class, 'promo_reward_code_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCreditUsage::class, 'customer_promo_credit_id');
    }

    public function isUsable(): bool
    {
        if ((int) $this->quantity_remaining < 1) {
            return false;
        }
        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        return true;
    }
}
