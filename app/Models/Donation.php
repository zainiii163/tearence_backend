<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'story',
        'category',
        'organizer_name',
        'organizer_email',
        'organizer_phone',
        'goal_amount',
        'current_amount',
        'currency',
        'deadline',
        'country',
        'city',
        'cover_image',
        'images',
        'video_url',
        'beneficiaries',
        'use_of_funds',
        'milestones',
        'is_verified',
        'is_active',
        'is_featured',
        'is_urgent',
        'donor_count',
        'views_count',
        'shares_count',
        'status',
        'published_at',
    ];

    protected $casts = [
        'goal_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline' => 'datetime',
        'images' => 'array',
        'beneficiaries' => 'array',
        'milestones' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'customer_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->goal_amount == 0) return 0;
        return min(100, round(($this->current_amount / $this->goal_amount) * 100, 2));
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->deadline) return null;
        return max(0, now()->diffInDays($this->deadline, false));
    }

    public function getFormattedGoalAmountAttribute()
    {
        return number_format($this->goal_amount, 2);
    }

    public function getFormattedCurrentAmountAttribute()
    {
        return number_format($this->current_amount, 2);
    }
}
