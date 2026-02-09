<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'buyer_id',
        'seller_id',
        'package_id',
        'requirements',
        'total_price',
        'delivery_time',
        'status',
        'buyer_notes',
        'seller_notes',
        'completed_at',
        'cancelled_at',
        'refund_amount',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'requirements' => 'array',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $dates = [
        'completed_at',
        'cancelled_at',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function review()
    {
        return $this->hasOne(ServiceReview::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    public function scopeBySeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    // Status methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function canBeReviewed(): bool
    {
        return $this->isCompleted() && !$this->review;
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function getStatusText(): string
    {
        $statuses = [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }
}
