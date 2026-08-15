<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class BusinessAffiliateOffer extends Model
{
    use HasFactory;

    protected $guarded = [];

    /** Never expose on public marketplace payloads */
    protected $hidden = [
        'postback_token',
    ];

    protected $casts = [
        'allowed_traffic_types' => 'array',
        'promotional_assets' => 'array',
        'is_verified' => 'boolean',
        'is_promoted' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'drop_at' => 'datetime',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $offer) {
            if (empty($offer->postback_token)) {
                $offer->postback_token = Str::random(40);
            }
        });
    }

    public function ensurePostbackToken(): string
    {
        if (empty($this->postback_token)) {
            $this->forceFill(['postback_token' => Str::random(40)])->save();
        }

        return (string) $this->postback_token;
    }

    public function rotatePostbackToken(): string
    {
        $this->forceFill(['postback_token' => Str::random(40)])->save();

        return (string) $this->postback_token;
    }

    /**
     * Get the user that created the offer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the category for this offer.
     */
    public function affiliateCategory(): BelongsTo
    {
        return $this->belongsTo(AffiliateCategory::class);
    }

    /**
     * Get the applications for this offer.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(AffiliateApplication::class);
    }

    /**
     * Get the analytics for this offer.
     */
    public function analytics(): MorphMany
    {
        return $this->morphMany(AffiliateAnalytics::class, 'affiliatable');
    }

    /**
     * Get the upsells for this offer.
     */
    public function upsells(): MorphMany
    {
        return $this->morphMany(AffiliatePostUpsell::class, 'affiliatable');
    }

    /**
     * Scope a query to only include active offers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('status', 'approved')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope a query to only include verified offers.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include promoted offers.
     */
    public function scopePromoted($query)
    {
        return $query->where('is_promoted', true);
    }

    /**
     * Scope a query to only include featured offers.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include sponsored offers.
     */
    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    /**
     * Scope a query to only include paid offers.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Check if the offer is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active || $this->status !== 'approved') {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    /** Public marketplace / joinable: live and (if a cookie fee was charged) paid. */
    public function isJoinable(): bool
    {
        if (! $this->isCurrentlyActive()) {
            return false;
        }

        $status = (string) ($this->payment_status ?: 'paid');

        return $status === 'paid' || $status === '';
    }

    /**
     * YouTube Shopping-style deal / drop payload for the public marketplace.
     */
    public function shoppingActivity(): array
    {
        $price = (float) ($this->sale_price ?? 0);
        $compare = (float) ($this->compare_at_price ?? 0);
        $dropAt = $this->drop_at;
        $type = (string) ($this->promotion_type ?: 'none');

        $droppingSoon = false;
        if ($dropAt) {
            try {
                $droppingSoon = \Carbon\Carbon::parse($dropAt)->isFuture();
            } catch (\Throwable $e) {
                $droppingSoon = false;
            }
        }

        $onSale = $compare > 0 && $price > 0 && $compare > $price;
        $percentOff = $onSale ? (int) round((($compare - $price) / $compare) * 100) : null;

        if ($type === '' || $type === 'none') {
            if ($droppingSoon) {
                $type = 'product_drop';
            } elseif ($onSale) {
                $type = 'sale';
            } elseif (! empty($this->discount_code)) {
                $type = 'sale';
            }
        }

        $label = $this->promotion_label;
        if (! $label) {
            if ($type === 'product_drop') {
                $label = $droppingSoon ? 'Dropping soon' : 'Product drop';
            } elseif ($type === 'price_drop' && $onSale) {
                $label = 'Price drop';
            } elseif ($type === 'percent_off' && $percentOff) {
                $label = $percentOff.'% off';
            } elseif ($type === 'amount_off' && $onSale) {
                $label = '$'.number_format($compare - $price, 2).' off';
            } elseif ($onSale && $percentOff) {
                $label = $percentOff.'% off';
            } elseif (! empty($this->discount_code)) {
                $label = 'Code: '.$this->discount_code;
            }
        }

        $isoDrop = null;
        if ($dropAt instanceof \Carbon\CarbonInterface) {
            $isoDrop = $dropAt->toIso8601String();
        } elseif ($dropAt) {
            try {
                $isoDrop = \Carbon\Carbon::parse($dropAt)->toIso8601String();
            } catch (\Throwable $e) {
                $isoDrop = null;
            }
        }

        return [
            'type' => $type === 'none' ? null : $type,
            'label' => $label,
            'price' => $price > 0 ? $price : null,
            'sale_price' => $price > 0 ? $price : null,
            'compare_at_price' => $compare > 0 ? $compare : null,
            'discount_code' => $this->discount_code ?: null,
            'drop_at' => $isoDrop,
            'dropping_soon' => (bool) $droppingSoon,
            'on_sale' => (bool) $onSale,
            'percent_off' => $percentOff,
        ];
    }

    /**
     * Get the display commission text.
     */
    public function getDisplayCommissionAttribute(): string
    {
        return $this->commission_type === 'percentage' 
            ? $this->commission_rate . '%'
            : '$' . number_format($this->commission_rate, 2);
    }

    /**
     * Get the full URL for tracking link.
     */
    public function getFullTrackingLinkAttribute(): string
    {
        return $this->tracking_link ?? '#';
    }

    /**
     * Get the verification document URL.
     */
    public function getVerificationDocumentUrlAttribute(): ?string
    {
        if (!$this->verification_document) {
            return null;
        }

        $fileUpload = new FileUploadHelper();
        return $fileUpload->getFile($this->verification_document, 'verification');
    }

    /**
     * Get promotional assets URLs.
     */
    public function getPromotionalAssetsUrlsAttribute(): array
    {
        if (!$this->promotional_assets) {
            return [];
        }

        $fileUpload = new FileUploadHelper();
        $urls = [];

        foreach ($this->promotional_assets as $asset) {
            $urls[] = $fileUpload->getFile($asset, 'affiliate_assets');
        }

        return $urls;
    }

    /**
     * Increment views count (safe for unique daily analytics row).
     */
    public function incrementViews(): void
    {
        $this->increment('views');

        try {
            $row = $this->analytics()->firstOrCreate(
                ['date' => now()->toDateString()],
                [
                    'views' => 0,
                    'unique_views' => 0,
                    'clicks' => 0,
                    'unique_clicks' => 0,
                ]
            );
            $row->increment('views');
            $row->increment('unique_views');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Increment clicks count (safe for unique daily analytics row).
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');

        try {
            $row = $this->analytics()->firstOrCreate(
                ['date' => now()->toDateString()],
                [
                    'views' => 0,
                    'unique_views' => 0,
                    'clicks' => 0,
                    'unique_clicks' => 0,
                ]
            );
            $row->increment('clicks');
            $row->increment('unique_clicks');
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
