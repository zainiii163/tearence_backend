<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'trending',
        'job_count',
        'active',
    ];

    protected $casts = [
        'trending' => 'boolean',
        'active' => 'boolean',
        'job_count' => 'integer',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function activeJobs(): HasMany
    {
        return $this->hasMany(Job::class)->where('status', 'active');
    }

    public function jobAlerts(): HasMany
    {
        return $this->hasMany(JobAlert::class);
    }

    public function getActiveJobsCountAttribute(): int
    {
        return $this->activeJobs()->count();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeTrending($query)
    {
        return $query->where('trending', true);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = str()->slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = str()->slug($category->name);
            }
        });
    }
}
