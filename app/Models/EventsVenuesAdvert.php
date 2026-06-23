<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventsVenuesAdvert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sync_source_type',
        'sync_source_id',
        'category_id',
        'advert_type',
        'title',
        'slug',
        'description',
        'short_description',
        'tagline',
        'event_date',
        'event_time',
        'event_end_date',
        'event_end_time',
        'venue_name',
        'ticket_price',
        'ticket_currency',
        'free_event',
        'event_category',
        'venue_type',
        'capacity',
        'price_range',
        'amenities',
        'country',
        'city',
        'state',
        'address',
        'latitude',
        'longitude',
        'contact_name',
        'business_name',
        'email',
        'phone',
        'website',
        'social_links',
        'main_image',
        'images',
        'video_url',
        'logo',
        'key_features',
        'additional_notes',
        'indoor_outdoor',
        'family_friendly',
        'catering_available',
        'parking_available',
        'accessible',
        'promotion_tier',
        'promotion_price',
        'promotion_start',
        'promotion_expires',
        'is_verified',
        'status',
        'is_active',
        'views_count',
        'saves_count',
        'enquiries_count',
        'terms_accepted',
        'accurate_info',
        'expires_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime',
        'event_end_date' => 'date',
        'event_end_time' => 'datetime',
        'ticket_price' => 'decimal:2',
        'amenities' => 'array',
        'social_links' => 'array',
        'images' => 'array',
        'key_features' => 'array',
        'indoor_outdoor' => 'boolean',
        'family_friendly' => 'boolean',
        'catering_available' => 'boolean',
        'parking_available' => 'boolean',
        'accessible' => 'boolean',
        'promotion_price' => 'decimal:2',
        'promotion_start' => 'datetime',
        'promotion_expires' => 'datetime',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'free_event' => 'boolean',
        'terms_accepted' => 'boolean',
        'accurate_info' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    protected function mainImage(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($url = self::resolveMediaUrl($value)) {
                    return $url;
                }

                return self::resolveFirstImageFromRaw($this->attributes['images'] ?? null);
            },
        );
    }

    /**
     * @param  mixed  $rawImages
     */
    public static function resolveFirstImageFromRaw($rawImages): ?string
    {
        $items = is_array($rawImages) ? $rawImages : (json_decode($rawImages ?? '[]', true) ?: []);

        foreach ($items as $item) {
            if (is_string($item) && $item !== '') {
                return self::resolveMediaUrl($item);
            }

            if (is_array($item) && ! empty($item['url'])) {
                return self::resolveMediaUrl($item['url']);
            }
        }

        return null;
    }

    protected function images(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $items = is_array($value) ? $value : (json_decode($value ?? '[]', true) ?: []);

                return array_values(array_filter(array_map(function ($item) {
                    if (is_string($item)) {
                        return self::resolveMediaUrl($item);
                    }

                    if (is_array($item) && ! empty($item['url'])) {
                        $item['url'] = self::resolveMediaUrl($item['url']);

                        return $item;
                    }

                    return null;
                }, $items)));
            },
        );
    }

    public static function resolveMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventsVenuesCategory::class, 'category_id');
    }

    public function saves(): HasMany
    {
        return $this->hasMany(EventsVenuesSave::class);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementSaves(): void
    {
        $this->increment('saves_count');
    }

    public function incrementEnquiries(): void
    {
        $this->increment('enquiries_count');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    public function scopeEvents($query)
    {
        return $query->where('advert_type', 'event');
    }

    public function scopeVenues($query)
    {
        return $query->where('advert_type', 'venue');
    }

    public function scopeFeatured($query)
    {
        return $query->where('promotion_tier', 'featured')
                     ->where('promotion_expires', '>', now());
    }

    public function scopeSponsored($query)
    {
        return $query->where('promotion_tier', 'sponsored')
                     ->where('promotion_expires', '>', now());
    }

    public function scopePromoted($query)
    {
        return $query->where('promotion_tier', 'promoted')
                     ->where('promotion_expires', '>', now());
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%')
              ->orWhere('tagline', 'like', '%' . $search . '%')
              ->orWhere('venue_name', 'like', '%' . $search . '%')
              ->orWhere('business_name', 'like', '%' . $search . '%');
        });
    }
}
