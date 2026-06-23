<?php

namespace App\Models;

use App\Support\JobSeekerSchema;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
        'is_remote_available' => 'boolean',
        'willing_to_relocate' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_promoted' => 'boolean',
        'salary_expectation_min' => 'decimal:2',
        'salary_expectation_max' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'featured_until' => 'datetime',
        'sponsored_until' => 'datetime',
        'promoted_until' => 'datetime',
        'last_contact_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(JobPricingPlan::class, 'pricing_plan_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function upsells(): MorphMany
    {
        return $this->morphMany(JobUpsell::class, 'upsellable');
    }

    // Scopes
    public function scopeActive($query)
    {
        if (JobSeekerSchema::usesActiveFlag()) {
            return $query->where('is_active', true);
        }

        if (JobSeekerSchema::usesStatusColumn()) {
            return $query->where('status', 'active');
        }

        return $query;
    }

    public function scopeByProfession($query, $profession)
    {
        $column = JobSeekerSchema::column('title');

        return $query->where($column, 'LIKE', "%{$profession}%");
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where(function ($q) use ($location) {
            $q->where('country', 'LIKE', "%{$location}%")
              ->orWhere('city', 'LIKE', "%{$location}%");

            if (Schema::hasColumn('job_seekers', 'state')) {
                $q->orWhere('state', 'LIKE', "%{$location}%");
            }
        });
    }

    public function scopeByExperience($query, $experience)
    {
        return $query->where('years_of_experience', $experience);
    }

    public function scopeRemote($query)
    {
        return $query->where(JobSeekerSchema::column('remote'), true);
    }

    public function scopePromoted($query)
    {
        return $query->where('is_featured', true)
                    ->where(function ($q) {
                        $q->whereNull('featured_until')
                          ->orWhere('featured_until', '>', now());
                    });
    }

    // Accessors
    public function getIsPromotionActiveAttribute()
    {
        return $this->is_featured && 
               (!$this->featured_until || $this->featured_until->isFuture());
    }

    public function getSkillsArrayAttribute()
    {
        return $this->key_skills ? explode(',', $this->key_skills) : [];
    }

    public function getFormattedSalaryAttribute()
    {
        if (!$this->salary_expectation_min && !$this->salary_expectation_max) return 'Negotiable';
        
        $currency = $this->salary_currency ?? 'USD';
        
        if ($this->salary_expectation_min && $this->salary_expectation_max) {
            return $currency . ' ' . number_format($this->salary_expectation_min) . ' - ' . number_format($this->salary_expectation_max);
        }
        
        if ($this->salary_expectation_min) {
            return $currency . ' ' . number_format($this->salary_expectation_min) . '+';
        }
        
        return 'Negotiable';
    }

    public function getExperienceLabelAttribute()
    {
        return [
            '0-1' => 'Less than 1 year',
            '1-3' => '1-3 years',
            '3-5' => '3-5 years',
            '5-10' => '5-10 years',
            '10+' => '10+ years',
        ][$this->years_of_experience] ?? $this->years_of_experience;
    }

    public function getEducationLevelLabelAttribute()
    {
        return [
            'high_school' => 'High School',
            'diploma' => 'Diploma',
            'bachelor' => 'Bachelor\'s Degree',
            'master' => 'Master\'s Degree',
            'phd' => 'PhD',
            'none' => 'None',
        ][$this->education_level] ?? $this->education_level;
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
            'any' => 'Any',
        ][$this->preferred_work_type] ?? $this->preferred_work_type;
    }

    // Methods
    public function incrementViews()
    {
        $this->increment(JobSeekerSchema::column('views'));
    }

    public function incrementContacts()
    {
        $this->increment(JobSeekerSchema::column('contacts'));
    }

    public function incrementProfileContacts(): void
    {
        $this->incrementContacts();
    }

    /**
     * Safely remove a profile and related records.
     */
    public static function deleteProfile(self $seeker): void
    {
        $cols = JobSeekerSchema::columns();

        foreach ([$cols['photo'], $cols['cv']] as $fileColumn) {
            $path = $seeker->getAttribute($fileColumn);
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if (Schema::hasTable('job_applications') && Schema::hasColumn('job_applications', 'job_seeker_id')) {
            DB::table('job_applications')->where('job_seeker_id', $seeker->id)->delete();
        }

        if (Schema::hasTable('job_upsells')) {
            DB::table('job_upsells')
                ->where('upsellable_type', self::class)
                ->where('upsellable_id', $seeker->id)
                ->delete();
        }

        $seeker->deleteQuietly();
    }

    public function hasSkills($skills)
    {
        if (is_string($skills)) {
            $skills = [$skills];
        }
        
        $seekerSkills = $this->skills_array;
        
        foreach ($skills as $skill) {
            if (in_array(strtolower($skill), array_map('strtolower', $seekerSkills))) {
                return true;
            }
        }
        
        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($seeker) {
            try {
                if (Schema::hasTable('job_applications') && Schema::hasColumn('job_applications', 'job_seeker_id')) {
                    $seeker->applications()->delete();
                }
            } catch (\Throwable $e) {
                report($e);
            }

            try {
                if (Schema::hasTable('job_upsells')) {
                    $seeker->upsells()->delete();
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
