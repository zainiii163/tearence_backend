<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobAlert extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'job_alert_id';
    protected $table = 'job_alerts';

    protected $fillable = [
        'customer_id',
        'name',
        'keywords',
        'location_id',
        'category_id',
        'job_type',
        'salary_min',
        'salary_max',
        'frequency',
        'is_active',
        'notification_email',
        'last_notified_at',
        'last_matched_count',
    ];

    protected $casts = [
        'keywords' => 'array',
        'job_type' => 'array',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'is_active' => 'boolean',
        'last_notified_at' => 'datetime',
        'last_matched_count' => 'integer',
    ];

    /**
     * Get the customer that owns the alert.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the location for the alert.
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    /**
     * Get the category for the alert.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    /**
     * Check if alert should be notified based on frequency
     */
    public function shouldNotify(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->frequency === 'instant') {
            return true;
        }

        if ($this->last_notified_at === null) {
            return true;
        }

        $now = now();
        switch ($this->frequency) {
            case 'daily':
                return $this->last_notified_at->copy()->addDay()->isPast();
            case 'weekly':
                return $this->last_notified_at->copy()->addWeek()->isPast();
            default:
                return false;
        }
    }

    /**
     * Find matching jobs for this alert
     */
    public function findMatchingJobs($limit = 50)
    {
        $query = Listing::where('status', 'active')
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });

        // Keyword search
        $keywords = $this->keywords;
        // Ensure keywords is an array
        if (!is_array($keywords) && !($keywords instanceof \Countable)) {
            if (is_string($keywords)) {
                $decoded = json_decode($keywords, true);
                $keywords = is_array($decoded) ? $decoded : [];
            } else {
                $keywords = [];
            }
        }
        
        if (is_array($keywords) && count($keywords) > 0) {
            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                }
            });
        }

        // Location filter
        if ($this->location_id) {
            $query->where('location_id', $this->location_id);
        }

        // Category filter
        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        // Job type filter
        $jobType = $this->job_type;
        // Ensure job_type is an array
        if (!is_array($jobType) && !($jobType instanceof \Countable)) {
            if (is_string($jobType)) {
                $decoded = json_decode($jobType, true);
                $jobType = is_array($decoded) ? $decoded : [];
            } else {
                $jobType = [];
            }
        }
        
        if (is_array($jobType) && count($jobType) > 0) {
            $query->whereIn('job_type', $jobType);
        }

        // Salary range filter
        if ($this->salary_min) {
            $query->where(function($q) {
                $q->where('salary_min', '>=', $this->salary_min)
                  ->orWhere('salary_max', '>=', $this->salary_min);
            });
        }

        if ($this->salary_max) {
            $query->where(function($q) {
                $q->where('salary_max', '<=', $this->salary_max)
                  ->orWhere('salary_min', '<=', $this->salary_max);
            });
        }

        // Order by featured first, then by date
        $query->orderByRaw("CASE WHEN is_featured = 1 AND (featured_expires_at IS NULL OR featured_expires_at > NOW()) THEN 0 ELSE 1 END")
              ->orderBy('created_at', 'desc');

        return $query->with(['category', 'location', 'customer', 'currency'])
                    ->limit($limit)
                    ->get();
    }
}

