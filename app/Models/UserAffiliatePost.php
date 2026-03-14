<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UserAffiliatePost extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'hashtags' => 'array',
        'is_promoted' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'moderated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that created the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for this post.
     */
    public function affiliateCategory(): BelongsTo
    {
        return $this->belongsTo(AffiliateCategory::class);
    }

    /**
     * Get the analytics for this post.
     */
    public function analytics(): MorphMany
    {
        return $this->morphMany(AffiliateAnalytics::class, 'affiliatable');
    }

    /**
     * Get the upsells for this post.
     */
    public function upsells(): MorphMany
    {
        return $this->morphMany(AffiliatePostUpsell::class, 'affiliatable');
    }

    /**
     * Get the admin who moderated this post.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope a query to only include active posts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('status', 'approved')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope a query to only include promoted posts.
     */
    public function scopePromoted($query)
    {
        return $query->where('is_promoted', true);
    }

    /**
     * Scope a query to only include featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include sponsored posts.
     */
    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    /**
     * Scope a query to only include paid posts.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include posts pending moderation.
     */
    public function scopePendingModeration($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved posts.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected posts.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the post is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active || $this->status !== 'approved') {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        $fileUpload = new FileUploadHelper();
        return $fileUpload->getFile($this->image, 'affiliate_posts');
    }

    /**
     * Get the full affiliate link URL.
     */
    public function getFullAffiliateLinkAttribute(): string
    {
        return $this->affiliate_link ?? '#';
    }

    /**
     * Get hashtags as a string.
     */
    public function getHashtagsStringAttribute(): string
    {
        if (!$this->hashtags) {
            return '';
        }

        return '#' . implode(' #', $this->hashtags);
    }

    /**
     * Set hashtags from string.
     */
    public function setHashtagsFromStringAttribute(string $value): void
    {
        $hashtags = array_filter(array_map('trim', explode('#', $value)));
        $this->hashtags = $hashtags;
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
        
        // Also track in analytics
        $this->analytics()->create([
            'date' => now()->toDateString(),
            'views' => 1,
            'unique_views' => 1,
        ]);
    }

    /**
     * Increment clicks count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');
        
        // Also track in analytics
        $this->analytics()->create([
            'date' => now()->toDateString(),
            'clicks' => 1,
            'unique_clicks' => 1,
        ]);
    }

    /**
     * Increment shares count.
     */
    public function incrementShares(): void
    {
        $this->increment('shares');
    }

    /**
     * Approve the post.
     */
    public function approve(?int $moderatorId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'moderation_notes' => $notes,
        ]);
    }

    /**
     * Reject the post.
     */
    public function reject(?int $moderatorId = null, ?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'moderation_notes' => $reason,
        ]);
    }
}
