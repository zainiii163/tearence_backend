<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'payload_capacity' => 'decimal:10,2',
        'length' => 'decimal:8,2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_promoted' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_top_of_category' => 'boolean',
        'show_exact_location' => 'boolean',
        'negotiable' => 'boolean',
        'trailer_included' => 'boolean',
        'airport_pickup' => 'boolean',
        'additional_images' => 'array',
        'features' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that created the vehicle.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business that owns the vehicle.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the category that owns the vehicle.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class);
    }

    /**
     * Get the make that owns the vehicle.
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class);
    }

    /**
     * Get the model that owns the vehicle.
     */
    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    /**
     * Get the pricing plan for the vehicle.
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(AdPricingPlan::class);
    }

    /**
     * Get the favourites for the vehicle.
     */
    public function favourites(): HasMany
    {
        return $this->hasMany(VehicleFavourite::class);
    }

    /**
     * Get the analytics for the vehicle.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(VehicleAnalytic::class);
    }

    /**
     * Get the enquiries for the vehicle.
     */
    public function enquiries(): HasMany
    {
        return $this->hasMany(VehicleEnquiry::class);
    }

    /**
     * Get users who favourited this vehicle.
     */
    public function favouritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vehicle_favourites')
            ->withTimestamps();
    }

    /**
     * Get main image URL.
     */
    public function getMainImageUrlAttribute(): string
    {
        if (!$this->main_image) {
            return asset('placeholder.png');
        }

        $fileUpload = new FileUploadHelper();
        return $fileUpload->getFile($this->main_image, 'vehicles');
    }

    /**
     * Get additional images URLs.
     */
    public function getAdditionalImagesUrlsAttribute(): array
    {
        if (!$this->additional_images) {
            return [];
        }

        $fileUpload = new FileUploadHelper();
        $urls = [];
        
        foreach ($this->additional_images as $image) {
            $urls[] = $fileUpload->getFile($image, 'vehicles');
        }
        
        return $urls;
    }

    /**
     * Get display price with currency.
     */
    public function getDisplayPriceAttribute(): string
    {
        if (!$this->price) {
            return 'Price on request';
        }

        $symbol = '$'; // You can make this dynamic based on user preferences
        $formattedPrice = number_format($this->price, 2);
        
        switch ($this->price_type) {
            case 'per_day':
                return "{$symbol}{$formattedPrice} / day";
            case 'per_week':
                return "{$symbol}{$formattedPrice} / week";
            case 'per_month':
                return "{$symbol}{$formattedPrice} / month";
            case 'per_hour':
                return "{$symbol}{$formattedPrice} / hour";
            default:
                return $symbol . $formattedPrice;
        }
    }

    /**
     * Get full vehicle name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->year} {$this->make->name} {$this->vehicleModel->name}";
    }

    /**
     * Get location display.
     */
    public function getLocationAttribute(): string
    {
        return "{$this->city}, {$this->country}";
    }

    /**
     * Scope a query to only include active vehicles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('status', 'approved')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope a query to only include paid vehicles.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include featured vehicles.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include sponsored vehicles.
     */
    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    /**
     * Scope a query to only include promoted vehicles.
     */
    public function scopePromoted($query)
    {
        return $query->where('is_promoted', true);
    }

    /**
     * Scope a query to only include top of category vehicles.
     */
    public function scopeTopOfCategory($query)
    {
        return $query->where('is_top_of_category', true);
    }

    /**
     * Scope a query by advert type.
     */
    public function scopeAdvertType($query, $type)
    {
        return $query->where('advert_type', $type);
    }

    /**
     * Scope a query by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query by location.
     */
    public function scopeByLocation($query, $country, $city = null)
    {
        $query->where('country', $country);
        
        if ($city) {
            $query->where('city', $city);
        }
        
        return $query;
    }

    /**
     * Scope a query by price range.
     */
    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        
        return $query;
    }

    /**
     * Check if vehicle is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active || $this->status !== 'approved') {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user has favourited this vehicle.
     */
    public function isFavouritedBy($userId): bool
    {
        return $this->favourites()->where('user_id', $userId)->exists();
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
        
        // Track in analytics
        $this->analytics()->create([
            'event_type' => 'view',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');
        
        // Track in analytics
        $this->analytics()->create([
            'event_type' => 'click',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Increment saves count.
     */
    public function incrementSaves(): void
    {
        $this->increment('saves');
    }

    /**
     * Increment enquiries count.
     */
    public function incrementEnquiries(): void
    {
        $this->increment('enquiries');
    }

    /**
     * Get upgrade badges.
     */
    public function getUpgradeBadgesAttribute(): array
    {
        $badges = [];
        
        if ($this->is_top_of_category) {
            $badges[] = ['text' => 'Top of Category', 'color' => 'purple'];
        } elseif ($this->is_sponsored) {
            $badges[] = ['text' => 'Sponsored', 'color' => 'red'];
        } elseif ($this->is_featured) {
            $badges[] = ['text' => 'Featured', 'color' => 'blue'];
        } elseif ($this->is_promoted) {
            $badges[] = ['text' => 'Promoted', 'color' => 'green'];
        }
        
        return $badges;
    }
}
