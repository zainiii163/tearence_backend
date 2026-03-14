<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BuySellItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'item_type',
        'condition',
        'brand',
        'model',
        'color',
        'dimensions',
        'weight',
        'description',
        'key_features',
        'usage_notes',
        'price',
        'currency',
        'is_negotiable',
        'country',
        'city',
        'latitude',
        'longitude',
        'location_details',
        'views',
        'contacts',
        'saves',
        'shares',
        'rating',
        'review_count',
        'status',
        'promotion_type',
        'promotion_expires_at',
        'is_verified',
        'expires_at',
        'meta_data',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'key_features' => 'array',
        'usage_notes' => 'array',
        'price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_negotiable' => 'boolean',
        'is_verified' => 'boolean',
        'promotion_expires_at' => 'datetime',
        'expires_at' => 'datetime',
        'meta_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BuySellCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(BuySellImage::class, 'item_id')->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(BuySellImage::class, 'item_id')->where('is_primary', true);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(BuySellVideo::class, 'item_id');
    }

    public function seller(): HasMany
    {
        return $this->hasMany(BuySellSeller::class, 'item_id');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(BuySellEnquiry::class, 'item_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(BuySellFavorite::class, 'item_id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'buy_sell_favorites', 'item_id', 'user_id')
            ->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BuySellReview::class, 'item_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(BuySellAnalytic::class, 'item_id');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(BuySellPromotion::class, 'item_id');
    }

    public function activePromotion(): HasMany
    {
        return $this->hasMany(BuySellPromotion::class, 'item_id')
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePromoted($query)
    {
        return $query->where('promotion_type', '!=', 'standard')
            ->where(function ($q) {
                $q->whereNull('promotion_expires_at')
                  ->orWhere('promotion_expires_at', '>', now());
            });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    public function scopePriceRange($query, $min, $max = null)
    {
        $query->where('price', '>=', $min);
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('brand', 'LIKE', "%{$term}%")
              ->orWhere('model', 'LIKE', "%{$term}%");
        });
    }

    public function incrementViews()
    {
        $this->increment('views');
        $this->analytics()->create([
            'date' => now()->toDateString(),
            'views' => 1,
        ]);
    }

    public function incrementContacts()
    {
        $this->increment('contacts');
        $this->analytics()->create([
            'date' => now()->toDateString(),
            'contacts' => 1,
        ]);
    }

    public function incrementSaves()
    {
        $this->increment('saves');
        $this->analytics()->create([
            'date' => now()->toDateString(),
            'saves' => 1,
        ]);
    }

    public function incrementShares()
    {
        $this->increment('shares');
        $this->analytics()->create([
            'date' => now()->toDateString(),
            'shares' => 1,
        ]);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->slug)) {
                $item->slug = Str::slug($item->title) . '-' . time();
            }
        });

        static::updating(function ($item) {
            if ($item->isDirty('title') && empty($item->slug)) {
                $item->slug = Str::slug($item->title) . '-' . time();
            }
        });
    }
}
