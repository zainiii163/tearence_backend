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
        'category_id',
        'title',
        'slug',
        'tagline',
        'category', // buy, rent, lease, auction, invest
        'property_type',
        'country',
        'city',
        'region',
        'address',
        'latitude',
        'longitude',
        'show_exact_location',
        
        // Residential
        'bedrooms',
        'bathrooms',
        'property_size',
        'size_unit',
        'furnished',
        'parking_spaces',
        
        // Commercial
        'commercial_type',
        'floor_area',
        'footfall_rating',
        'accessibility_features',
        
        // Industrial
        'zoning_type',
        'warehouse_size',
        'loading_bays',
        'power_capacity',
        'ceiling_height',
        
        // Land
        'land_size',
        'land_type',
        'planning_permission',
        'soil_quality',
        
        // Luxury
        'premium_features',
        'security_features',
        'view_type',
        
        // Investment
        'rental_yield',
        'occupancy_rate',
        'current_rental_income',
        'roi_percentage',
        
        // Pricing
        'price',
        'currency',
        'negotiable',
        'deposit_required',
        'service_charges',
        'maintenance_fees',
        
        // Media
        'cover_image',
        'additional_images',
        'video_tour_link',
        
        // Description
        'description',
        'overview',
        'key_features',
        'location_highlights',
        'nearby_amenities',
        'transport_links',
        'additional_notes',
        'amenities',
        
        // Seller/Agent
        'seller_name',
        'seller_company',
        'seller_phone',
        'seller_email',
        'seller_website',
        'seller_logo',
        'verified_agent',
        
        // Status
        'status',
        'featured',
        'promoted',
        'sponsored',
        'featured_until',
        'promoted_until',
        'sponsored_until',
        
        // Analytics
        'views_count',
        'inquiries_count',
        'saves_count',
        
        // Approval
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'property_size' => 'decimal:2',
        'floor_area' => 'decimal:2',
        'warehouse_size' => 'decimal:2',
        'land_size' => 'decimal:2',
        'power_capacity' => 'decimal:2',
        'ceiling_height' => 'decimal:2',
        'price' => 'decimal:2',
        'deposit_required' => 'decimal:2',
        'service_charges' => 'decimal:2',
        'maintenance_fees' => 'decimal:2',
        'current_rental_income' => 'decimal:2',
        'rental_yield' => 'decimal:2',
        'occupancy_rate' => 'decimal:2',
        'roi_percentage' => 'decimal:2',
        'furnished' => 'boolean',
        'accessibility_features' => 'boolean',
        'negotiable' => 'boolean',
        'show_exact_location' => 'boolean',
        'featured' => 'boolean',
        'promoted' => 'boolean',
        'sponsored' => 'boolean',
        'verified_agent' => 'boolean',
        'additional_images' => 'array',
        'premium_features' => 'array',
        'security_features' => 'array',
        'amenities' => 'array',
        'featured_until' => 'datetime',
        'promoted_until' => 'datetime',
        'sponsored_until' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected $dates = [
        'featured_until',
        'promoted_until',
        'sponsored_until',
        'approved_at',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function upsells(): HasMany
    {
        return $this->hasMany(PropertyUpsell::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(PropertyAnalytics::class);
    }

    public function savedBy(): HasMany
    {
        return $this->hasMany(PropertySaved::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
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
        return $query->where('status', 'active')
                    ->where('approval_status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true)
                    ->where(function ($q) {
                        $q->whereNull('featured_until')
                          ->orWhere('featured_until', '>', now());
                    });
    }

    public function scopePromoted($query)
    {
        return $query->where('promoted', true)
                    ->where(function ($q) {
                        $q->whereNull('promoted_until')
                          ->orWhere('promoted_until', '>', now());
                    });
    }

    public function scopeSponsored($query)
    {
        return $query->where('sponsored', true)
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
