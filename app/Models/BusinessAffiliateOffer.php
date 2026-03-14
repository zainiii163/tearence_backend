<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BusinessAffiliateOffer extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    /**
     * Get the user that created the offer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
        
        // Also track in analytics
        $this->analytics()->create([
            'date' => now()->toDateString(),
            'views' => 1,
            'unique_views' => 1,
        ]);
    }

    /**
     * Increment clicks count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');
        
        // Also track in analytics
        $this->analytics()->create([
            'date' => now()->toDateString(),
            'clicks' => 1,
            'unique_clicks' => 1,
        ]);
    }
}
