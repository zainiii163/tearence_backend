<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceUpsell extends Model
{
    protected $fillable = [
        'service_id',
        'upsell_type',
        'price',
        'duration_days',
        'starts_at',
        'expires_at',
        'is_active',
        'payment_status',
        'transaction_id',
        'benefits',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'benefits' => 'array',
    ];

    // Upsell types and their benefits
    public static function getUpsellTypes()
    {
        return [
            'promoted' => [
                'name' => 'Promoted Listing',
                'price' => 19.99,
                'benefits' => [
                    'Highlighted listing',
                    'Appears above standard services',
                    'Promoted badge',
                    '2× more visibility',
                ]
            ],
            'featured' => [
                'name' => 'Featured Listing',
                'price' => 49.99,
                'benefits' => [
                    'Top of category pages',
                    'Larger service card',
                    'Priority in search results',
                    'Included in weekly Featured Services email',
                    'Featured badge',
                ]
            ],
            'sponsored' => [
                'name' => 'Sponsored Listing',
                'price' => 99.99,
                'benefits' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    'Sponsored badge',
                ]
            ],
            'network_boost' => [
                'name' => 'Network-Wide Boost',
                'price' => 199.99,
                'benefits' => [
                    'Appears across multiple pages',
                    'Services page placement',
                    'Homepage placement',
                    'Category pages placement',
                    'Related search pages placement',
                    'Included in newsletters',
                    'Included in push notifications',
                    'Top Spotlight badge',
                ]
            ],
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('upsell_type', $type);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function isActive(): bool
    {
        return $this->is_active && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function getRemainingDaysAttribute(): int
    {
        if (!$this->expires_at) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->expires_at));
    }
}
