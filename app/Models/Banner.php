<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banner extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'banner';

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function getImgAttribute($image)
    {
        $fileUpload = new FileUploadHelper();

        // Check if $image starts with "/uploads/images/banner"
        if (strpos($image, '/uploads/images/banner') !== false) {
            // Remove "/uploads/images/banner" from the image path
            $path = str_replace("/uploads/images/banner", "", $image);
        } else {
            // If image already has "banner/" just use it
            $path = $image;
        }

        // Return the processed file path using the FileUploadHelper
        return $fileUpload->getFile($path, 'banner');
    }

    public function setImgAttribute($value)
    {
        $this->attributes['img'] = basename($value); // Only store the file name
    }

    /**
     * Get the user that created the banner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the service associated with the banner.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Get the pricing plan for the banner.
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(AdPricingPlan::class, 'pricing_plan_id');
    }

    /**
     * Scope a query to only include active banners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope a query to only include paid banners.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Check if banner is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get the full banner URL.
     */
    public function getFullUrlAttribute(): string
    {
        return $this->url_link ?? '#';
    }

    /**
     * Get the display price with currency.
     */
    public function getDisplayPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }
}
