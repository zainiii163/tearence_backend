<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class FeaturedAdvert extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'listing_id',
        'customer_id',
        'category_id',
        'country_id',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'advert_type',
        'condition',
        'images',
        'video_url',
        'contact_name',
        'contact_email',
        'contact_phone',
        'website',
        'country',
        'city',
        'latitude',
        'longitude',
        'upsell_tier',
        'upsell_price',
        'payment_status',
        'payment_reference',
        'starts_at',
        'expires_at',
        'is_active',
        'view_count',
        'save_count',
        'contact_count',
        'rating',
        'review_count',
        'is_verified_seller',
        'admin_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'upsell_price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_verified_seller' => 'boolean',
        'rating' => 'decimal:2',
        'view_count' => 'integer',
        'save_count' => 'integer',
        'contact_count' => 'integer',
        'review_count' => 'integer',
    ];

    /**
     * Upsell tier constants
     */
    const TIER_PROMOTED = 'promoted';
    const TIER_FEATURED = 'featured';
    const TIER_SPONSORED = 'sponsored';

    /**
     * Payment status constants
     */
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';

    /**
     * Advert type constants
     */
    const TYPE_PRODUCT = 'product';
    const TYPE_SERVICE = 'service';
    const TYPE_PROPERTY = 'property';
    const TYPE_JOB = 'job';
    const TYPE_EVENT = 'event';
    const TYPE_VEHICLE = 'vehicle';
    const TYPE_BUSINESS = 'business';
    const TYPE_EDUCATION = 'education';
    const TYPE_TRAVEL = 'travel';
    const TYPE_FASHION = 'fashion';
    const TYPE_ELECTRONICS = 'electronics';
    const TYPE_PETS = 'pets';
    const TYPE_HOME = 'home';
    const TYPE_HEALTH = 'health';
    const TYPE_MISC = 'misc';

    /**
     * Get the listing that owns the featured advert.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }

    /**
     * Get the customer that owns the featured advert.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the category that belongs to the featured advert.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    /**
     * Get the country that belongs to the featured advert.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    /**
     * Scope to get only active featured adverts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where('payment_status', self::PAYMENT_PAID)
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope to get adverts by upsell tier
     */
    public function scopeByTier(Builder $query, string $tier): Builder
    {
        return $query->where('upsell_tier', $tier);
    }

    /**
     * Scope to get sponsored adverts
     */
    public function scopeSponsored(Builder $query): Builder
    {
        return $query->byTier(self::TIER_SPONSORED);
    }

    /**
     * Scope to get featured adverts
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->byTier(self::TIER_FEATURED);
    }

    /**
     * Scope to get promoted adverts
     */
    public function scopePromoted(Builder $query): Builder
    {
        return $query->byTier(self::TIER_PROMOTED);
    }

    /**
     * Scope to get adverts by country
     */
    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to get adverts by city
     */
    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to get adverts by category
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to get adverts by type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('advert_type', $type);
    }

    /**
     * Scope to get adverts by price range
     */
    public function scopeByPriceRange(Builder $query, float $min, float $max = null): Builder
    {
        $query->where('price', '>=', $min);
        
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        
        return $query;
    }

    /**
     * Scope to get verified seller adverts
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified_seller', true);
    }

    /**
     * Scope to order by priority (sponsored first, then featured, then promoted)
     */
    public function scopeOrderByPriority(Builder $query): Builder
    {
        return $query->orderByRaw("
            CASE upsell_tier
                WHEN 'sponsored' THEN 1
                WHEN 'featured' THEN 2
                WHEN 'promoted' THEN 3
                ELSE 4
            END
        ")->orderByDesc('created_at');
    }

    /**
     * Scope to search adverts
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('country', 'LIKE', "%{$term}%")
              ->orWhere('city', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Get the main image URL
     */
    public function getMainImageAttribute(): ?string
    {
        if (empty($this->images)) {
            return null;
        }

        $mainImage = $this->images[0] ?? null;
        return $mainImage ? asset('storage/' . $mainImage) : null;
    }

    /**
     * Get formatted price with currency
     */
    public function getFormattedPriceAttribute(): string
    {
        if (!$this->price) {
            return 'Free';
        }

        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($this->price, 2);
    }

    /**
     * Get currency symbol
     */
    public function getCurrencySymbol(): string
    {
        $symbols = [
            'GBP' => '£',
            'USD' => '$',
            'EUR' => '€',
            'JPY' => '¥',
        ];

        return $symbols[$this->currency] ?? $this->currency . ' ';
    }

    /**
     * Check if advert is currently active
     */
    public function isCurrentlyActive(): bool
    {
        return $this->is_active 
            && $this->payment_status === self::PAYMENT_PAID
            && $this->starts_at->isPast()
            && $this->expires_at->isFuture();
    }

    /**
     * Check if advert is expiring soon (within 7 days)
     */
    public function isExpiringSoon(): bool
    {
        return $this->expires_at->diffInDays(now()) <= 7;
    }

    /**
     * Get days remaining until expiry
     */
    public function getDaysRemaining(): int
    {
        return max(0, $this->expires_at->diffInDays(now()));
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): int
    {
        $this->increment('view_count');
        return $this->view_count;
    }

    /**
     * Increment save count
     */
    public function incrementSaveCount(): int
    {
        $this->increment('save_count');
        return $this->save_count;
    }

    /**
     * Increment contact count
     */
    public function incrementContactCount(): int
    {
        $this->increment('contact_count');
        return $this->contact_count;
    }

    /**
     * Get upsell tier display name
     */
    public function getUpsellTierDisplayName(): string
    {
        $names = [
            self::TIER_PROMOTED => 'Promoted',
            self::TIER_FEATURED => 'Featured',
            self::TIER_SPONSORED => 'Sponsored',
        ];

        return $names[$this->upsell_tier] ?? 'Standard';
    }

    /**
     * Get payment status display name
     */
    public function getPaymentStatusDisplayName(): string
    {
        $names = [
            self::PAYMENT_PENDING => 'Pending',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_FAILED => 'Failed',
        ];

        return $names[$this->payment_status] ?? 'Unknown';
    }

    /**
     * Boot method to handle model events
     */
    protected static function booted()
    {
        static::creating(function ($featuredAdvert) {
            if (empty($featuredAdvert->slug)) {
                $featuredAdvert->slug = \Illuminate\Support\Str::slug($featuredAdvert->title) . '-' . uniqid();
            }
        });

        static::updating(function ($featuredAdvert) {
            // Update listing upsell status when featured advert changes
            if ($featuredAdvert->isDirty(['upsell_tier', 'expires_at', 'is_active'])) {
                if ($featuredAdvert->listing) {
                    $featuredAdvert->listing->update([
                        'is_featured' => $featuredAdvert->upsell_tier === self::TIER_FEATURED && $featuredAdvert->isCurrentlyActive(),
                        'is_sponsored' => $featuredAdvert->upsell_tier === self::TIER_SPONSORED && $featuredAdvert->isCurrentlyActive(),
                        'is_promoted' => $featuredAdvert->upsell_tier === self::TIER_PROMOTED && $featuredAdvert->isCurrentlyActive(),
                        'featured_expires_at' => $featuredAdvert->upsell_tier === self::TIER_FEATURED ? $featuredAdvert->expires_at : null,
                        'sponsored_expires_at' => $featuredAdvert->upsell_tier === self::TIER_SPONSORED ? $featuredAdvert->expires_at : null,
                        'promoted_expires_at' => $featuredAdvert->upsell_tier === self::TIER_PROMOTED ? $featuredAdvert->expires_at : null,
                    ]);
                }
            }
        });

        static::deleted(function ($featuredAdvert) {
            // Update listing upsell status when featured advert is deleted
            if ($featuredAdvert->listing) {
                $featuredAdvert->listing->update([
                    'is_featured' => false,
                    'is_sponsored' => false,
                    'is_promoted' => false,
                    'featured_expires_at' => null,
                    'sponsored_expires_at' => null,
                    'promoted_expires_at' => null,
                ]);
            }
        });
    }
}
