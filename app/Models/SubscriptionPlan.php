<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'price',
        'currency',
        'duration_days',
        'paid_posts',
        'promoted_posts',
        'featured_posts',
        'sponsored_posts',
        'support_included',
        'marketing_toolkit',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'paid_posts' => 'integer',
        'promoted_posts' => 'integer',
        'featured_posts' => 'integer',
        'sponsored_posts' => 'integer',
        'support_included' => 'boolean',
        'marketing_toolkit' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}
