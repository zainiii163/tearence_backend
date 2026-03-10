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
        'is_active',
    ];

    protected $casts = [
        'minimum_contribution' => 'decimal:2',
        'estimated_delivery_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function getRemainingSlotsAttribute(): int
    {
        if ($this->limit === null) return -1; // Unlimited
        return max(0, $this->limit - $this->claimed_count);
    }

    public function getIsSoldOutAttribute(): bool
    {
        return $this->limit !== null && $this->claimed_count >= $this->limit;
    }

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }

    public function backers(): HasMany
    {
        return $this->hasMany(FundingBacker::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('limit')
              ->orWhereRaw('claimed_count < limit');
        });
    }

    public function scopeByContribution($query, $amount)
    {
        return $query->where('minimum_contribution', '<=', $amount)
                    ->orderBy('minimum_contribution', 'desc');
    }
}
