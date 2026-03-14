<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class JobSeeker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pricing_plan_id',
        'full_name',
        'profession',
        'bio',
        'profile_photo_url',
        'country',
        'city',
        'state',
        'latitude',
        'longitude',
        'years_of_experience',
        'key_skills',
        'education_level',
        'education_details',
        'experience_summary',
        'desired_role',
        'salary_expectation',
        'work_type_preference',
        'remote_availability',
        'preferred_locations',
        'preferred_industries',
        'portfolio_link',
        'linkedin_link',
        'github_link',
        'cv_file_url',
        'additional_links',
        'status',
        'terms_accepted',
        'accurate_info',
        'verified_profile',
        'views',
        'contact_count',
        'profile_views',
        'promotion_type',
        'promotion_expires_at',
    ];

    protected $casts = [
        'preferred_locations' => 'array',
        'preferred_industries' => 'array',
        'additional_links' => 'array',
        'remote_availability' => 'boolean',
        'terms_accepted' => 'boolean',
        'accurate_info' => 'boolean',
        'verified_profile' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'promotion_expires_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        return $query->where('status', 'active');
    }

    public function scopeByProfession($query, $profession)
    {
        return $query->where('profession', 'LIKE', "%{$profession}%");
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where(function ($q) use ($location) {
            $q->where('country', 'LIKE', "%{$location}%")
              ->orWhere('city', 'LIKE', "%{$location}%")
              ->orWhere('state', 'LIKE', "%{$location}%");
        });
    }

    public function scopeByExperience($query, $experience)
    {
        return $query->where('years_of_experience', $experience);
    }

    public function scopeRemote($query)
    {
        return $query->where('remote_availability', true);
    }

    public function scopePromoted($query)
    {
        return $query->where('promotion_type', '!=', 'basic')
                    ->where(function ($q) {
                        $q->whereNull('promotion_expires_at')
                          ->orWhere('promotion_expires_at', '>', now());
                    });
    }

    // Accessors
    public function getIsPromotionActiveAttribute()
    {
        return $this->promotion_type !== 'basic' && 
               (!$this->promotion_expires_at || $this->promotion_expires_at->isFuture());
    }

    public function getSkillsArrayAttribute()
    {
        return $this->key_skills ? explode(',', $this->key_skills) : [];
    }

    public function getFormattedSalaryAttribute()
    {
        if (!$this->salary_expectation) return 'Negotiable';
        
        $range = explode('-', $this->salary_expectation);
        if (count($range) === 2) {
            return '$' . number_format($range[0]) . ' - $' . number_format($range[1]);
        }
        
        return '$' . number_format($range[0]) . '+';
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
            'associate' => 'Associate Degree',
            'bachelor' => 'Bachelor\'s Degree',
            'master' => 'Master\'s Degree',
            'doctorate' => 'Doctorate',
        ][$this->education_level] ?? $this->education_level;
    }

    public function getWorkTypeLabelAttribute()
    {
        return [
            'Full-time' => 'Full-time',
            'Part-time' => 'Part-time',
            'Contract' => 'Contract',
            'Freelance' => 'Freelance',
        ][$this->work_type_preference] ?? $this->work_type_preference;
    }

    // Methods
    public function incrementViews()
    {
        $this->increment('views');
        $this->increment('profile_views');
    }

    public function incrementContacts()
    {
        $this->increment('contact_count');
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

        static::creating(function ($seeker) {
            if (empty($seeker->status)) {
                $seeker->status = 'active';
            }
        });

        static::deleting(function ($seeker) {
            $seeker->applications()->delete();
            $seeker->upsells()->delete();
        });
    }
}
