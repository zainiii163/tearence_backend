<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class JobListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_category_id',
        'title',
        'description',
        'responsibilities',
        'requirements',
        'skills_needed',
        'company_name',
        'company_website',
        'company_logo',
        'country',
        'city',
        'work_type',
        'experience_level',
        'education_level',
        'salary_range',
        'currency',
        'benefits',
        'application_method',
        'application_email',
        'application_url',
        'is_urgent',
        'is_featured',
        'is_sponsored',
        'is_promoted',
        'is_verified_employer',
        'is_active',
        'expires_at',
        'views_count',
        'applications_count',
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_promoted' => 'boolean',
        'is_verified_employer' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'views_count' => 'integer',
        'applications_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobCategory(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function savedBy(): HasMany
    {
        return $this->hasMany(JobSavedListing::class);
    }

    public function upsells(): MorphMany
    {
        return $this->morphMany(JobUpsell::class, 'upsellable');
    }

    public function activeUpsells(): MorphMany
    {
        return $this->morphMany(JobUpsell::class, 'upsellable')
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function getIsPromotedAttribute(): bool
    {
        return $this->activeUpsells()->whereIn('upsell_type', ['promoted', 'featured', 'sponsored', 'network_wide'])->exists();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementApplications(): void
    {
        $this->increment('applications_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByWorkType($query, $workType)
    {
        return $query->where('work_type', $workType);
    }
}
