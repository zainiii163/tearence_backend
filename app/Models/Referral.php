<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Referral extends Model
{
    use HasFactory;

    protected $guarded = ['referral_id'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'referral_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'referrer_id',
        'referral_code',
        'referral_link',
        'message',
        'is_active',
        'max_uses',
        'current_uses',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot method to generate referral code and link
     */
    protected static function booted()
    {
        static::creating(function ($referral) {
            if (empty($referral->referral_code)) {
                $referral->referral_code = $referral->generateUniqueReferralCode();
            }
            if (empty($referral->referral_link)) {
                $referral->referral_link = $referral->generateReferralLink();
            }
        });
    }

    /**
     * Get the user who created this referral
     */
    public function referrer()
    {
        return $this->belongsTo(Customer::class, 'referrer_id', 'customer_id');
    }

    /**
     * Get all user referrals from this referral
     */
    public function userReferrals()
    {
        return $this->hasMany(UserReferral::class, 'referral_id', 'referral_id');
    }

    /**
     * Get completed referrals
     */
    public function completedReferrals()
    {
        return $this->userReferrals()->where('status', 'completed');
    }

    /**
     * Get pending referrals
     */
    public function pendingReferrals()
    {
        return $this->userReferrals()->where('status', 'pending');
    }

    /**
     * Generate a unique referral code
     */
    public function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());
        
        return $code;
    }

    /**
     * Generate referral link
     */
    public function generateReferralLink(): string
    {
        return url('/register?ref=' . $this->referral_code);
    }

    /**
     * Check if referral is still valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->increment('current_uses');
        return true;
    }

    /**
     * Get remaining uses
     */
    public function getRemainingUsesAttribute(): int
    {
        return max(0, $this->max_uses - $this->current_uses);
    }

    /**
     * Scope to get only active referrals
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get referrals that haven't expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get referrals that still have uses remaining
     */
    public function scopeHasUsesRemaining($query)
    {
        return $query->whereRaw('current_uses < max_uses');
    }

    /**
     * Scope to get valid referrals (active, not expired, has uses)
     */
    public function scopeValid($query)
    {
        return $query->active()->notExpired()->hasUsesRemaining();
    }

    /**
     * Find referral by code
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('referral_code', strtoupper($code))->first();
    }

    /**
     * Get referral statistics
     */
    public function getStats(): array
    {
        return [
            'total_uses' => $this->current_uses,
            'remaining_uses' => $this->remaining_uses,
            'completed_referrals' => $this->completedReferrals()->count(),
            'pending_referrals' => $this->pendingReferrals()->count(),
            'conversion_rate' => $this->current_uses > 0 
                ? ($this->completedReferrals()->count() / $this->current_uses) * 100 
                : 0,
        ];
    }
}
