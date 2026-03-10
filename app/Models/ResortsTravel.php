<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ResortsTravel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'resorts_travel_adverts';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'tagline',
        'advert_type',
        'accommodation_type',
        'transport_type',
        'experience_type',
        'country',
        'city',
        'address',
        'latitude',
        'longitude',
        'price_per_night',
        'price_per_trip',
        'price_per_service',
        'currency',
        'availability_start',
        'availability_end',
        'room_types',
        'amenities',
        'distance_to_city_centre',
        'check_in_time',
        'check_out_time',
        'guest_capacity',
        'vehicle_type',
        'passenger_capacity',
        'luggage_capacity',
        'service_area',
        'operating_hours',
        'airport_pickup',
        'duration',
        'group_size',
        'whats_included',
        'what_to_bring',
        'description',
        'overview',
        'key_features',
        'why_travellers_love_this',
        'nearby_attractions',
        'additional_notes',
        'contact_name',
        'business_name',
        'phone_number',
        'email',
        'website',
        'social_links',
        'logo',
        'verified_business',
        'images',
        'video_link',
        'main_image',
        'promotion_tier',
        'is_active',
        'is_approximate_location',
    ];

    protected $casts = [
        'room_types' => 'array',
        'amenities' => 'array',
        'operating_hours' => 'array',
        'social_links' => 'array',
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'price_per_night' => 'decimal:2',
        'price_per_trip' => 'decimal:2',
        'price_per_service' => 'decimal:2',
        'availability_start' => 'date',
        'availability_end' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'is_active' => 'boolean',
        'is_approximate_location' => 'boolean',
        'verified_business' => 'boolean',
        'airport_pickup' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($advert) {
            $advert->slug = static::generateUniqueSlug($advert->title);
        });

        static::updating(function ($advert) {
            if ($advert->isDirty('title')) {
                $advert->slug = static::generateUniqueSlug($advert->title, $advert->id);
            }
        });
    }

    protected static function generateUniqueSlug($title, $id = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($id, function ($query, $id) {
                return $query->where('id', '!=', $id);
            })
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ResortsTravelCategory::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('advert_type', $type);
    }

    public function scopeByAccommodationType(Builder $query, string $type): Builder
    {
        return $query->where('accommodation_type', $type);
    }

    public function scopeByTransportType(Builder $query, string $type): Builder
    {
        return $query->where('transport_type', $type);
    }

    public function scopeByExperienceType(Builder $query, string $type): Builder
    {
        return $query->where('experience_type', $type);
    }

    public function scopeByLocation(Builder $query, ?string $country = null, ?string $city = null): Builder
    {
        if ($country) {
            $query->where('country', $country);
        }
        if ($city) {
            $query->where('city', $city);
        }
        return $query;
    }

    public function scopeByPriceRange(Builder $query, ?float $minPrice = null, ?float $maxPrice = null): Builder
    {
        if ($minPrice !== null) {
            $query->where(function ($q) use ($minPrice) {
                $q->where('price_per_night', '>=', $minPrice)
                  ->orWhere('price_per_trip', '>=', $minPrice)
                  ->orWhere('price_per_service', '>=', $minPrice);
            });
        }
        if ($maxPrice !== null) {
            $query->where(function ($q) use ($maxPrice) {
                $q->where('price_per_night', '<=', $maxPrice)
                  ->orWhere('price_per_trip', '<=', $maxPrice)
                  ->orWhere('price_per_service', '<=', $maxPrice);
            });
        }
        return $query;
    }

    public function scopeByPromotionTier(Builder $query, string $tier): Builder
    {
        return $query->where('promotion_tier', $tier);
    }

    public function scopeByAmenity(Builder $query, string $amenity): Builder
    {
        return $query->whereJsonContains('amenities', $amenity);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verified_business', true);
    }

    public function scopePromoted(Builder $query): Builder
    {
        return $query->whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'network_wide']);
    }

    public function getMainImageUrlAttribute()
    {
        return $this->main_image ? asset('storage/' . $this->main_image) : null;
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getImageUrlsAttribute()
    {
        if (!$this->images) {
            return [];
        }

        return collect($this->images)->map(function ($image) {
            return asset('storage/' . $image);
        })->toArray();
    }

    public function getDisplayPriceAttribute()
    {
        if ($this->price_per_night) {
            return [
                'amount' => $this->price_per_night,
                'type' => 'per_night',
                'formatted' => number_format($this->price_per_night, 2) . ' ' . $this->currency . ' / night'
            ];
        } elseif ($this->price_per_trip) {
            return [
                'amount' => $this->price_per_trip,
                'type' => 'per_trip',
                'formatted' => number_format($this->price_per_trip, 2) . ' ' . $this->currency . ' / trip'
            ];
        } elseif ($this->price_per_service) {
            return [
                'amount' => $this->price_per_service,
                'type' => 'per_service',
                'formatted' => number_format($this->price_per_service, 2) . ' ' . $this->currency . ' / service'
            ];
        }

        return null;
    }

    public function getPromotionBadgeAttribute()
    {
        $badges = [
            'promoted' => 'Promoted',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            'network_wide' => 'Network-Wide Boost'
        ];

        return $badges[$this->promotion_tier] ?? null;
    }

    public function getPromotionPriorityAttribute()
    {
        $priorities = [
            'standard' => 0,
            'promoted' => 1,
            'featured' => 2,
            'sponsored' => 3,
            'network_wide' => 4
        ];

        return $priorities[$this->promotion_tier] ?? 0;
    }
}
