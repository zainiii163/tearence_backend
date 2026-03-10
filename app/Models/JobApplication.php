<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'job_seeker_id',
        'cover_letter',
        'cv_file',
        'portfolio_link',
        'contact_email',
        'contact_phone',
        'status',
        'employer_notes',
        'applied_at',
        'viewed_at',
        'responded_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'viewed_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobSeeker()
    {
        return $this->belongsTo(JobSeeker::class);
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'viewed' => 'Viewed',
            'shortlisted' => 'Shortlisted',
            'rejected' => 'Rejected',
            'hired' => 'Hired',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'yellow',
            'viewed' => 'blue',
            'shortlisted' => 'green',
            'rejected' => 'red',
            'hired' => 'purple',
        ][$this->status] ?? 'gray';
    }

    public function isPending()
    {
        return $this->status === 'pending';
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
        ]);
    }

    public function markAsShortlisted()
    {
        $this->update([
            'status' => 'shortlisted',
            'responded_at' => now(),
        ]);
    }

    public function markAsRejected()
    {
        $this->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);
    }

    public function markAsHired()
    {
        $this->update([
            'status' => 'hired',
            'responded_at' => now(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            if (empty($application->applied_at)) {
                $application->applied_at = now();
            }
        });

        static::created(function ($application) {
            $application->job->incrementApplications();
        });
    }
}
