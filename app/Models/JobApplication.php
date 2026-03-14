<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'job_seeker_id',
        'full_name',
        'email',
        'phone',
        'location',
        'cover_letter',
        'cv_file_url',
        'portfolio_links',
        'expected_salary',
        'available_start_date',
        'additional_notes',
        'status',
        'employer_notes',
        'next_steps',
        'status_updated_at',
        'interview_date',
        'interview_type',
        'interview_notes',
        'viewed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'portfolio_links' => 'array',
        'available_start_date' => 'date',
        'interview_date' => 'datetime',
        'viewed_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    // Relationships
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(JobSeeker::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeViewed($query)
    {
        return $query->where('status', 'viewed');
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', 'shortlisted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeHired($query)
    {
        return $query->where('status', 'hired');
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return [
            'submitted' => 'Submitted',
            'viewed' => 'Viewed',
            'shortlisted' => 'Shortlisted',
            'interview_scheduled' => 'Interview Scheduled',
            'rejected' => 'Rejected',
            'hired' => 'Hired',
            'withdrawn' => 'Withdrawn',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return [
            'submitted' => 'blue',
            'viewed' => 'yellow',
            'shortlisted' => 'green',
            'interview_scheduled' => 'purple',
            'rejected' => 'red',
            'hired' => 'emerald',
            'withdrawn' => 'gray',
        ][$this->status] ?? 'gray';
    }

    public function getFormattedSalaryAttribute()
    {
        if (!$this->expected_salary) return 'Not specified';
        
        $range = explode('-', $this->expected_salary);
        if (count($range) === 2) {
            return '$' . number_format($range[0]) . ' - $' . number_format($range[1]);
        }
        
        return '$' . number_format($range[0]) . '+';
    }

    // Methods
    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isViewed()
    {
        return $this->status === 'viewed';
    }

    public function isShortlisted()
    {
        return $this->status === 'shortlisted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isHired()
    {
        return $this->status === 'hired';
    }

    public function markAsViewed()
    {
        $this->update([
            'status' => 'viewed',
            'viewed_at' => now(),
            'status_updated_at' => now(),
        ]);
    }

    public function markAsShortlisted($notes = null)
    {
        $this->update([
            'status' => 'shortlisted',
            'employer_notes' => $notes,
            'status_updated_at' => now(),
        ]);
    }

    public function markAsRejected($notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'employer_notes' => $notes,
            'status_updated_at' => now(),
        ]);
    }

    public function markAsHired($notes = null)
    {
        $this->update([
            'status' => 'hired',
            'employer_notes' => $notes,
            'status_updated_at' => now(),
        ]);
    }

    public function scheduleInterview($date, $type, $notes = null)
    {
        $this->update([
            'status' => 'interview_scheduled',
            'interview_date' => $date,
            'interview_type' => $type,
            'interview_notes' => $notes,
            'status_updated_at' => now(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            if (empty($application->ip_address)) {
                $application->ip_address = request()->ip();
            }
            if (empty($application->user_agent)) {
                $application->user_agent = request()->userAgent();
            }
        });

        static::created(function ($application) {
            $application->job->incrementApplications();
        });
    }
}
