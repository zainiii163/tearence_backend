<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'affiliate_type',
        'program_name',
        'commission_rate',
        'affiliate_network',
        'product_category',
        'promotion_method',
        'affiliate_link',
        'is_active',
        'clicks_count',
        'conversions_count',
        'total_earnings',
        'approved',
        'featured',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'is_active' => 'boolean',
        'approved' => 'boolean',
        'featured' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('affiliate_type', $type);
    }

    public function scopeByNetwork($query, $network)
    {
        return $query->where('affiliate_network', $network);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    public function incrementConversions(): void
    {
        $this->increment('conversions_count');
    }

    public function addEarnings(float $amount): void
    {
        $this->increment('total_earnings', $amount);
    }

    public function getConversionRate(): float
    {
        if ($this->clicks_count === 0) return 0;
        return ($this->conversions_count / $this->clicks_count) * 100;
    }

    public function getAffiliateTypeText(): string
    {
        $types = [
            'user_link' => 'My Affiliate Link',
            'program_join' => 'Join Our Program',
            'product_promotion' => 'Product Promotion',
        ];

        return $types[$this->affiliate_type] ?? 'Unknown';
    }

    public function getNetworkText(): string
    {
        $networks = [
            'amazon' => 'Amazon',
            'clickbank' => 'ClickBank',
            'shareasale' => 'ShareASale',
            'commission_junction' => 'Commission Junction',
            'rakuten' => 'Rakuten',
            'independent' => 'Independent Program',
            'our_program' => 'Our Referral Program',
        ];

        return $networks[$this->affiliate_network] ?? 'Unknown';
    }

    public function getPromotionMethodText(): string
    {
        $methods = [
            'link_sharing' => 'Link Sharing',
            'review' => 'Product Review',
            'tutorial' => 'Tutorial/Guide',
            'social_media' => 'Social Media',
            'email_marketing' => 'Email Marketing',
        ];

        return $methods[$this->promotion_method] ?? 'Link Sharing';
    }

    public function getFormattedEarnings(): string
    {
        return '$' . number_format($this->total_earnings, 2);
    }

    public function getFormattedCommissionRate(): string
    {
        return $this->commission_rate . '%';
    }
}
