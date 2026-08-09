<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingPledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'funding_project_id',
        'funding_reward_id',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'payment_method',
        'notes',
        'is_anonymous',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /** @deprecated Prefer customer() */
    public function user(): BelongsTo
    {
        return $this->customer();
    }

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(FundingReward::class, 'funding_reward_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
