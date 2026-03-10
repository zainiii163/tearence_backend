<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BannerCategory extends Model
{
    use HasFactory;

    protected $table = 'banner_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'color',
        'is_active',
        'sort_order',
        'active_banners_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'active_banners_count' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the banner ads for the category.
     */
    public function bannerAds(): HasMany
    {
        return $this->hasMany(BannerAd::class, 'banner_category_id');
    }

    /**
     * Get the active banner ads for the category.
     */
    public function activeBannerAds(): HasMany
    {
        return $this->bannerAds()->active();
    }

    /**
     * Get the promoted banner ads for the category.
     */
    public function promotedBannerAds(): HasMany
    {
        return $this->bannerAds()->promoted();
    }

    /**
     * Get the featured banner ads for the category.
     */
    public function featuredBannerAds(): HasMany
    {
        return $this->bannerAds()->featured();
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Scope a query to get categories with most banner ads.
     */
    public function scopeMostPopular($query, $limit = 10)
    {
        return $query->orderBy('active_banners_count', 'desc')->limit($limit);
    }

    /**
     * Update the active banners count.
     */
    public function updateActiveBannersCount(): void
    {
        $this->update([
            'active_banners_count' => $this->activeBannerAds()->count()
        ]);
    }

    /**
     * Get the full image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('images/default-category.jpg');
        }
        
        return asset('storage/category-images/' . $this->image);
    }

    /**
     * Get the full icon URL.
     */
    public function getIconUrlAttribute(): string
    {
        if (!$this->icon) {
            return asset('images/default-icon.png');
        }
        
        return asset('storage/category-icons/' . $this->icon);
    }
}
