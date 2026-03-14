<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobView extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'country',
        'city',
        'device_type',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
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
    public function scopeByJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByDevice($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    // Accessors
    public function getDeviceTypeLabelAttribute()
    {
        return [
            'desktop' => 'Desktop',
            'mobile' => 'Mobile',
            'tablet' => 'Tablet',
        ][$this->device_type] ?? $this->device_type;
    }

    // Methods
    public static function trackView($jobId, $userId = null)
    {
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();
        $referrer = request()->header('referer');
        
        // Check if this IP has already viewed this job recently (to prevent spam)
        $recentView = self::where('job_id', $jobId)
                        ->where('ip_address', $ipAddress)
                        ->where('created_at', '>', now()->subHours(1))
                        ->first();
        
        if (!$recentView) {
            $view = self::create([
                'job_id' => $jobId,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referrer' => $referrer,
                'country' => $this->getCountryFromIp($ipAddress),
                'city' => $this->getCityFromIp($ipAddress),
                'device_type' => $this->getDeviceTypeFromUserAgent($userAgent),
            ]);
            
            // Increment job's views count
            $job = Job::find($jobId);
            if ($job) {
                $job->incrementViews();
            }
            
            return $view;
        }
        
        return null;
    }

    private function getCountryFromIp($ip)
    {
        // This is a placeholder - you would integrate with a GeoIP service
        // like MaxMind GeoIP, IP-API, etc.
        return null;
    }

    private function getCityFromIp($ip)
    {
        // This is a placeholder - you would integrate with a GeoIP service
        return null;
    }

    private function getDeviceTypeFromUserAgent($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($view) {
            $view->job->incrementViews();
        });
    }
}
