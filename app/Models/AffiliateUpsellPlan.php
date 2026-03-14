<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateUpsellPlan extends Model
{
    use HasFactory;

    protected $table = 'affiliate_upsell_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_type',
        'duration_days',
        'features',
        'benefits',
        'highlighted_background',
        'above_standard_posts',
        'top_category_placement',
        'larger_card_size',
        'priority_search',
        'homepage_placement',
        'category_top_placement',
        'homepage_slider',
        'social_media_promotion',
        'email_blast_inclusion',
        'badge_text',
        'badge_color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'highlighted_background' => 'boolean',
        'above_standard_posts' => 'boolean',
        'top_category_placement' => 'boolean',
        'larger_card_size' => 'boolean',
        'priority_search' => 'boolean',
        'homepage_placement' => 'boolean',
        'category_top_placement' => 'boolean',
        'homepage_slider' => 'boolean',
        'social_media_promotion' => 'boolean',
        'email_blast_inclusion' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the post upsells for this plan.
     */
    public function postUpsells(): HasMany
    {
        return $this->hasMany(AffiliatePostUpsell::class, 'affiliate_upsell_plan_id');
    }

    /**
     * Scope a query to only include active plans.
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
        return $query->orderBy('sort_order', 'asc')->orderBy('price', 'asc');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the duration description.
     */
    public function getDurationDescriptionAttribute()
    {
        $duration = $this->duration_days > 1 ? $this->duration_days : '';
        
        return "{$duration} {$this->duration_type}" . ($this->duration_days > 1 ? 's' : '');
    }

    /**
     * Get the features as array.
     */
    public function getFeaturesArrayAttribute()
    {
        return $this->features ?? [];
    }

    /**
     * Get the benefits as an array.
     */
    public function getBenefitsArrayAttribute()
    {
        $benefits = [];
        
        if ($this->highlighted_background) {
            $benefits[] = 'Highlighted background';
        }
        
        if ($this->above_standard_posts) {
            $benefits[] = 'Appears above standard posts';
        }
        
        if ($this->top_category_placement) {
            $benefits[] = 'Top of category pages';
        }
        
        if ($this->larger_card_size) {
            $benefits[] = 'Larger card size';
        }
        
        if ($this->priority_search) {
            $benefits[] = 'Priority in search results';
        }
        
        if ($this->homepage_placement) {
            $benefits[] = 'Homepage placement';
        }
        
        if ($this->category_top_placement) {
            $benefits[] = 'Category top placement';
        }
        
        if ($this->homepage_slider) {
            $benefits[] = 'Included in homepage slider';
        }
        
        if ($this->social_media_promotion) {
            $benefits[] = 'Social media promotion';
        }
        
        if ($this->email_blast_inclusion) {
            $benefits[] = 'Included in weekly email blast';
        }
        
        return $benefits;
    }

    /**
     * Get the badge name based on plan name.
     */
    public function getBadgeNameAttribute()
    {
        return match($this->slug) {
            'promoted' => 'Promoted',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            default => ucfirst($this->slug),
        };
    }
}
