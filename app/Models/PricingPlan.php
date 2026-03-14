<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'features' => 'array',
        'recommended' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pricing_plans';

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get recommended plans.
     */
    public function scopeRecommended($query)
    {
        return $query->where('recommended', true);
    }

    /**
     * Scope a query to order by price.
     */
    public function scopeOrderByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . 'USD';
    }

    /**
     * Get the duration in human readable format.
     */
    public function getDurationAttribute()
    {
        return $this->duration_days . ' days';
    }
}
