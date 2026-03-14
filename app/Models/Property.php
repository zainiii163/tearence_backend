<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'tagline',
        'category',
        'property_type',
        'country',
        'city',
        'address',
        'latitude',
        'longitude',
        'price',
        'currency',
        'negotiable',
        'deposit',
        'service_charges',
        'maintenance_fees',
        'cover_image',
        'additional_images',
        'video_tour_link',
        'description',
        'specifications',
        'amenities',
        'location_highlights',
        'transport_links',
        'seller_name',
        'seller_company',
        'seller_phone',
        'seller_email',
        'seller_website',
        'seller_logo',
        'verified_agent',
        'advert_type',
        'promoted_until',
        'featured_until',
        'sponsored_until',
        'views',
        'saves',
        'enquiries',
        'active',
        'approved',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'service_charges' => 'decimal:2',
        'maintenance_fees' => 'decimal:2',
        'negotiable' => 'boolean',
        'verified_agent' => 'boolean',
        'additional_images' => 'array',
        'specifications' => 'array',
        'amenities' => 'array',
        'location_highlights' => 'array',
        'transport_links' => 'array',
        'promoted_until' => 'datetime',
        'featured_until' => 'datetime',
        'sponsored_until' => 'datetime',
    ];

    protected $dates = [
        'promoted_until',
        'featured_until',
        'sponsored_until',
        'deleted_at',
    ];

    public static function getPropertyTypes(): array
    {
        return [
            'residential' => 'Residential Property',
            'commercial' => 'Commercial Property',
            'industrial' => 'Industrial Property',
            'land' => 'Land / Plots',
            'agricultural' => 'Agricultural Land',
            'luxury' => 'Luxury Property',
            'short_term_rental' => 'Short-Term Rental / Holiday Home',
            'investment' => 'Investment Property',
            'new_development' => 'New Development / Off-Plan',
        ];
    }

    public static function getCategories(): array
    {
        return [
            'buy' => 'Buy',
            'rent' => 'Rent',
            'lease' => 'Lease',
            'auction' => 'Auction',
            'invest' => 'Invest',
        ];
    }

    public static function getCommercialTypes(): array
    {
        return [
            'office' => 'Office Space',
            'retail' => 'Retail Space',
            'warehouse' => 'Warehouse',
            'industrial' => 'Industrial Unit',
            'restaurant' => 'Restaurant/Cafe',
            'showroom' => 'Showroom',
        ];
    }

    public static function getLandTypes(): array
    {
        return [
            'residential' => 'Residential',
            'commercial' => 'Commercial',
            'agricultural' => 'Agricultural',
        ];
    }

    public static function getPlanningPermissions(): array
    {
        return [
            'approved' => 'Approved',
            'pending' => 'Pending',
            'none' => 'None',
        ];
    }

    public static function getViewTypes(): array
    {
        return [
            'sea' => 'Sea View',
            'mountain' => 'Mountain View',
            'skyline' => 'Skyline View',
            'garden' => 'Garden View',
            'pool' => 'Pool View',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(PropertyFavourite::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(PropertyAnalytic::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(PropertyEnquiry::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'AED' => 'د.إ',
            'SAR' => '﷼',
        ];

        $symbol = $currencySymbols[$this->currency] ?? $this->currency;
        
        if ($this->price >= 1000000) {
            return $symbol . number_format($this->price / 1000000, 2) . 'M';
        } elseif ($this->price >= 1000) {
            return $symbol . number_format($this->price / 1000, 1) . 'K';
        }
        
        return $symbol . number_format($this->price);
    }

    public function getFormattedSizeAttribute(): string
    {
        if ($this->property_size) {
            $unit = $this->size_unit === 'sq_ft' ? 'sq ft' : 'm²';
            return number_format($this->property_size) . ' ' . $unit;
        }
        
        return null;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->region,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured && $this->featured_until && $this->featured_until->isFuture();
    }

    public function getIsPromotedAttribute(): bool
    {
        return $this->promoted && $this->promoted_until && $this->promoted_until->isFuture();
    }

    public function getIsSponsoredAttribute(): bool
    {
        return $this->sponsored && $this->sponsored_until && $this->sponsored_until->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
                    ->where('approved', true);
    }

    public function scopePromoted($query)
    {
        return $query->where('advert_type', 'promoted')
                    ->where(function ($q) {
                        $q->whereNull('promoted_until')
                          ->orWhere('promoted_until', '>', now());
                    });
    }

    public function scopeFeatured($query)
    {
        return $query->where('advert_type', 'featured')
                    ->where(function ($q) {
                        $q->whereNull('featured_until')
                          ->orWhere('featured_until', '>', now());
                    });
    }

    public function scopeSponsored($query)
    {
        return $query->where('advert_type', 'sponsored')
                    ->where(function ($q) {
                        $q->whereNull('sponsored_until')
                          ->orWhere('sponsored_until', '>', now());
                    });
    }

    public function scopeByPropertyType($query, $type)
    {
        return $query->where('property_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocation($query, $country = null, $city = null)
    {
        if ($country) {
            $query->where('country', $country);
        }
        
        if ($city) {
            $query->where('city', $city);
        }
        
        return $query;
    }

    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('price', '>=', $min);
        }
        
        if ($max) {
            $query->where('price', '<=', $max);
        }
        
        return $query;
    }

    public function scopeBedrooms($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('bedrooms', '>=', $min);
        }
        
        if ($max) {
            $query->where('bedrooms', '<=', $max);
        }
        
        return $query;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title) . '-' . uniqid();
            }
        });

        static::updating(function ($property) {
            if ($property->isDirty('title') && empty($property->slug)) {
                $property->slug = Str::slug($property->title) . '-' . uniqid();
            }
        });
    }
}
