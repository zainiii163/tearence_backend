<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSeeker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'bio',
        'profile_photo',
        'cv_file',
        'portfolio_link',
        'linkedin_url',
        'github_url',
        'website_url',
        'experience_level',
        'years_of_experience',
        'education_level',
        'key_skills',
        'desired_role',
        'industries_interested',
        'salary_expectation_min',
        'salary_expectation_max',
        'salary_currency',
        'preferred_work_type',
        'is_remote_available',
        'country',
        'city',
        'latitude',
        'longitude',
        'location_name',
        'willing_to_relocate',
        'is_active',
        'is_featured',
        'is_sponsored',
        'is_promoted',
        'featured_until',
        'sponsored_until',
        'promoted_until',
        'views_count',
        'profile_contacts_count',
        'saves_count',
        'last_contact_at',
    ];

    protected $casts = [
        'salary_expectation_min' => 'decimal:2',
        'salary_expectation_max' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_remote_available' => 'boolean',
        'willing_to_relocate' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_promoted' => 'boolean',
        'featured_until' => 'datetime',
        'sponsored_until' => 'datetime',
        'promoted_until' => 'datetime',
        'last_contact_at' => 'datetime',
        'views_count' => 'integer',
        'profile_contacts_count' => 'integer',
        'saves_count' => 'integer',
        'years_of_experience' => 'integer',
        'key_skills' => 'array',
        'industries_interested' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function getKeySkillsArrayAttribute()
    {
        return $this->key_skills ? explode(',', $this->key_skills) : [];
    }

    public function getIndustriesInterestedArrayAttribute()
    {
        return $this->industries_interested ? explode(',', $this->industries_interested) : [];
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

    public function getSalaryExpectationRangeAttribute()
    {
        if ($this->salary_expectation_min && $this->salary_expectation_max) {
            return $this->salary_currency . ' ' . number_format($this->salary_expectation_min) . ' - ' . number_format($this->salary_expectation_max);
        }
        if ($this->salary_expectation_min) {
            return $this->salary_currency . ' ' . number_format($this->salary_expectation_min) . '+';
        }
        return 'Negotiable';
    }

    public function getPreferredWorkTypeLabelAttribute()
    {
        return [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'contract' => 'Contract',
            'temporary' => 'Temporary',
            'internship' => 'Internship',
            'remote' => 'Remote',
            'any' => 'Any',
        ][$this->preferred_work_type] ?? $this->preferred_work_type;
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

    public function incrementProfileContacts()
    {
        $this->increment('profile_contacts_count');
        $this->update(['last_contact_at' => now()]);
    }

    public function incrementSaves()
    {
        $this->increment('saves_count');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobSeeker) {
            if (empty($jobSeeker->salary_currency)) {
                $jobSeeker->salary_currency = 'USD';
            }
        });
    }
}
