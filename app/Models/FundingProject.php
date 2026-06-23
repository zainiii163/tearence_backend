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
        'customer_id',
        'title',
        'tagline',
        'project_type',
        'category',
        'description',
        'problem_solved',
        'vision_mission',
        'why_matters_now',
        'cover_image',
        'additional_images',
        'country',
        'region',
        'funding_goal',
        'currency',
        'minimum_contribution',
        'funding_model',
        'use_of_funds',
        'milestones',
        'team_members',
        'identity_verification',
        'business_registration_number',
        'website',
        'social_links',
        'pitch_video_url',
        'verification_documents',
        'is_verified',
        'is_active',
        'is_featured',
        'is_sponsored',
        'is_promoted',
        'current_funded',
        'backers_count',
        'views_count',
        'shares_count',
        'funding_deadline',
        'published_at',
        'status',
        'risk_level',
        'revenue_model',
        'forecasts',
        'risk_disclosures',
        'slug',
    ];

    protected $casts = [
        'additional_images' => 'array',
        'use_of_funds' => 'array',
        'milestones' => 'array',
        'team_members' => 'array',
        'social_links' => 'array',
        'verification_documents' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_promoted' => 'boolean',
        'funding_goal' => 'decimal:2',
        'minimum_contribution' => 'decimal:2',
        'current_funded' => 'decimal:2',
        'funding_deadline' => 'datetime',
        'published_at' => 'datetime',
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
        return round(($this->current_funded / $this->funding_goal) * 100, 2);
    }

    public function isFunded(): bool
    {
        return $this->current_funded >= $this->funding_goal;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->funding_deadline) {
            return null;
        }
        $days = now()->diffInDays($this->funding_deadline, false);
        return max(0, (int)$days);
    }

    public function isActive(): bool
    {
        return $this->is_active
            && (!$this->funding_deadline || $this->funding_deadline->isFuture());
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
