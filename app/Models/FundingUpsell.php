<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingUpsell extends Model
{
    use HasFactory;

    protected $fillable = [
        'funding_project_id',
        'customer_id',
        'upsell_type',
        'price',
        'currency',
        'status',
        'duration_days',
        'starts_at',
        'expires_at',
        'payment_reference',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function getUpsellTypes(): array
    {
        return [
            'promoted' => 'Promoted Project',
            'featured' => 'Featured Project',
            'sponsored' => 'Sponsored Project',
        ];
    }

    public static function getPricing(): array
    {
        return [
            'promoted' => 29.99,
            'featured' => 79.99,
            'sponsored' => 199.99,
        ];
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && 
               $this->starts_at?->isPast() && 
               $this->expires_at?->isFuture();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('upsell_type', $type);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($upsell) {
            // Set default pricing based on type
            if (!$upsell->price) {
                $upsell->price = self::getPricing()[$upsell->upsell_type] ?? 0;
            }
            
            // Set default duration
            if (!$upsell->duration_days) {
                $upsell->duration_days = 30;
            }
        });

        static::updated(function ($upsell) {
            // Update project flags based on active upsells
            if ($upsell->wasChanged('status')) {
                $project = $upsell->fundingProject;
                
                // Reset all flags first
                $project->update([
                    'is_promoted' => false,
                    'is_featured' => false,
                    'is_sponsored' => false,
                ]);
                
                // Set flags based on active upsells
                $activeUpsells = $project->upsells()->active()->get();
                foreach ($activeUpsells as $activeUpsell) {
                    $project->update([$activeUpsell->upsell_type => true]);
                }
            }
        });
    }
}
