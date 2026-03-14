<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateApplication extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'promotion_methods' => 'array',
        'audience_details' => 'array',
        'social_media_links' => 'array',
        'reviewed_at' => 'datetime',
        'business_responded_at' => 'datetime',
    ];

    /**
     * Get the business affiliate offer for this application.
     */
    public function businessAffiliateOffer(): BelongsTo
    {
        return $this->belongsTo(BusinessAffiliateOffer::class);
    }

    /**
     * Get the user who made the application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the application.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope a query to only include pending applications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved applications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include withdrawn applications.
     */
    public function scopeWithdrawn($query)
    {
        return $query->where('status', 'withdrawn');
    }

    /**
     * Approve the application.
     */
    public function approve(?int $reviewerId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Reject the application.
     */
    public function reject(?int $reviewerId = null, ?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Withdraw the application.
     */
    public function withdraw(): void
    {
        $this->update([
            'status' => 'withdrawn',
        ]);
    }

    /**
     * Add business response.
     */
    public function addBusinessResponse(string $response): void
    {
        $this->update([
            'business_response' => $response,
            'business_responded_at' => now(),
        ]);
    }

    /**
     * Check if the application is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the application is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the application is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get social media links as array.
     */
    public function getSocialMediaLinksArrayAttribute(): array
    {
        return $this->social_media_links ?? [];
    }

    /**
     * Get promotion methods as array.
     */
    public function getPromotionMethodsArrayAttribute(): array
    {
        return $this->promotion_methods ?? [];
    }

    /**
     * Get audience details as array.
     */
    public function getAudienceDetailsArrayAttribute(): array
    {
        return $this->audience_details ?? [];
    }

    /**
     * Get formatted estimated monthly visitors.
     */
    public function getFormattedEstimatedMonthlyVisitorsAttribute(): string
    {
        if (!$this->estimated_monthly_visitors) {
            return 'Not specified';
        }

        return number_format($this->estimated_monthly_visitors) . ' visitors/month';
    }
}
