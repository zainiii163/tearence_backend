<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FundingProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'tagline',
        'project_type',
        'category',
        'description',
        'problem_solving',
        'vision_mission',
        'why_now',
        'cover_image',
        'additional_images',
        'country',
        'city',
        'funding_goal',
        'currency',
        'minimum_contribution',
        'funding_model',
        'use_of_funds',
        'milestones',
        'team_members',
        'identity_verification',
        'business_registration_number',
        'business_registration_document',
        'website',
        'social_links',
        'pitch_video',
        'documents',
        'is_verified',
        'is_active',
        'is_featured',
        'is_sponsored',
        'is_promoted',
        'amount_raised',
        'backer_count',
        'views_count',
        'shares_count',
        'funding_starts_at',
        'funding_ends_at',
    ];

    protected $casts = [
        'additional_images' => 'array',
        'use_of_funds' => 'array',
        'milestones' => 'array',
        'team_members' => 'array',
        'social_links' => 'array',
        'documents' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_promoted' => 'boolean',
        'funding_goal' => 'decimal:2',
        'minimum_contribution' => 'decimal:2',
        'amount_raised' => 'decimal:2',
        'funding_starts_at' => 'datetime',
        'funding_ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(FundingPledge::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(FundingReward::class);
    }

    public function upsells(): HasMany
    {
        return $this->hasMany(FundingUpsell::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(FundingAnalytic::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'funding_favorites');
    }

    public function backers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'funding_pledges', 'funding_project_id', 'user_id');
    }

    public function getFundingPercentageAttribute(): float
    {
        if ($this->funding_goal == 0) {
            return 0;
        }
        return round(($this->amount_raised / $this->funding_goal) * 100, 2);
    }

    public function isFunded(): bool
    {
        return $this->amount_raised >= $this->funding_goal;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->funding_ends_at) {
            return null;
        }
        $days = now()->diffInDays($this->funding_ends_at, false);
        return max(0, (int)$days);
    }

    public function isActive(): bool
    {
        return $this->is_active
            && (!$this->funding_ends_at || $this->funding_ends_at->isFuture())
            && (!$this->funding_starts_at || $this->funding_starts_at->isPast());
    }

    public function getCompletedPledgesAmount(): float
    {
        return $this->pledges()
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getCompletedPledgesCount(): int
    {
        return $this->pledges()
            ->where('status', 'completed')
            ->count();
    }
}
