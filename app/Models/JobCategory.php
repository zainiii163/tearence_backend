<?php

namespace App\Models;

use App\Support\JobSchema;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class JobCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'active',
        'sort_order',
        'jobs_count',
        'job_count',
        'trending',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'active' => 'boolean',
        'trending' => 'boolean',
        'sort_order' => 'integer',
        'jobs_count' => 'integer',
        'job_count' => 'integer',
    ];

    protected function categoryForeignKey(): string
    {
        return JobSchema::column('category');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, $this->categoryForeignKey());
    }

    public function activeJobs(): HasMany
    {
        $relation = $this->hasMany(Job::class, $this->categoryForeignKey());

        if (Schema::hasColumn('jobs', 'is_active')) {
            return $relation->where('is_active', true);
        }

        if (Schema::hasColumn('jobs', 'status')) {
            return $relation->whereIn('status', ['active', 'published', 'open']);
        }

        return $relation;
    }

    public function activeJobListings(): HasMany
    {
        return $this->activeJobs();
    }

    public function jobAlerts(): HasMany
    {
        return $this->hasMany(JobAlert::class);
    }

    public function getActiveJobsCountAttribute(): int
    {
        if (array_key_exists('active_job_listings_count', $this->attributes)) {
            return (int) $this->attributes['active_job_listings_count'];
        }

        if (array_key_exists('active_jobs_count', $this->attributes)) {
            return (int) $this->attributes['active_jobs_count'];
        }

        try {
            return (int) $this->activeJobs()->count();
        } catch (\Throwable) {
            return (int) ($this->jobs_count ?? $this->job_count ?? 0);
        }
    }

    public function scopeActive($query)
    {
        if (Schema::hasColumn('job_categories', 'is_active')) {
            return $query->where('is_active', true);
        }

        if (Schema::hasColumn('job_categories', 'active')) {
            return $query->where('active', true);
        }

        return $query;
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
