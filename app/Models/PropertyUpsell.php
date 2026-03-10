<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyUpsell extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'upsell_type',
        'price',
        'currency',
        'duration_days',
        'starts_at',
        'expires_at',
        'payment_status',
        'payment_method',
        'transaction_id',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected $dates = [
        'starts_at',
        'expires_at',
        'paid_at',
    ];

    public static function getUpsellTypes(): array
    {
        return [
            'promoted' => 'Promoted Listing',
            'featured' => 'Featured Listing',
            'sponsored' => 'Sponsored Listing',
        ];
    }

    public static function getPricing(): array
    {
        return [
            'promoted' => [
                7 => 29.99,
                14 => 49.99,
                30 => 79.99,
            ],
            'featured' => [
                7 => 49.99,
                14 => 89.99,
                30 => 149.99,
            ],
            'sponsored' => [
                7 => 99.99,
                14 => 179.99,
                30 => 299.99,
            ],
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getDurationAttribute(): string
    {
        return $this->duration_days . ' days';
    }

    public function getBenefitsAttribute(): array
    {
        $benefits = [
            'promoted' => [
                'Highlighted card',
                'Appears above standard listings',
                '"Promoted" badge',
            ],
            'featured' => [
                'Top of category',
                'Larger card',
                'Priority in search results',
                'Included in weekly email blast',
                '"Featured" badge',
            ],
            'sponsored' => [
                'Homepage placement',
                'Category top placement',
                'Included in homepage slider',
                'Social media promotion',
                '"Sponsored" badge',
                'Maximum visibility',
            ],
        ];

        return $benefits[$this->upsell_type] ?? [];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('upsell_type', $type);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }
}
