<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoPricingPlan extends Model
{
    public const VERTICALS = [
        'all' => 'All verticals (default)',
        'property' => 'Property',
        'buysell' => 'Buy & Sell',
        'services' => 'Services',
        'jobs' => 'Jobs',
        'events' => 'Events & Venues',
        'vehicles' => 'Vehicles',
        'books' => 'Books',
        'funding' => 'Funding',
        'affiliates' => 'Affiliates',
        'banners' => 'Banners',
        'resorts' => 'Resorts & Travel',
        'images' => 'Images',
    ];

    protected $fillable = [
        'slug',
        'vertical',
        'name',
        'tier',
        'price_usd',
        'duration_days',
        'description',
        'features',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected $casts = [
        'price_usd' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForVertical($query, ?string $vertical)
    {
        $vertical = $vertical ?: 'all';
        return $query->where(function ($q) use ($vertical) {
            $q->where('vertical', $vertical)->orWhere('vertical', 'all');
        });
    }

    public function durationLabel(): string
    {
        $days = (int) $this->duration_days;
        if ($days % 7 === 0) {
            $weeks = $days / 7;
            return $weeks === 1 ? '1 week' : "{$weeks} weeks";
        }
        if ($days === 30) {
            return '1 month';
        }
        return "{$days} days";
    }
}
