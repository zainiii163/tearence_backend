<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FundingProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'title',
        'slug',
        'tagline',
        'project_type',
        'category',
        'description',
        'problem_solved',
        'vision_mission',
        'why_matters_now',
        'funding_goal',
        'minimum_contribution',
        'funding_model',
        'current_funded',
        'backers_count',
        'funding_deadline',
        'status',
        'risk_level',
        'is_verified',
        'is_featured',
        'is_promoted',
        'is_sponsored',
        'country',
        'region',
        'cover_image',
        'additional_images',
        'pitch_video_url',
        'team_members',
        'use_of_funds',
        'milestones',
        'social_links',
        'revenue_model',
        'forecasts',
        'risk_disclosures',
        'business_registration_number',
        'website',
        'verification_documents',
        'published_at',
    ];

    protected $casts = [
        'funding_goal' => 'decimal:2',
        'minimum_contribution' => 'decimal:2',
        'current_funded' => 'decimal:2',
        'funding_deadline' => 'date',
        'published_at' => 'datetime',
        'additional_images' => 'array',
        'team_members' => 'array',
        'use_of_funds' => 'array',
        'milestones' => 'array',
        'social_links' => 'array',
        'verification_documents' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_promoted' => 'boolean',
        'is_sponsored' => 'boolean',
    ];

    public static function getProjectTypes(): array
    {
        return [
            'personal' => 'Personal Project',
            'startup' => 'Startup / Business Project',
            'community' => 'Community / Charity Project',
            'creative' => 'Creative / Innovation Project',
        ];
    }

    public static function getCategories(): array
    {
        return [
            'technology' => 'Technology & Innovation',
            'creative_arts' => 'Creative Arts',
            'community_social_impact' => 'Community & Social Impact',
            'health_wellness' => 'Health & Wellness',
            'education' => 'Education',
            'real_estate' => 'Real Estate & Construction',
            'environment' => 'Environment & Sustainability',
            'startups_business' => 'Startups & Small Business',
            'other' => 'Other',
        ];
    }

    public static function getFundingModels(): array
    {
        return [
            'donation' => 'Donation-Based',
            'reward_based' => 'Reward-Based',
            'equity' => 'Equity-Based',
            'loan_based' => 'Loan-Based',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'active' => 'Active',
            'funded' => 'Funded',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function getRiskLevels(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ];
    }

    public function getFundingProgressAttribute(): float
    {
        if ($this->funding_goal == 0) return 0;
        return min(($this->current_funded / $this->funding_goal) * 100, 100);
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->diffInDays($this->funding_deadline, false));
    }

    public function getIsFundingActiveAttribute(): bool
    {
        return $this->status === 'active' && 
               $this->funding_deadline->isFuture() && 
               $this->current_funded < $this->funding_goal;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(FundingReward::class);
    }

    public function backers(): HasMany
    {
        return $this->hasMany(FundingBacker::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(FundingUpdate::class);
    }

    public function upsells(): HasMany
    {
        return $this->hasMany(FundingUpsell::class);
    }

    public function successfulBackers(): HasMany
    {
        return $this->backers()->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('published_at', '<=', now())
                    ->where('funding_deadline', '>', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePromoted($query)
    {
        return $query->where('is_promoted', true);
    }

    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeTrending($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30))
                    ->orderBy('current_funded', 'desc')
                    ->orderBy('backers_count', 'desc');
    }

    public function scopeEndingSoon($query)
    {
        return $query->where('funding_deadline', '<=', now()->addDays(7))
                    ->where('funding_deadline', '>', now())
                    ->orderBy('funding_deadline', 'asc');
    }

    public function scopeNearlyFunded($query)
    {
        return $query->whereRaw('(current_funded / funding_goal) >= 0.75')
                    ->whereRaw('(current_funded / funding_goal) < 1');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title);
            }
        });

        static::updating(function ($project) {
            if ($project->isDirty('title') && empty($project->slug)) {
                $project->slug = Str::slug($project->title);
            }
        });
    }
}
