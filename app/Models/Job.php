<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_category_id',
        'title',
        'slug',
        'description',
        'responsibilities',
        'requirements',
        'skills_needed',
        'benefits',
        'company_name',
        'company_website',
        'company_logo',
        'contact_email',
        'application_link',
        'application_method',
        'work_type',
        'experience_level',
        'education_level',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_type',
        'salary_negotiable',
        'country',
        'city',
        'latitude',
        'longitude',
        'location_name',
        'is_remote',
        'is_urgent',
        'is_verified_employer',
        'is_active',
        'is_featured',
        'is_sponsored',
        'is_promoted',
        'featured_until',
        'sponsored_until',
        'promoted_until',
        'expires_at',
        'views_count',
        'applications_count',
        'saves_count',
        'last_application_at',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_remote' => 'boolean',
        'is_urgent' => 'boolean',
        'is_verified_employer' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_promoted' => 'boolean',
        'salary_negotiable' => 'boolean',
        'featured_until' => 'datetime',
        'sponsored_until' => 'datetime',
        'promoted_until' => 'datetime',
        'expires_at' => 'datetime',
        'last_application_at' => 'datetime',
        'views_count' => 'integer',
        'applications_count' => 'integer',
        'saves_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function pendingApplications()
    {
        return $this->hasMany(JobApplication::class)->where('status', 'pending');
    }

    public function shortlistedApplications()
    {
        return $this->hasMany(JobApplication::class)->where('status', 'shortlisted');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getSalaryRangeAttribute()
    {
        if ($this->salary_min && $this->salary_max) {
            return $this->salary_currency . ' ' . number_format($this->salary_min) . ' - ' . number_format($this->salary_max);
        }
        if ($this->salary_min) {
            return $this->salary_currency . ' ' . number_format($this->salary_min) . '+';
        }
        return 'Negotiable';
    }

    public function getWorkTypeLabelAttribute()
    {
        return [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'contract' => 'Contract',
            'temporary' => 'Temporary',
            'internship' => 'Internship',
            'remote' => 'Remote',
        ][$this->work_type] ?? $this->work_type;
    }

    public function getExperienceLevelLabelAttribute()
    {
        return [
            'entry' => 'Entry Level',
            'junior' => 'Junior',
            'mid' => 'Mid Level',
            'senior' => 'Senior',
            'executive' => 'Executive',
        ][$this->experience_level] ?? $this->experience_level;
    }

    public function getEducationLevelLabelAttribute()
    {
        return [
            'high_school' => 'High School',
            'diploma' => 'Diploma',
            'bachelor' => 'Bachelor\'s Degree',
            'master' => 'Master\'s Degree',
            'phd' => 'PhD',
            'none' => 'No Education Requirement',
        ][$this->education_level] ?? $this->education_level;
    }

    public function getSalaryTypeLabelAttribute()
    {
        return [
            'hourly' => 'per hour',
            'monthly' => 'per month',
            'yearly' => 'per year',
            'project' => 'per project',
        ][$this->salary_type] ?? $this->salary_type;
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPromotionActive()
    {
        return ($this->is_featured && $this->featured_until && $this->featured_until->isFuture()) ||
               ($this->is_sponsored && $this->sponsored_until && $this->sponsored_until->isFuture()) ||
               ($this->is_promoted && $this->promoted_until && $this->promoted_until->isFuture());
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementApplications()
    {
        $this->increment('applications_count');
        $this->update(['last_application_at' => now()]);
    }

    public function incrementSaves()
    {
        $this->increment('saves_count');
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
        });
    }
}
