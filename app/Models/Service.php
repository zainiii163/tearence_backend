<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'service_type',
        'pricing_model',
        'base_price',
        'delivery_time',
        'skill_level',
        'service_category',
        'portfolio_link',
        'requirements',
        'revisions_included',
        'extra_fast_delivery',
        'is_active',
        'views_count',
        'orders_count',
        'rating',
        'reviews_count',
        'featured',
        'verified',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'extra_fast_delivery' => 'decimal:2',
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'verified' => 'boolean',
        'requirements' => 'array',
        'rating' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ServiceReview::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(ServiceGallery::class);
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByServiceCategory($query, $serviceCategory)
    {
        return $query->where('service_category', $serviceCategory);
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice = null)
    {
        $query->where('base_price', '>=', $minPrice);
        if ($maxPrice) {
            $query->where('base_price', '<=', $maxPrice);
        }
        return $query;
    }

    public function scopeByDeliveryTime($query, $deliveryTime)
    {
        return $query->where('delivery_time', $deliveryTime);
    }

    public function scopeOrderByRating($query, $direction = 'desc')
    {
        return $query->orderBy('rating', $direction);
    }

    public function scopeOrderByOrders($query, $direction = 'desc')
    {
        return $query->orderBy('orders_count', $direction);
    }

    public function scopeOrderByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('base_price', $direction);
    }

    // Helper methods
    public function getAverageRating(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getTotalEarnings(): float
    {
        return $this->orders()->where('status', 'completed')->sum('total_price');
    }

    public function getCompletionRate(): float
    {
        $totalOrders = $this->orders()->count();
        if ($totalOrders === 0) return 0;
        
        $completedOrders = $this->orders()->where('status', 'completed')->count();
        return ($completedOrders / $totalOrders) * 100;
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementOrders(): void
    {
        $this->increment('orders_count');
    }

    public function updateRating(): void
    {
        $this->rating = $this->getAverageRating();
        $this->reviews_count = $this->reviews()->count();
        $this->save();
    }

    // Search functionality
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('service_category', 'LIKE', "%{$term}%");
        });
    }

    // Get formatted price
    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->base_price, 2);
    }

    // Get delivery time text
    public function getDeliveryTimeText(): string
    {
        $deliveryTimes = [
            '1_day' => '1 Day',
            '3_days' => '3 Days',
            '1_week' => '1 Week',
            '2_weeks' => '2 Weeks',
            '1_month' => '1 Month',
            'custom' => 'Custom'
        ];

        return $deliveryTimes[$this->delivery_time] ?? 'Custom';
    }

    // Get pricing model text
    public function getPricingModelText(): string
    {
        $models = [
            'fixed_price' => 'Fixed Price',
            'hourly_rate' => 'Hourly Rate',
            'package' => 'Service Package',
            'quote_based' => 'Quote Based'
        ];

        return $models[$this->pricing_model] ?? 'Fixed Price';
    }

    // Get skill level text
    public function getSkillLevelText(): string
    {
        $levels = [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'expert' => 'Expert',
            'professional' => 'Professional'
        ];

        return $levels[$this->skill_level] ?? 'Intermediate';
    }
}
