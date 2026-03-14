<?php

namespace App\Models;

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
        'category_id',
        'pricing_plan_id',
        'title',
        'description',
        'responsibilities',
        'requirements',
        'benefits',
        'skills_needed',
        'company_name',
        'company_description',
        'company_size',
        'company_industry',
        'company_founded',
        'logo_url',
        'company_website',
        'company_social',
        'country',
        'city',
        'state',
        'address',
        'latitude',
        'longitude',
        'work_type',
        'salary_range',
        'currency',
        'experience_level',
        'education_level',
        'remote_available',
        'application_method',
        'application_email',
        'application_phone',
        'application_website',
        'application_instructions',
        'status',
        'verified_employer',
        'terms_accepted',
        'accurate_info',
        'views',
        'applications_count',
        'saves_count',
        'expires_at',
        'promotion_type',
        'promotion_expires_at',
        'gallery',
    ];

    protected $casts = [
        'company_social' => 'array',
        'gallery' => 'array',
        'verified_employer' => 'boolean',
        'terms_accepted' => 'boolean',
        'accurate_info' => 'boolean',
        'remote_available' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'expires_at' => 'datetime',
        'promotion_expires_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
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
        return $query->where('status', 'active');
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
        return $query->where('promotion_type', '!=', 'basic')
                    ->where(function ($q) {
                        $q->whereNull('promotion_expires_at')
                          ->orWhere('promotion_expires_at', '>', now());
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
        return $query->where('remote_available', true);
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
        return $this->promotion_type !== 'basic' && 
               (!$this->promotion_expires_at || $this->promotion_expires_at->isFuture());
    }

    public function getFormattedSalaryAttribute()
    {
        if (!$this->salary_range) return 'Negotiable';
        
        $currency = $this->currency ?? 'USD';
        $range = explode('-', $this->salary_range);
        
        if (count($range) === 2) {
            return $currency . ' ' . number_format($range[0]) . ' - ' . number_format($range[1]);
        }
        
        return $currency . ' ' . number_format($range[0]) . '+';
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
        $this->increment('views');
        JobView::create([
            'job_id' => $this->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
        ]);
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

        static::deleting(function ($job) {
            $job->applications()->delete();
            $job->saves()->delete();
            $job->views()->delete();
            $job->upsells()->delete();
        });
    }
}
