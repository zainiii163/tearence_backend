<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AffiliatePost extends Model
{
    use HasFactory;

    protected $table = 'affiliate_posts';

    protected $fillable = [
        'post_type',
        'title',
        'tagline',
        'description',
        'business_name',
        'commission_rate',
        'cookie_duration',
        'allowed_traffic_types',
        'restrictions',
        'affiliate_link',
        'business_email',
        'website_url',
        'verification_document',
        'target_audience',
        'hashtags',
        'country_region',
        'images',
        'promotional_assets',
        'customer_id',
        'category_id',
        'upsell_tier',
        'status',
        'is_active',
        'approved_at',
        'expires_at',
    ];

    protected $casts = [
        'allowed_traffic_types' => 'array',
        'hashtags' => 'array',
        'images' => 'array',
        'promotional_assets' => 'array',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the customer that owns the affiliate post.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the category that owns the affiliate post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the active upsell for the affiliate post.
     */
    public function activeUpsell(): HasOne
    {
        return $this->hasOne(AffiliatePostUpsell::class)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    /**
     * Get all upsells for the affiliate post.
     */
    public function upsells(): HasMany
    {
        return $this->hasMany(AffiliatePostUpsell::class);
    }

    /**
     * Scope a query to only include active posts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('status', 'approved')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope a query to only include business posts.
     */
    public function scopeBusiness($query)
    {
        return $query->where('post_type', 'business');
    }

    /**
     * Scope a query to only include promoter posts.
     */
    public function scopePromoter($query)
    {
        return $query->where('post_type', 'promoter');
    }

    /**
     * Scope a query to filter by upsell tier.
     */
    public function scopeByUpsellTier($query, $tier)
    {
        return $query->where('upsell_tier', $tier);
    }

    /**
     * Scope a query to order by upsell priority.
     */
    public function scopeOrderByUpsellPriority($query)
    {
        return $query->orderByRaw("FIELD(upsell_tier, 'sponsored', 'featured', 'promoted', 'standard')")
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get the main image URL.
     */
    public function getMainImageUrlAttribute()
    {
        $images = $this->images;
        if (is_array($images) && !empty($images[0])) {
            return $images[0];
        }
        return null;
    }

    /**
     * Get the formatted commission rate.
     */
    public function getFormattedCommissionRateAttribute()
    {
        if (str_contains($this->commission_rate, '%')) {
            return $this->commission_rate;
        }
        
        return '£' . number_format($this->commission_rate, 2);
    }

    /**
     * Check if the post is currently promoted.
     */
    public function getIsCurrentlyPromotedAttribute()
    {
        return $this->upsell_tier !== 'standard' && 
               $this->activeUpsell && 
               $this->activeUpsell->is_active;
    }

    /**
     * Get the visibility multiplier based on upsell tier and active upsell.
     */
    public function getVisibilityMultiplierAttribute()
    {
        if ($this->is_currently_promoted && $this->activeUpsell && $this->activeUpsell->upsellPlan) {
            return $this->activeUpsell->upsellPlan->visibility_multiplier;
        }
        
        // Default multipliers for upsell tiers
        $multipliers = [
            'standard' => 1,
            'promoted' => 2,
            'featured' => 3,
            'sponsored' => 5,
        ];
        
        return $multipliers[$this->upsell_tier] ?? 1;
    }
}
