<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobUpsell extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'job_upsell_id';
    protected $table = 'job_upsells';

    protected $fillable = [
        'listing_id',
        'upsell_type',
        'price',
        'duration_days',
        'starts_at',
        'expires_at',
        'status',
        'payment_transaction_id',
        'payment_status',
        'payment_details',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'payment_details' => 'array',
    ];

    /**
     * Get the listing that owns the upsell.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }

    /**
     * Check if upsell is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Activate the upsell
     */
    public function activate(): void
    {
        $this->status = 'active';
        $this->starts_at = now();
        
        if ($this->expires_at === null && $this->duration_days > 0) {
            $this->expires_at = now()->addDays($this->duration_days);
        }
        
        $this->save();
    }
}

