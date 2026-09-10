<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionEntitlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'entitlement_type',
        'total',
        'used',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'total' => 'integer',
        'used' => 'integer',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function remaining(): int
    {
        return max(0, $this->total - $this->used);
    }

    public function hasRemaining(): bool
    {
        return $this->remaining() > 0;
    }

    public function consume(int $count = 1): bool
    {
        if (!$this->hasRemaining()) {
            return false;
        }
        $this->increment('used', $count);
        return true;
    }
}
