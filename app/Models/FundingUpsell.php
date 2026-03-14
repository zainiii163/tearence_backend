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
        'type',
        'price',
        'currency',
        'status',
        'transaction_id',
        'purchased_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'paid' && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
