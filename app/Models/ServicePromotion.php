<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePromotion extends Model
{
    use HasFactory;

    protected $table = 'service_promotions';

    protected $fillable = [
        'service_id',
        'promotion_type',
        'price',
        'currency',
        'duration_days',
        'starts_at',
        'expires_at',
        'status',
        'benefits',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'benefits' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    public function getPromotionBadgeAttribute(): string
    {
        return match($this->promotion_type) {
            'promoted' => 'Promoted',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            'network_boost' => 'Top Spotlight',
            default => '',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('promotion_type', $type);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' 
               && $this->starts_at <= now() 
               && $this->expires_at > now();
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    // Static methods to get promotion pricing
    public static function getPromotionPricing(): array
    {
        return [
            'promoted' => [
                'name' => 'Promoted Listing',
                'price' => 29.99,
                'duration' => 30,
                'benefits' => [
                    'Highlighted listing',
                    'Appears above standard services',
                    'Promoted badge',
                    '2× more visibility'
                ]
            ],
            'featured' => [
                'name' => 'Featured Listing',
                'price' => 59.99,
                'duration' => 30,
                'benefits' => [
                    'Top of category pages',
                    'Larger service card',
                    'Priority in search results',
                    'Included in weekly Featured Services email',
                    'Featured badge'
                ]
            ],
            'sponsored' => [
                'name' => 'Sponsored Listing',
                'price' => 99.99,
                'duration' => 30,
                'benefits' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    'Sponsored badge'
                ]
            ],
            'network_boost' => [
                'name' => 'Network-Wide Boost',
                'price' => 199.99,
                'duration' => 30,
                'benefits' => [
                    'Appears across multiple pages',
                    'Services page',
                    'Homepage',
                    'Category pages',
                    'Related search pages',
                    'Included in newsletters',
                    'Included in push notifications',
                    'Top Spotlight badge'
                ]
            ]
        ];
    }
}
