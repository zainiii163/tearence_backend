<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $table = 'service_providers';

    protected $fillable = [
        'user_id',
        'business_name',
        'bio',
        'phone',
        'website',
        'social_links',
        'country',
        'city',
        'is_verified',
        'rating',
        'review_count',
        'skills',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'rating' => 'decimal:2',
        'review_count' => 'integer',
        'social_links' => 'array',
        'skills' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_provider_id');
    }

    public function activeServices(): HasMany
    {
        return $this->services()->where('status', 'active');
    }

    public function getActiveServicesCountAttribute(): int
    {
        return $this->activeServices()->count();
    }

    public function getFullNameAttribute(): string
    {
        return $this->business_name ?: $this->user?->name ?: '';
    }

    public function getProfilePhotoAttribute(): string
    {
        return $this->user?->profile_photo_url ?: '';
    }
}
