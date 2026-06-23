<?php

namespace App\Models;

use App\Support\JobSchema;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_category_id',
        'category_id',
        'title',
        'slug',
        'description',
        'responsibilities',
        'requirements',
        'skills_needed',
        'benefits',
        'company_name',
        'company_description',
        'company_size',
        'company_industry',
        'company_founded',
        'company_logo',
        'logo_url',
        'company_website',
        'company_social',
        'contact_email',
        'application_email',
        'application_link',
        'application_phone',
        'application_instructions',
        'country',
        'city',
        'state',
        'address',
        'latitude',
        'longitude',
        'location_name',
        'work_type',
        'experience_level',
        'education_level',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_range',
        'currency',
        'application_method',
        'is_active',
        'status',
        'is_remote',
        'remote_available',
        'is_verified_employer',
        'verified_employer',
        'terms_accepted',
        'accurate_info',
        'applications_count',
        'saves_count',
        'views_count',
        'views',
        'expires_at',
        'posted_at',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'is_verified_employer' => 'boolean',
        'terms_accepted' => 'boolean',
        'accurate_info' => 'boolean',
        'is_active' => 'boolean',
        'company_social' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, JobSchema::column('category'));
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(JobPricingPlan::class, 'pricing_plan_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(JobSave::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(JobView::class);
    }

    public function upsells(): MorphMany
    {
        return $this->morphMany(JobUpsell::class, 'upsellable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeVerified($query)
    {
        return $query->where('verified_employer', true);
    }

    public function scopePromoted($query)
    {
        return $query->where('is_featured', true)
                    ->where(function ($q) {
                        $q->whereNull('featured_until')
                          ->orWhere('featured_until', '>', now());
                    });
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where(function ($q) use ($location) {
            $q->where('country', 'LIKE', "%{$location}%")
              ->orWhere('city', 'LIKE', "%{$location}%")
              ->orWhere('state', 'LIKE', "%{$location}%");
        });
    }

    public function scopeByWorkType($query, $workType)
    {
        return $query->where('work_type', $workType);
    }

    public function scopeByExperienceLevel($query, $level)
    {
        return $query->where('experience_level', $level);
    }

    public function scopeRemote($query)
    {
        return $query->where(JobSchema::column('remote'), true);
    }

    public function scopeBySalaryRange($query, $minSalary, $maxSalary = null)
    {
        return $query->where(function ($q) use ($minSalary, $maxSalary) {
            if ($maxSalary) {
                $q->whereRaw("CAST(SUBSTRING_INDEX(salary_range, '-', 1) AS UNSIGNED) >= ?", [$minSalary])
                  ->whereRaw("CAST(SUBSTRING_INDEX(salary_range, '-', -1) AS UNSIGNED) <= ?", [$maxSalary]);
            } else {
                $q->whereRaw("CAST(SUBSTRING_INDEX(salary_range, '-', 1) AS UNSIGNED) >= ?", [$minSalary]);
            }
        });
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsPromotionActiveAttribute()
    {
        return $this->is_featured && 
               (!$this->featured_until || $this->featured_until->isFuture());
    }

    public function getFormattedSalaryAttribute()
    {
        if (!$this->salary_min && !$this->salary_max) return 'Negotiable';
        
        $currency = $this->salary_currency ?? 'USD';
        
        if ($this->salary_min && $this->salary_max) {
            return $currency . ' ' . number_format($this->salary_min) . ' - ' . number_format($this->salary_max);
        }
        
        if ($this->salary_min) {
            return $currency . ' ' . number_format($this->salary_min) . '+';
        }
        
        return 'Negotiable';
    }

    public function getWorkTypeLabelAttribute()
    {
        return [
            'Full-time' => 'Full-time',
            'Part-time' => 'Part-time',
            'Contract' => 'Contract',
            'Freelance' => 'Freelance',
            'Internship' => 'Internship',
            'Temporary' => 'Temporary',
        ][$this->work_type] ?? $this->work_type;
    }

    public function getExperienceLevelLabelAttribute()
    {
        return [
            'entry' => 'Entry Level',
            'mid' => 'Mid Level',
            'senior' => 'Senior Level',
            'executive' => 'Executive Level',
        ][$this->experience_level] ?? $this->experience_level;
    }

    public function getEducationLevelLabelAttribute()
    {
        return [
            'high_school' => 'High School',
            'associate' => 'Associate Degree',
            'bachelor' => 'Bachelor\'s Degree',
            'master' => 'Master\'s Degree',
            'doctorate' => 'Doctorate',
        ][$this->education_level] ?? $this->education_level;
    }

    // Methods
    public function incrementViews()
    {
        // Views are tracked via JobView records; avoid creating duplicates here.
    }

    public function incrementApplications()
    {
        $this->increment('applications_count');
    }

    public function incrementSaves()
    {
        $this->increment('saves_count');
    }

    public function isSavedByUser($userId)
    {
        return $this->saves()->where('user_id', $userId)->exists();
    }

    public function hasAppliedByUser($userId)
    {
        return $this->applications()->where('user_id', $userId)->exists();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->slug)) {
                $job->slug = str()->slug($job->title) . '-' . uniqid();
            }
            if (empty($job->expires_at)) {
                $job->expires_at = now()->addDays(30);
            }
        });

        static::updating(function ($job) {
            if ($job->isDirty('title') && empty($job->slug)) {
                $job->slug = str()->slug($job->title) . '-' . uniqid();
            }
        });
    }
}
