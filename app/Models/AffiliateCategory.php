<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the business affiliate offers for this category.
     */
    public function businessAffiliateOffers(): HasMany
    {
        return $this->hasMany(BusinessAffiliateOffer::class);
    }

    /**
     * Get the user affiliate posts for this category.
     */
    public function userAffiliatePosts(): HasMany
    {
        return $this->hasMany(UserAffiliatePost::class);
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
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Get the total count of all posts in this category.
     */
    public function getTotalPostsAttribute(): int
    {
        return $this->businessAffiliateOffers()->count() + $this->userAffiliatePosts()->count();
    }

    /**
     * Get the active posts count.
     */
    public function getActivePostsAttribute(): int
    {
        return $this->businessAffiliateOffers()->active()->count() + 
               $this->userAffiliatePosts()->active()->count();
    }
}
