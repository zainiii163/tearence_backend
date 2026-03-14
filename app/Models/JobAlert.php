<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'keywords',
        'location',
        'category',
        'work_type',
        'salary_range',
        'experience_level',
        'education_level',
        'remote_only',
        'frequency',
        'active',
        'last_sent_at',
        'jobs_sent_count',
    ];

    protected $casts = [
        'remote_only' => 'boolean',
        'active' => 'boolean',
        'last_sent_at' => 'datetime',
        'jobs_sent_count' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    // Accessors
    public function getFrequencyLabelAttribute()
    {
        return [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'instant' => 'Instant',
        ][$this->frequency] ?? $this->frequency;
    }

    // Methods
    public function shouldSend()
    {
        if (!$this->active) {
            return false;
        }

        if ($this->frequency === 'instant') {
            return true;
        }

        if (!$this->last_sent_at) {
            return true;
        }

        $now = now();
        switch ($this->frequency) {
            case 'daily':
                return $this->last_sent_at->copy()->addDay()->isPast();
            case 'weekly':
                return $this->last_sent_at->copy()->addWeek()->isPast();
            case 'monthly':
                return $this->last_sent_at->copy()->addMonth()->isPast();
            default:
                return false;
        }
    }

    public function findMatchingJobs($limit = 50)
    {
        $query = Job::active()->notExpired();

        // Keyword search
        if ($this->keywords) {
            $keywords = explode(',', $this->keywords);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('title', 'LIKE', "%{$keyword}%")
                      ->orWhere('description', 'LIKE', "%{$keyword}%")
                      ->orWhere('skills_needed', 'LIKE', "%{$keyword}%");
                }
            });
        }

        // Location filter
        if ($this->location) {
            $query->byLocation($this->location);
        }

        // Category filter
        if ($this->category) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        // Work type filter
        if ($this->work_type) {
            $query->byWorkType($this->work_type);
        }

        // Salary range filter
        if ($this->salary_range) {
            $range = explode('-', $this->salary_range);
            if (count($range) === 2) {
                $query->bySalaryRange($range[0], $range[1]);
            } else {
                $query->bySalaryRange($range[0]);
            }
        }

        // Experience level filter
        if ($this->experience_level) {
            $query->byExperienceLevel($this->experience_level);
        }

        // Education level filter
        if ($this->education_level) {
            $query->where('education_level', $this->education_level);
        }

        // Remote only filter
        if ($this->remote_only) {
            $query->remote();
        }

        // Order by promoted jobs first, then by posted date
        $query->orderByRaw("CASE WHEN promotion_type != 'basic' AND (promotion_expires_at IS NULL OR promotion_expires_at > NOW()) THEN 0 ELSE 1 END")
              ->orderBy('posted_at', 'desc');

        return $query->with(['category', 'user'])
                    ->limit($limit)
                    ->get();
    }

    public function markAsSent($jobsCount = 0)
    {
        $this->update([
            'last_sent_at' => now(),
            'jobs_sent_count' => $this->jobs_sent_count + $jobsCount,
        ]);
    }

    public function test()
    {
        $jobs = $this->findMatchingJobs(5);
        
        // Here you would send a test email to the user
        // For now, we'll just return the jobs count
        
        return [
            'alert_name' => $this->name,
            'matching_jobs_count' => $jobs->count(),
            'sample_jobs' => $jobs->take(3),
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($alert) {
            if (empty($alert->frequency)) {
                $alert->frequency = 'daily';
            }
            if (empty($alert->active)) {
                $alert->active = true;
            }
        });
    }
}

