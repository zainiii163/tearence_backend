<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateUpsell extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'candidate_upsell_id';
    protected $table = 'candidate_upsells';

    protected $fillable = [
        'candidate_profile_id',
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
     * Get the candidate profile that owns the upsell.
     */
    public function candidateProfile()
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_profile_id', 'candidate_profile_id');
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

