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
        'is_active' => 'boolean',
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
