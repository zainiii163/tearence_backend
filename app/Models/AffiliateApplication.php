<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AffiliateApplication extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['hop_url', 'promoter_link'];

    protected $casts = [
        'promotion_methods' => 'array',
        'audience_details' => 'array',
        'social_media_links' => 'array',
        'reviewed_at' => 'datetime',
        'business_responded_at' => 'datetime',
        'joined_at' => 'datetime',
        'earnings_total' => 'decimal:2',
    ];

    public function businessAffiliateOffer(): BelongsTo
    {
        return $this->belongsTo(BusinessAffiliateOffer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    public function hopClicks(): HasMany
    {
        return $this->hasMany(AffiliateHopClick::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeWithdrawn($query)
    {
        return $query->where('status', 'withdrawn');
    }

    /**
     * Mint a unique ClickBank-style hop code for this promoter + offer.
     */
    public function ensureTrackingCode(): string
    {
        if ($this->tracking_code) {
            return $this->tracking_code;
        }

        do {
            $code = strtolower(Str::random(10));
        } while (static::where('tracking_code', $code)->exists());

        $this->forceFill([
            'tracking_code' => $code,
            'joined_at' => $this->joined_at ?: now(),
        ])->save();

        return $code;
    }

    public function getHopUrlAttribute(): ?string
    {
        if (!$this->tracking_code) {
            return null;
        }

        $base = rtrim(config('app.url') ?: 'https://api.worldwideadverts.info', '/');

        return $base . '/go/aff/' . $this->tracking_code;
    }

    public function getPromoterLinkAttribute(): ?string
    {
        return $this->hop_url;
    }

    public function approve(?int $reviewerId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'approval_notes' => $notes,
            'joined_at' => $this->joined_at ?: now(),
        ]);
        $this->ensureTrackingCode();
    }

    public function reject(?int $reviewerId = null, ?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function withdraw(): void
    {
        $this->update([
            'status' => 'withdrawn',
        ]);
    }

    public function addBusinessResponse(string $response): void
    {
        $this->update([
            'business_response' => $response,
            'business_responded_at' => now(),
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
