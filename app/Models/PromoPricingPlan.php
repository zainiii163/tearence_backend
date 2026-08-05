<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoPricingPlan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'tier',
        'price_usd',
        'duration_days',
        'description',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_usd' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
