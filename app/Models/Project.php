<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'project_type',
        'title',
        'tagline',
        'description',
        'story',
        'vision',
        'funding_model',
        'funding_goal',
        'current_funding',
        'currency',
        'start_date',
        'end_date',
        'status',
        'promotion_tier',
        'submitted_at',
        'metadata',
    ];

    protected $casts = [
        'funding_goal' => 'decimal:2',
        'current_funding' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'submitted_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'current_funding' => 0,
        'currency' => 'USD',
        'status' => 'draft',
        'promotion_tier' => 'basic',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fundingDetails(): HasOne
    {
        return $this->hasOne(ProjectFundingDetail::class);
    }

    public function verification(): HasOne
    {
        return $this->hasOne(ProjectVerification::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ProjectReward::class)->orderBy('order_index');
    }

    public function marketingAssets(): HasOne
    {
        return $this->hasOne(ProjectMarketingAsset::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(ProjectPromotion::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('project_type', $type);
    }

    public function scopeByFundingModel($query, $model)
    {
        return $query->where('funding_model', $model);
    }

    public function scopePromoted($query)
    {
        return $query->whereIn('promotion_tier', ['promoted', 'featured', 'sponsored']);
    }

    public function scopeFeatured($query)
    {
        return $query->where('promotion_tier', 'featured');
    }

    public function scopeSponsored($query)
    {
        return $query->where('promotion_tier', 'sponsored');
    }

    // Accessors
    public function getFundingProgressAttribute()
    {
        if ($this->funding_goal > 0) {
            return ($this->current_funding / $this->funding_goal) * 100;
        }
        return 0;
    }

    public function getDaysLeftAttribute()
    {
        if ($this->end_date) {
            return max(0, $this->end_date->diffInDays(now()));
        }
        return null;
    }

    public function getIsFullyFundedAttribute()
    {
        return $this->current_funding >= $this->funding_goal;
    }

    public function getSlugAttribute()
    {
        return Str::slug($this->title);
    }

    // Methods
    public function addFunding($amount)
    {
        $this->current_funding += $amount;
        $this->save();
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'active']);
    }

    public function isActive()
    {
        return $this->status === 'active' && 
               (!$this->end_date || $this->end_date->isFuture());
    }

    public function getActivePromotion()
    {
        return $this->promotions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>=', now());
            })
            ->first();
    }
}
