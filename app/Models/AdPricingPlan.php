<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdPricingPlan extends Model
{
    use HasFactory;

    protected $table = 'ad_pricing_plans';

    protected $fillable = [
        'name',
        'ad_type',
        'price',
        'duration_days',
        'description',
        'features',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Scope to get only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get plans by ad type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('ad_type', $type);
    }

    /**
     * Scope to get featured plans
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationTextAttribute()
    {
        if ($this->duration_days == 30) {
            return '1 Month';
        } elseif ($this->duration_days == 90) {
            return '3 Months';
        } elseif ($this->duration_days == 365) {
            return '1 Year';
        } else {
            return $this->duration_days . ' Days';
        }
    }
}
