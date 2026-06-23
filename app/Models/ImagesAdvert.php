<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ImagesAdvert extends Model
{
    use HasFactory;

    protected $table = 'images_adverts';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'short_description',
        'main_image',
        'images',
        'thumbnail',
        'width',
        'height',
        'orientation',
        'color_type',
        'dominant_color',
        'image_category',
        'tags',
        'license_type',
        'standard_price',
        'extended_price',
        'exclusive_price',
        'currency',
        'available_resolutions',
        'available_formats',
        'verification_status',
        'rejection_reason',
        'verified_at',
        'verified_by',
        'contact_name',
        'contact_email',
        'contact_phone',
        'business_name',
        'website',
        'social_links',
        'has_model_release',
        'model_release_document',
        'has_property_release',
        'property_release_document',
        'views_count',
        'downloads_count',
        'saves_count',
        'rating',
        'rating_count',
        'promotion_tier',
        'is_verified_creator',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'tags' => 'array',
        'available_resolutions' => 'array',
        'available_formats' => 'array',
        'social_links' => 'array',
        'standard_price' => 'decimal:2',
        'extended_price' => 'decimal:2',
        'exclusive_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'is_verified_creator' => 'boolean',
        'has_model_release' => 'boolean',
        'has_property_release' => 'boolean',
        'verified_at' => 'datetime',
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
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('verification_status', 'rejected');
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('image_category', $category);
    }

    public function scopeByLicenseType(Builder $query, string $licenseType): Builder
    {
        return $query->where('license_type', $licenseType);
    }

    public function scopeByOrientation(Builder $query, string $orientation): Builder
    {
        return $query->where('orientation', $orientation);
    }

    public function scopeByColorType(Builder $query, string $colorType): Builder
    {
        return $query->where('color_type', $colorType);
    }

    public function scopeByPriceRange(Builder $query, ?float $minPrice = null, ?float $maxPrice = null): Builder
    {
        if ($minPrice !== null) {
            $query->where('standard_price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('standard_price', '<=', $maxPrice);
        }
        return $query;
    }

    public function scopeByPromotionTier(Builder $query, string $tier): Builder
    {
        return $query->where('promotion_tier', $tier);
    }

    public function scopeByMinRating(Builder $query, float $minRating): Builder
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopePromoted(Builder $query): Builder
    {
        return $query->whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'network_wide']);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->whereIn('promotion_tier', ['featured', 'sponsored', 'network_wide']);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderBy('downloads_count', 'desc');
    }

    public function scopeTrending(Builder $query): Builder
    {
        return $query->orderBy('views_count', 'desc');
    }

    public function getMainImageUrlAttribute()
    {
        return $this->main_image ? asset('storage/' . $this->main_image) : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : $this->main_image_url;
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
        $price = $this->standard_price ?? 0;
        return [
            'amount' => $price,
            'currency' => $this->currency,
            'formatted' => number_format($price, 2) . ' ' . $this->currency,
            'standard' => $this->standard_price,
            'extended' => $this->extended_price,
            'exclusive' => $this->exclusive_price,
        ];
    }

    public function getLicenseLabelAttribute()
    {
        $labels = [
            'standard' => 'Standard License',
            'extended' => 'Extended License',
            'editorial' => 'Editorial License',
            'exclusive' => 'Exclusive License',
        ];

        return $labels[$this->license_type] ?? 'Standard License';
    }

    public function getVerificationStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending Review',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];

        return $labels[$this->verification_status] ?? 'Unknown';
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

    public function getIsAdminVerifiedAttribute()
    {
        return $this->verification_status === 'verified';
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementDownloads()
    {
        $this->increment('downloads_count');
    }

    public function incrementSaves()
    {
        $this->increment('saves_count');
    }

    public function verify($adminUserId)
    {
        $this->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $adminUserId,
            'rejection_reason' => null,
        ]);
    }

    public function reject($adminUserId, $reason)
    {
        $this->update([
            'verification_status' => 'rejected',
            'verified_by' => $adminUserId,
            'rejection_reason' => $reason,
        ]);
    }
}
