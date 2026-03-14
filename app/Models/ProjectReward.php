<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectReward extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'project_id',
        'title',
        'description',
        'minimum_contribution',
        'limit_quantity',
        'estimated_delivery',
        'includes_shipping',
        'shipping_cost',
        'order_index',
    ];

    protected $casts = [
        'minimum_contribution' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'estimated_delivery' => 'date',
        'includes_shipping' => 'boolean',
    ];

    protected $attributes = [
        'includes_shipping' => false,
        'shipping_cost' => 0,
        'order_index' => 0,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Helper methods
    public function getTotalCost()
    {
        return $this->minimum_contribution + ($this->includes_shipping ? $this->shipping_cost : 0);
    }

    public function isAvailable()
    {
        return !$this->limit_quantity || $this->getClaimedCount() < $this->limit_quantity;
    }

    public function getRemainingQuantity()
    {
        if (!$this->limit_quantity) {
            return null; // Unlimited
        }
        return max(0, $this->limit_quantity - $this->getClaimedCount());
    }

    public function getClaimedCount()
    {
        // This would need to be implemented based on your backing/reward system
        // For now, return 0 as placeholder
        return 0;
    }

    public function isSoldOut()
    {
        return $this->limit_quantity && $this->getClaimedCount() >= $this->limit_quantity;
    }

    public function getDeliveryStatus()
    {
        if (!$this->estimated_delivery) {
            return 'not_set';
        }

        if ($this->estimated_delivery->isPast()) {
            return 'overdue';
        }

        if ($this->estimated_delivery->diffInDays(now()) <= 30) {
            return 'upcoming';
        }

        return 'on_schedule';
    }
}
