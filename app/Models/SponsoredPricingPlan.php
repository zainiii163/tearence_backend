<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsoredPricingPlan extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sponsored_pricing_plans';

    protected $primaryKey = 'plan_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'price',
        'currency',
        'duration_days',
        'features',
        'active',
        'recommended',
        'visibility_multiplier',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'active' => 'boolean',
        'recommended' => 'boolean',
        'visibility_multiplier' => 'integer',
    ];

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include recommended plans.
     */
    public function scopeRecommended($query)
    {
        return $query->where('recommended', true);
    }

    /**
     * Scope a query to only include featured plans.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by tier.
     */
    public function scopeByTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency;
        return $symbol . number_format($this->price, 2);
    }

    /**
     * Get the tier display name.
     */
    public function getTierDisplayNameAttribute()
    {
        $tiers = [
            'basic' => 'Sponsored',
            'plus' => 'Sponsored Plus',
            'premium' => 'Sponsored Premium',
        ];

        return $tiers[$this->tier] ?? ucfirst($this->tier);
    }

    /**
     * Get duration display.
     */
    public function getDurationDisplayAttribute()
    {
        return $this->duration_days . ' days';
    }

    /**
     * Get default features for each tier.
     */
    public static function getDefaultFeatures()
    {
        return [
            'basic' => [
                'Listed on Sponsored Adverts Page',
                'Highlighted card',
                '"Sponsored" badge',
                '3× more visibility than standard ads',
            ],
            'plus' => [
                'All Basic features',
                'Top of category placement',
                'Larger advert card',
                'Priority in search results',
                'Included in weekly "Sponsored Highlights" email',
            ],
            'premium' => [
                'Homepage placement',
                'Featured in homepage slider',
                'Category top placement',
                'Included in social media promotion',
                '"Premium Sponsored" badge',
                'Maximum visibility across the platform',
            ],
        ];
    }

    /**
     * Get default visibility settings for each tier.
     */
    public static function getDefaultVisibilitySettings()
    {
        return [
            'basic' => [
                'sponsored_page' => true,
                'category_page' => false,
                'homepage' => false,
                'search_priority' => 1,
            ],
            'plus' => [
                'sponsored_page' => true,
                'category_page' => true,
                'homepage' => false,
                'search_priority' => 2,
            ],
            'premium' => [
                'sponsored_page' => true,
                'category_page' => true,
                'homepage' => true,
                'search_priority' => 3,
            ],
        ];
    }

    /**
     * Get default badge settings for each tier.
     */
    public static function getDefaultBadgeSettings()
    {
        return [
            'basic' => [
                'text' => 'Sponsored',
                'color' => '#F59E0B', // Yellow-500
                'style' => 'solid',
            ],
            'plus' => [
                'text' => 'Sponsored Plus',
                'color' => '#3B82F6', // Blue-500
                'style' => 'solid',
            ],
            'premium' => [
                'text' => 'Premium Sponsored',
                'color' => '#8B5CF6', // Purple-500
                'style' => 'solid',
            ],
        ];
    }

    /**
     * Get default placement settings for each tier.
     */
    public static function getDefaultPlacementSettings()
    {
        return [
            'basic' => [
                'card_size' => 'standard',
                'carousel_inclusion' => false,
                'featured_section' => false,
            ],
            'plus' => [
                'card_size' => 'large',
                'carousel_inclusion' => true,
                'featured_section' => true,
            ],
            'premium' => [
                'card_size' => 'premium',
                'carousel_inclusion' => true,
                'featured_section' => true,
                'homepage_slider' => true,
            ],
        ];
    }

    /**
     * Get default promotion settings for each tier.
     */
    public static function getDefaultPromotionSettings()
    {
        return [
            'basic' => [
                'email_inclusion' => false,
                'social_media_promotion' => false,
                'newsletter_feature' => false,
            ],
            'plus' => [
                'email_inclusion' => true,
                'social_media_promotion' => false,
                'newsletter_feature' => true,
            ],
            'premium' => [
                'email_inclusion' => true,
                'social_media_promotion' => true,
                'newsletter_feature' => true,
            ],
        ];
    }
}
