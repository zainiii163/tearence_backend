<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingPledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
