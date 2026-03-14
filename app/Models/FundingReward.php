<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundingReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'funding_project_id',
        'title',
        'description',
        'minimum_contribution',
        'limit',
        'claimed_count',
        'estimated_delivery_date',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'minimum_contribution' => 'decimal:2',
        'is_active' => 'boolean',
        'estimated_delivery_date' => 'date',
    ];

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(FundingPledge::class);
    }

    public function isLimitReached(): bool
    {
        if ($this->limit === null) {
            return false;
        }
        return $this->claimed_count >= $this->limit;
    }

    public function getAvailableCountAttribute(): int
    {
        if ($this->limit === null) {
            return PHP_INT_MAX;
        }
        return max(0, $this->limit - $this->claimed_count);
    }
}
