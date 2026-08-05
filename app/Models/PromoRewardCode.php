<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoRewardCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'uses_count',
        'valid_from',
        'valid_until',
        'applies_to',
        'is_active',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'applies_to' => 'array',
        'is_active' => 'boolean',
    ];

    public function isCurrentlyValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->valid_from && now()->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && now()->gt($this->valid_until)) {
            return false;
        }
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }
        return true;
    }

    public function appliesToTier(?string $tier): bool
    {
        if (!$this->applies_to || count($this->applies_to) === 0) {
            return true;
        }
        if (!$tier) {
            return true;
        }
        return in_array($tier, $this->applies_to, true);
    }

    /**
     * @return array{discount_amount: float, final_price: float, points_awarded: float, type: string, value: float}
     */
    public function calculateForPrice(float $originalPrice): array
    {
        $discount = 0.0;
        $points = 0.0;

        if ($this->type === 'percent') {
            $discount = round($originalPrice * ((float) $this->value / 100), 2);
        } elseif ($this->type === 'fixed') {
            $discount = min((float) $this->value, $originalPrice);
        } elseif ($this->type === 'points') {
            $points = (float) $this->value;
        }

        return [
            'discount_amount' => $discount,
            'final_price' => max(0, round($originalPrice - $discount, 2)),
            'points_awarded' => $points,
            'type' => $this->type,
            'value' => (float) $this->value,
        ];
    }

    public function incrementUses(): void
    {
        $this->increment('uses_count');
    }
}
