<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class SponsoredAdvert extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sponsored_adverts';

    protected $primaryKey = 'sponsored_advert_id';

    protected $appends = ['id', 'status'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sponsorship_price' => 'decimal:2',
        'sponsorship_start_date' => 'datetime',
        'sponsorship_end_date' => 'datetime',
        'additional_images' => 'array',
        'social_links' => 'array',
        'tags' => 'array',
        'seo_meta' => 'array',
        'is_active' => 'boolean',
        'verified_seller' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($advert) {
            if (empty($advert->slug)) {
                $advert->slug = static::createUniqueSlug($advert->title);
            }
        });

        static::updating(function ($advert) {
            if ($advert->isDirty('title') && empty($advert->slug)) {
                $advert->slug = static::createUniqueSlug($advert->title);
            }
        });
    }

    /** Alias primary key as id for API consumers */
    public function getIdAttribute(): ?int
    {
        $key = $this->attributes['sponsored_advert_id'] ?? null;
        return $key !== null ? (int) $key : null;
    }

    /** Dashboard-friendly status derived from is_active + payment_status */
    public function getStatusAttribute(): string
    {
        if ((bool) ($this->attributes['is_active'] ?? false)) {
            return 'active';
        }

        return match ($this->payment_status ?? 'pending') {
            'pending' => 'pending',
            'failed' => 'failed',
            default => 'paused',
        };
    }

    /**
     * Get the user that posted the sponsored advert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the category for this sponsored advert.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SponsoredCategory::class, 'category_id');
    }

    /**
     * Get the analytics for the sponsored advert.
     */
    public function analytics(): MorphMany
    {
        return $this->morphMany(AnalyticsReport::class, 'analyzable');
    }

    /**
     * Scope a query to only include active sponsored adverts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get sponsored adverts by tier.
     */
    public function scopeByTier($query, $tier)
    {
        return $query->where('sponsorship_tier', $tier);
    }

    /**
     * Scope a query to get premium sponsored adverts.
     */
    public function scopePremium($query)
    {
        return $query->where('sponsorship_tier', 'premium');
    }

    /**
     * Scope a query to get plus sponsored adverts.
     */
    public function scopePlus($query)
    {
        return $query->where('sponsorship_tier', 'plus');
    }

    /**
     * Scope a query to get basic sponsored adverts.
     */
    public function scopeBasic($query)
    {
        return $query->where('sponsorship_tier', 'basic');
    }

    /**
     * Scope a query to get sponsored adverts by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category_id', $category);
    }

    /**
     * Scope a query to get sponsored adverts by country.
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope a query to get sponsored adverts by city.
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope a query to get sponsored adverts by advert type.
     */
    public function scopeByAdvertType($query, $advertType)
    {
        return $query->where('advert_type', $advertType);
    }

    /**
     * Scope a query to get verified seller sponsored adverts.
     */
    public function scopeVerifiedSeller($query)
    {
        return $query->where('verified_seller', true);
    }

    /**
     * Scope a query to get sponsored adverts within price range.
     */
    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }
        return $query;
    }

    /**
     * Scope a query to get sponsored adverts with active promotion.
     */
    public function scopeWithActivePromotion($query)
    {
        return $query->where(function($q) {
            $q->whereNull('sponsorship_end_date')
              ->orWhere('sponsorship_end_date', '>', now());
        });
    }

    /**
     * Scope a query to search sponsored adverts.
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('tagline', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('business_name', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('seller_name', 'LIKE', '%' . $searchTerm . '%');
        });
    }

    /**
     * Increment the view count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Increment the save count.
     */
    public function incrementSaves()
    {
        $this->increment('saves_count');
    }

    /**
     * Decrement the save count.
     */
    public function decrementSaves()
    {
        $this->decrement('saves_count');
    }

    /**
     * Increment the click count.
     */
    public function incrementClicks()
    {
        $this->increment('clicks_count');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        if ($this->price === null) {
            return 'N/A';
        }
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Get the main image URL.
     */
    public function getMainImageUrlAttribute()
    {
        if (isset($this->attributes['main_image']) && $this->attributes['main_image']) {
            $image = $this->attributes['main_image'];
            // If it's not already a full URL, prepend the storage URL
            if (!str_starts_with($image, 'http')) {
                return asset('storage/' . $image);
            }
            return $image;
        }
        return asset('img/NoImage.png');
    }

    /**
     * Get the main image URL (alias for frontend compatibility).
     */
    public function getImageAttribute()
    {
        return $this->getMainImageUrlAttribute();
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute()
    {
        if (isset($this->attributes['logo']) && $this->attributes['logo']) {
            return $this->attributes['logo'];
        }
        return asset('placeholder.png');
    }

    /**
     * Get the sponsored advert URL (slug-based).
     */
    public function getUrlAttribute()
    {
        return route('sponsored.show', $this->slug);
    }

    /**
     * Get the promotion status.
     */
    public function getPromotionStatusAttribute()
    {
        if ($this->sponsorship_end_date && $this->sponsorship_end_date->isPast()) {
            return 'Expired';
        }

        return ucfirst($this->sponsorship_tier);
    }

    /**
     * Check if the sponsored advert has active promotion.
     */
    public function hasActivePromotion()
    {
        return $this->sponsorship_end_date === null || $this->sponsorship_end_date->isFuture();
    }

    /**
     * Get the promotion badge color.
     */
    public function getBadgeColorAttribute()
    {
        return match($this->sponsorship_tier) {
            'premium' => 'amber',
            'plus' => 'blue',
            'basic' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get the promotion icon.
     */
    public function getPromotionIconAttribute()
    {
        return match($this->sponsorship_tier) {
            'premium' => 'crown',
            'plus' => 'zap',
            'basic' => 'star',
            default => 'star'
        };
    }

    /**
     * Create a unique slug.
     */
    public static function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
