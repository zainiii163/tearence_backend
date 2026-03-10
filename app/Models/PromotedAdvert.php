<?php

namespace App\Models;

use App\Models\PromotedAdvertCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PromotedAdvert extends Model
{
    use HasFactory;

    protected $table = 'promoted_adverts';

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'key_features',
        'special_notes',
        'advert_type',
        'category_id',
        'country',
        'city',
        'latitude',
        'longitude',
        'location_privacy',
        'price',
        'currency',
        'price_type',
        'condition',
        'main_image',
        'additional_images',
        'video_link',
        'seller_name',
        'business_name',
        'phone',
        'email',
        'website',
        'social_links',
        'logo',
        'verified_seller',
        'promotion_tier',
        'promotion_price',
        'promotion_start',
        'promotion_end',
        'views_count',
        'saves_count',
        'clicks_count',
        'inquiries_count',
        'status',
        'is_active',
        'is_featured',
        'approved_at',
        'user_id',
    ];

    protected $casts = [
        'key_features' => 'array',
        'additional_images' => 'array',
        'social_links' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'price' => 'decimal:2',
        'promotion_price' => 'decimal:2',
        'verified_seller' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
        'promotion_start' => 'date',
        'promotion_end' => 'date',
    ];

    protected $dates = [
        'approved_at',
        'promotion_start',
        'promotion_end',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($advert) {
            $advert->slug = Str::slug($advert->title);
        });

        static::updating(function ($advert) {
            if ($advert->isDirty('title')) {
                $advert->slug = Str::slug($advert->title);
            }
        });
    }

    /**
     * Get the user that owns the promoted advert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category that owns the promoted advert.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PromotedAdvertCategory::class, 'category_id');
    }

    /**
     * Get the analytics for the promoted advert.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(PromotedAdvertAnalytic::class, 'promoted_advert_id');
    }

    /**
     * Get the favorites for the promoted advert.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(PromotedAdvertFavorite::class, 'promoted_advert_id');
    }

    /**
     * Get the users who favorited this promoted advert.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'promoted_advert_favorites', 'promoted_advert_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active promoted adverts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope a query to only include featured promoted adverts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to get promoted adverts by tier.
     */
    public function scopeByTier($query, $tier)
    {
        return $query->where('promotion_tier', $tier);
    }

    /**
     * Scope a query to get currently promoted adverts.
     */
    public function scopeCurrentlyPromoted($query)
    {
        $now = now()->format('Y-m-d');
        return $query->whereNotNull('promotion_start')
                    ->whereNotNull('promotion_end')
                    ->where('promotion_start', '<=', $now)
                    ->where('promotion_end', '>=', $now);
    }

    /**
     * Scope a query to only include promoted adverts in a specific category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to only include promoted adverts in a specific country.
     */
    public function scopeInCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope a query to only include promoted adverts of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('advert_type', $type);
    }

    /**
     * Scope a query to get most viewed promoted adverts.
     */
    public function scopeMostViewed($query, $limit = 10)
    {
        return $query->orderBy('views_count', 'desc')->limit($limit);
    }

    /**
     * Scope a query to get most saved promoted adverts.
     */
    public function scopeMostSaved($query, $limit = 10)
    {
        return $query->orderBy('saves_count', 'desc')->limit($limit);
    }

    /**
     * Scope a query to get recently added promoted adverts.
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
        
        // Track analytics
        $this->analytics()->create([
            'event_type' => 'view',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'country' => $this->country,
            'city' => $this->city,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Increment the click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
        
        // Track analytics
        $this->analytics()->create([
            'event_type' => 'click',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'country' => $this->country,
            'city' => $this->city,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Increment the saves count.
     */
    public function incrementSaves(): void
    {
        $this->increment('saves_count');
        
        // Track analytics
        $this->analytics()->create([
            'event_type' => 'save',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'country' => $this->country,
            'city' => $this->city,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Increment the inquiries count.
     */
    public function incrementInquiries(): void
    {
        $this->increment('inquiries_count');
        
        // Track analytics
        $this->analytics()->create([
            'event_type' => 'inquiry',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'country' => $this->country,
            'city' => $this->city,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Check if the advert is currently promoted.
     */
    public function isCurrentlyPromoted(): bool
    {
        $now = now();
        return $this->promotion_start && $this->promotion_end &&
               $now->between($this->promotion_start, $this->promotion_end);
    }

    /**
     * Get the promotion tier display name.
     */
    public function getPromotionTierDisplayAttribute(): string
    {
        return match($this->promotion_tier) {
            'promoted_basic' => 'Promoted Basic',
            'promoted_plus' => 'Promoted Plus',
            'promoted_premium' => 'Promoted Premium',
            'network_wide_boost' => 'Network-Wide Boost',
            default => 'Standard',
        };
    }

    /**
     * Get the promotion badge display name.
     */
    public function getPromotionBadgeAttribute(): string
    {
        return match($this->promotion_tier) {
            'promoted_basic' => 'Promoted',
            'promoted_plus' => 'Most Popular',
            'promoted_premium' => 'Premium',
            'network_wide_boost' => 'Top Spotlight',
            default => 'Standard',
        };
    }

    /**
     * Get the formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        if (!$this->price) {
            return $this->price_type === 'free' ? 'Free' : 'Price on request';
        }

        $symbol = match($this->currency) {
            'GBP' => '£',
            'USD' => '$',
            'EUR' => '€',
            default => $this->currency . ' ',
        };

        return $symbol . number_format($this->price, 2);
    }

    /**
     * Get the main image URL.
     */
    public function getMainImageUrlAttribute(): string
    {
        return asset('storage/promoted-adverts/' . $this->main_image);
    }

    /**
     * Get the additional images URLs.
     */
    public function getAdditionalImagesUrlsAttribute(): array
    {
        if (!$this->additional_images) {
            return [];
        }

        return collect($this->additional_images)->map(function ($image) {
            return asset('storage/promoted-adverts/' . $image);
        })->toArray();
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo) {
            return asset('images/default-logo.png');
        }
        
        return asset('storage/promoted-adverts/logos/' . $this->logo);
    }

    /**
     * Get the advert type display name.
     */
    public function getAdvertTypeDisplayAttribute(): string
    {
        return match($this->advert_type) {
            'product' => 'Product / Item for Sale',
            'service' => 'Service / Business Offer',
            'property' => 'Property / Real Estate',
            'vehicle' => 'Vehicle / Motors',
            'job' => 'Job / Vacancy',
            'event' => 'Event / Experience',
            'business' => 'Business Opportunity',
            'miscellaneous' => 'Miscellaneous / Other',
            default => ucwords(str_replace('_', ' ', $this->advert_type)),
        };
    }

    /**
     * Get the condition display name.
     */
    public function getConditionDisplayAttribute(): string
    {
        return match($this->condition) {
            'new' => 'New',
            'used' => 'Used',
            'not_applicable' => 'Not Applicable',
            default => ucwords(str_replace('_', ' ', $this->condition)),
        };
    }

    /**
     * Check if the advert is favorited by the current user.
     */
    public function isFavoritedByCurrentUser(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->favorites()->where('user_id', auth()->id())->exists();
    }
}
