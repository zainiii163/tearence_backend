<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VenueService extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'venue_services';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'country',
        'city',
        'min_price',
        'max_price',
        'description',
        'packages_offered',
        'availability',
        'website',
        'contact_email',
        'social_links',
        'images',
        'video_link',
        'promotion_tier',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'packages_offered' => 'array',
        'availability' => 'array',
        'social_links' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            $service->slug = Str::slug($service->name) . '-' . uniqid();
        });

        static::updating(function ($service) {
            if ($service->isDirty('name')) {
                $service->slug = Str::slug($service->name) . '-' . uniqid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'ea_event_venue_service')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocation($query, $country, $city = null)
    {
        $query->where('country', $country);
        if ($city) {
            $query->where('city', $city);
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

    public function getCategoryLabelAttribute()
    {
        return match($this->category) {
            'catering' => 'Catering',
            'decor' => 'Decor',
            'dj' => 'DJ Services',
            'photography' => 'Photography',
            'videography' => 'Videography',
            'security' => 'Security',
            'event_planning' => 'Event Planning',
            'lighting' => 'Lighting',
            'sound' => 'Sound System',
            'transportation' => 'Transportation',
            'other' => 'Other',
            default => 'Unknown',
        };
    }
}
