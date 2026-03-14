<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'price',
        'currency',
        'period',
        'features',
        'recommended',
        'active',
        'duration_months',
        'visibility_multiplier',
    ];

    protected $casts = [
        'features' => 'array',
        'recommended' => 'boolean',
        'active' => 'boolean',
        'price' => 'decimal:2',
        'duration_months' => 'integer',
        'visibility_multiplier' => 'integer',
    ];

    // Relationships
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function jobSeekers(): HasMany
    {
        return $this->hasMany(JobSeeker::class);
    }

    public function upsells(): HasMany
    {
        return $this->hasMany(JobUpsell::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('recommended', true);
    }

    public function scopeByPrice($query, $min = null, $max = null)
    {
        if ($min && $max) {
            return $query->whereBetween('price', [$min, $max]);
        } elseif ($min) {
            return $query->where('price', '>=', $min);
        } elseif ($max) {
            return $query->where('price', '<=', $max);
        }
        
        return $query;
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    public function getPeriodLabelAttribute()
    {
        return [
            'month' => 'per month',
            'week' => 'per week',
            'day' => 'per day',
            'year' => 'per year',
        ][$this->period] ?? $this->period;
    }

    public function getVisibilityLabelAttribute()
    {
        return $this->visibility_multiplier . 'x visibility';
    }

    // Methods
    public function isActive()
    {
        return $this->active;
    }

    public function isRecommended()
    {
        return $this->recommended;
    }

    public function getFeatureList()
    {
        return $this->features ?? [];
    }

    public function hasFeature($feature)
    {
        return in_array($feature, $this->features ?? []);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = str()->slug($plan->name);
            }
            if (empty($plan->currency)) {
                $plan->currency = 'USD';
            }
            if (empty($plan->period)) {
                $plan->period = 'month';
            }
            if (empty($plan->duration_months)) {
                $plan->duration_months = 1;
            }
            if (empty($plan->visibility_multiplier)) {
                $plan->visibility_multiplier = 1;
            }
        });

        static::updating(function ($plan) {
            if ($plan->isDirty('name') && empty($plan->slug)) {
                $plan->slug = str()->slug($plan->name);
            }
        });
    }
}
