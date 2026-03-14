<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSave extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
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

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    // Methods
    public static function isSaved($jobId, $userId)
    {
        return self::where('job_id', $jobId)
                  ->where('user_id', $userId)
                  ->exists();
    }

    public static function saveJob($jobId, $userId)
    {
        if (!self::isSaved($jobId, $userId)) {
            $save = self::create([
                'job_id' => $jobId,
                'user_id' => $userId,
            ]);
            
            // Increment job's saves count
            $job = Job::find($jobId);
            if ($job) {
                $job->incrementSaves();
            }
            
            return $save;
        }
        
        return null;
    }

    public static function unsaveJob($jobId, $userId)
    {
        $save = self::where('job_id', $jobId)
                   ->where('user_id', $userId)
                   ->first();
        
        if ($save) {
            $save->delete();
            
            // Decrement job's saves count
            $job = Job::find($jobId);
            if ($job) {
                $job->decrement('saves_count');
            }
            
            return true;
        }
        
        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($save) {
            $save->job->incrementSaves();
        });

        static::deleted(function ($save) {
            $save->job->decrement('saves_count');
        });
    }
}
