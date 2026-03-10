<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'user_id',
        'service_provider_id',
        'category_id',
        'title',
        'slug',
        'tagline',
        'description',
        'whats_included',
        'whats_not_included',
        'requirements',
        'service_type',
        'starting_price',
        'currency',
        'delivery_time',
        'availability',
        'country',
        'city',
        'latitude',
        'longitude',
        'service_area_radius',
        'views',
        'enquiries',
        'rating',
        'review_count',
        'status',
        'promotion_type',
        'promotion_expires_at',
        'is_verified',
        'languages',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'service_area_radius' => 'integer',
        'views' => 'integer',
        'enquiries' => 'integer',
        'rating' => 'decimal:2',
        'review_count' => 'integer',
        'promotion_expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'whats_included' => 'array',
        'whats_not_included' => 'array',
        'availability' => 'array',
        'languages' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class)->active()->ordered();
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(ServicePromotion::class);
    }

    public function activePromotion(): HasMany
    {
        return $this->promotions()->active();
    }

    public function addons(): HasMany
    {
        return $this->hasMany(ServiceAddon::class)->active()->ordered();
    }

    public function media(): HasMany
    {
        return $this->hasMany(ServiceMedia::class)->ordered();
    }

    public function thumbnail(): HasMany
    {
        return $this->media()->thumbnail()->first();
    }

    public function images(): HasMany
    {
        return $this->media()->images();
    }

    public function videos(): HasMany
    {
        return $this->media()->videos();
    }

    public function documents(): HasMany
    {
        return $this->media()->documents();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePromoted(Builder $query): Builder
    {
        return $query->whereIn('promotion_type', ['promoted', 'featured', 'sponsored', 'network_boost'])
                    ->where(function ($q) {
                        $q->whereNull('promotion_expires_at')
                          ->orWhere('promotion_expires_at', '>', now());
                    });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->whereIn('promotion_type', ['featured', 'sponsored', 'network_boost'])
                    ->where(function ($q) {
                        $q->whereNull('promotion_expires_at')
                          ->orWhere('promotion_expires_at', '>', now());
                    });
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByCountry(Builder $query, $country): Builder
    {
        return $query->where('country', $country);
    }

    public function scopeByType(Builder $query, $type): Builder
    {
        return $query->where('service_type', $type);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->starting_price, 2);
    }

    public function getProviderNameAttribute(): string
    {
        return $this->serviceProvider?->getFullNameAttribute() ?: $this->user?->name ?: '';
    }

    public function getProviderPhotoAttribute(): string
    {
        return $this->serviceProvider?->getProfilePhotoAttribute() ?: $this->user?->profile_photo_url ?: '';
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail?->getFullUrlAttribute() ?: '';
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function incrementEnquiries(): void
    {
        $this->increment('enquiries');
    }

    public function isPromoted(): bool
    {
        return in_array($this->promotion_type, ['promoted', 'featured', 'sponsored', 'network_boost']) &&
               (!$this->promotion_expires_at || $this->promotion_expires_at > now());
    }

    public function getPromotionBadgeAttribute(): string
    {
        return match($this->promotion_type) {
            'promoted' => 'Promoted',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            'network_boost' => 'Top Spotlight',
            default => '',
        };
    }

    // Search functionality
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('tagline', 'LIKE', "%{$term}%");
        });
    }
}
