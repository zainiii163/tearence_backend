<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Venue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'venues';

    protected $fillable = [
        'name',
        'slug',
        'venue_type',
        'country',
        'city',
        'capacity',
        'min_price',
        'max_price',
        'description',
        'amenities',
        'indoor',
        'outdoor',
        'catering_available',
        'parking_available',
        'accessibility',
        'opening_hours',
        'booking_link',
        'contact_email',
        'social_links',
        'images',
        'floor_plan',
        'video_link',
        'promotion_tier',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'amenities' => 'array',
        'opening_hours' => 'array',
        'social_links' => 'array',
        'images' => 'array',
        'indoor' => 'boolean',
        'outdoor' => 'boolean',
        'catering_available' => 'boolean',
        'parking_available' => 'boolean',
        'accessibility' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($venue) {
            $venue->slug = Str::slug($venue->name) . '-' . uniqid();
        });

        static::updating(function ($venue) {
            if ($venue->isDirty('name')) {
                $venue->slug = Str::slug($venue->name) . '-' . uniqid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('venue_type', $type);
    }

    public function scopeByLocation($query, $country, $city = null)
    {
        $query->where('country', $country);
        if ($city) {
            $query->where('city', $city);
        }
        return $query;
    }

    public function scopeByCapacity($query, $minCapacity = null, $maxCapacity = null)
    {
        if ($minCapacity) {
            $query->where('capacity', '>=', $minCapacity);
        }
        if ($maxCapacity) {
            $query->where('capacity', '<=', $maxCapacity);
        }
        return $query;
    }

    public function scopeByPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('min_price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('max_price', '<=', $maxPrice);
        }
        return $query;
    }

    public function scopeByPromotionTier($query, $tier)
    {
        return $query->where('promotion_tier', $tier);
    }

    public function getFormattedPriceRangeAttribute()
    {
        if ($this->min_price && $this->max_price) {
            return '$' . number_format($this->min_price, 2) . ' - $' . number_format($this->max_price, 2);
        } elseif ($this->min_price) {
            return 'From $' . number_format($this->min_price, 2);
        } elseif ($this->max_price) {
            return 'Up to $' . number_format($this->max_price, 2);
        } else {
            return 'Contact for pricing';
        }
    }

    public function getFormattedCapacityAttribute()
    {
        return number_format($this->capacity) . ' guests';
    }

    public function getPromotionBadgeAttribute()
    {
        return match($this->promotion_tier) {
            'promoted' => 'Promoted',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            'spotlight' => 'Spotlight',
            default => null,
        };
    }

    public function isPromoted()
    {
        return in_array($this->promotion_tier, ['promoted', 'featured', 'sponsored', 'spotlight']);
    }

    public function getVenueTypeLabelAttribute()
    {
        return match($this->venue_type) {
            'wedding_hall' => 'Wedding Hall',
            'conference_centre' => 'Conference Centre',
            'party_hall' => 'Party Hall',
            'outdoor_space' => 'Outdoor Space',
            'hotel_banquet' => 'Hotel & Banquet Room',
            'bar_restaurant' => 'Bar & Restaurant',
            'meeting_room' => 'Meeting Room',
            'exhibition_space' => 'Exhibition Space',
            'sports_venue' => 'Sports Venue',
            'other' => 'Other',
            default => 'Unknown',
        };
    }
}
