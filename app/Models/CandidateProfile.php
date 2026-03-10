<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'candidate_profile_id';
    protected $table = 'candidate_profiles';

    protected $fillable = [
        'customer_id',
        'headline',
        'summary',
        'skills',
        'cv_url',
        'location_id',
        'visibility',
        'is_featured',
        'featured_expires_at',
        'has_job_alerts_boost',
        'job_alerts_boost_expires_at',
    ];

    protected $casts = [
        'skills' => 'array',
        'is_featured' => 'boolean',
        'has_job_alerts_boost' => 'boolean',
        'featured_expires_at' => 'datetime',
        'job_alerts_boost_expires_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the profile.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the location for the profile.
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    /**
     * Get the upsells for the candidate profile.
     */
    public function upsells()
    {
        return $this->hasMany(CandidateUpsell::class, 'candidate_profile_id', 'candidate_profile_id');
    }

    /**
     * Check if featured status is active
     */
    public function isFeaturedActive(): bool
    {
        return $this->is_featured && 
               ($this->featured_expires_at === null || $this->featured_expires_at->isFuture());
    }

    /**
     * Check if job alerts boost is active
     */
    public function isJobAlertsBoostActive(): bool
    {
        return $this->has_job_alerts_boost && 
               ($this->job_alerts_boost_expires_at === null || $this->job_alerts_boost_expires_at->isFuture());
    }
}

