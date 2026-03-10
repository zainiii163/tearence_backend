<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BannerAd extends Model
{
    use HasFactory;

    protected $table = 'banner_ads';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'business_name',
        'contact_person',
        'email',
        'phone',
        'website_url',
        'business_logo',
        'banner_type',
        'banner_size',
        'banner_image',
        'destination_link',
        'call_to_action',
        'key_selling_points',
        'offer_details',
        'validity_start',
        'validity_end',
        'banner_category_id',
        'country',
        'city',
        'target_countries',
        'target_audience',
        'promotion_tier',
        'promotion_price',
        'promotion_start',
        'promotion_end',
        'is_verified_business',
        'status',
        'is_active',
        'views_count',
        'clicks_count',
        'approved_at',
        'user_id',
    ];

    protected $casts = [
        'validity_start' => 'date',
        'validity_end' => 'date',
        'promotion_start' => 'date',
        'promotion_end' => 'date',
        'promotion_price' => 'decimal:2',
        'is_verified_business' => 'boolean',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'target_countries' => 'array',
        'target_audience' => 'array',
    ];

    protected $dates = [
        'validity_start',
        'validity_end',
        'promotion_start',
        'promotion_end',
        'approved_at',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bannerAd) {
            $bannerAd->slug = Str::slug($bannerAd->title);
        });

        static::updating(function ($bannerAd) {
            if ($bannerAd->isDirty('title')) {
                $bannerAd->slug = Str::slug($bannerAd->title);
            }
        });
    }

    /**
     * Get the user that owns the banner ad.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the category that owns the banner ad.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BannerCategory::class, 'banner_category_id');
    }

    /**
     * Scope a query to only include active banner ads.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope a query to only include promoted banner ads.
     */
    public function scopePromoted($query)
    {
        return $query->whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'network_boost']);
    }

    /**
     * Scope a query to only include featured banner ads.
     */
    public function scopeFeatured($query)
    {
        return $query->whereIn('promotion_tier', ['featured', 'sponsored', 'network_boost']);
    }

    /**
     * Scope a query to only include sponsored banner ads.
     */
    public function scopeSponsored($query)
    {
        return $query->whereIn('promotion_tier', ['sponsored', 'network_boost']);
    }

    /**
     * Scope a query to only include network boost banner ads.
     */
    public function scopeNetworkBoost($query)
    {
        return $query->where('promotion_tier', 'network_boost');
    }

    /**
     * Scope a query to only include banner ads in a specific category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('banner_category_id', $categoryId);
    }

    /**
     * Scope a query to only include banner ads in a specific country.
     */
    public function scopeInCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope a query to get most viewed banner ads.
     */
    public function scopeMostViewed($query, $limit = 10)
    {
        return $query->orderBy('views_count', 'desc')->limit($limit);
    }

    /**
     * Scope a query to get recently added banner ads.
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Increment the view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment the click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    /**
     * Get the click-through rate (CTR).
     */
    public function getCtrAttribute(): float
    {
        if ($this->views_count === 0) {
            return 0;
        }

        return round(($this->clicks_count / $this->views_count) * 100, 2);
    }

    /**
     * Check if the banner is currently promoted.
     */
    public function isCurrentlyPromoted(): bool
    {
        if ($this->promotion_tier === 'standard') {
            return false;
        }

        $now = now();
        return $this->promotion_start && $this->promotion_end &&
               $now->between($this->promotion_start, $this->promotion_end);
    }

    /**
     * Check if the banner is currently valid.
     */
    public function isCurrentlyValid(): bool
    {
        $now = now();
        
        if ($this->validity_start && $now->lt($this->validity_start)) {
            return false;
        }
        
        if ($this->validity_end && $now->gt($this->validity_end)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the promotion badge display name.
     */
    public function getPromotionBadgeAttribute(): string
    {
        return match($this->promotion_tier) {
            'promoted' => 'Promoted',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            'network_boost' => 'Top Spotlight',
            default => 'Standard',
        };
    }

    /**
     * Get the banner size display name.
     */
    public function getBannerSizeDisplayAttribute(): string
    {
        return match($this->banner_size) {
            '728x90' => 'Leaderboard (728×90)',
            '300x250' => 'Medium Rectangle (300×250)',
            '160x600' => 'Skyscraper (160×600)',
            '970x250' => 'Billboard (970×250)',
            '468x60' => 'Classic Banner (468×60)',
            '1080x1080' => 'Square Social (1080×1080)',
            default => $this->banner_size,
        };
    }

    /**
     * Get the full banner image URL.
     */
    public function getBannerImageUrlAttribute(): string
    {
        return asset('storage/banner-images/' . $this->banner_image);
    }

    /**
     * Get the full business logo URL.
     */
    public function getBusinessLogoUrlAttribute(): string
    {
        if (!$this->business_logo) {
            return asset('images/default-logo.png');
        }
        
        return asset('storage/business-logos/' . $this->business_logo);
    }
}
