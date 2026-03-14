<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BuySellAdvert extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    
    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'subcategory_id',
        'condition',
        'price',
        'negotiable',
        'currency',
        'country',
        'city',
        'state_province',
        'postal_code',
        'address',
        'latitude',
        'longitude',
        'brand',
        'model',
        'color',
        'dimensions',
        'weight',
        'material',
        'usage_duration',
        'reason_for_selling',
        'seller_name',
        'seller_email',
        'seller_phone',
        'seller_website',
        'logo_url',
        'verified_seller',
        'show_phone',
        'preferred_contact',
        'images',
        'video_url',
        'promotion_plan',
        'promotion_start_date',
        'promotion_end_date',
        'promotion_status',
        'status',
        'featured',
        'is_promoted',
        'is_sponsored',
        'is_urgent',
        'is_new',
        'is_hot',
        'views_count',
        'saves_count',
        'contacts_count',
        'shares_count',
        'last_viewed_at',
        'user_id',
        'ip_address',
        'user_agent',
        'expires_at',
        'deleted_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'negotiable' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'weight' => 'decimal:2',
        'verified_seller' => 'boolean',
        'show_phone' => 'boolean',
        'images' => 'array',
        'promotion_start_date' => 'datetime',
        'promotion_end_date' => 'datetime',
        'featured' => 'boolean',
        'is_promoted' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_urgent' => 'boolean',
        'is_new' => 'boolean',
        'is_hot' => 'boolean',
        'views_count' => 'integer',
        'saves_count' => 'integer',
        'contacts_count' => 'integer',
        'shares_count' => 'integer',
        'last_viewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BuySellCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(BuySellCategory::class, 'subcategory_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function savedAdverts(): HasMany
    {
        return $this->hasMany(BuySellSavedAdvert::class, 'advert_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(BuySellAdvertView::class, 'advert_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(BuySellAdvertReport::class, 'advert_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePromoted($query)
    {
        return $query->where('is_promoted', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByLocation($query, $country, $city = null)
    {
        $query->where('country', $country);
        if ($city) {
            $query->where('city', $city);
        }
        return $query;
    }

    public function scopeByPriceRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('price', '>=', $min);
        }
        if ($max) {
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

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    public function getFullLocationAttribute()
    {
        $parts = array_filter([$this->city, $this->state_province, $this->country]);
        return implode(', ', $parts);
    }

    public function getMainImageAttribute()
    {
        return $this->images[0] ?? null;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getPromotionIsActiveAttribute()
    {
        return $this->is_promoted && 
               $this->promotion_start_date && 
               $this->promotion_start_date->isPast() &&
               $this->promotion_end_date &&
               $this->promotion_end_date->isFuture();
    }

    // Methods
    public function incrementView($userId = null, $ipAddress = null, $userAgent = null, $referrer = null)
    {
        $this->increment('views_count');
        $this->update(['last_viewed_at' => now()]);

        $this->views()->create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referrer' => $referrer,
        ]);
    }

    public function toggleSave($userId)
    {
        $saved = $this->savedAdverts()->where('user_id', $userId)->first();

        if ($saved) {
            $saved->delete();
            $this->decrement('saves_count');
            return false;
        } else {
            $this->savedAdverts()->create(['user_id' => $userId]);
            $this->increment('saves_count');
            return true;
        }
    }

    public function isSavedBy($userId)
    {
        return $this->savedAdverts()->where('user_id', $userId)->exists();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($advert) {
            if (empty($advert->status)) {
                $advert->status = 'active';
            }
            
            // Set expiration date (90 days from creation)
            if (empty($advert->expires_at)) {
                $advert->expires_at = now()->addDays(90);
            }
        });

        static::updated(function ($advert) {
            // Update category advert count
            if ($advert->isDirty('category_id') || $advert->isDirty('status')) {
                $advert->category?->updateAdvertCount();
                if ($advert->isDirty('category_id')) {
                    $advert->subcategory?->updateAdvertCount();
                }
            }
        });

        static::deleted(function ($advert) {
            $advert->category?->updateAdvertCount();
            $advert->subcategory?->updateAdvertCount();
        });
    }
}
