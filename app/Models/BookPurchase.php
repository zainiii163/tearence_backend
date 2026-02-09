<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BookPurchase extends Model
{
    use HasFactory;

    protected $guarded = ['purchase_id'];

    protected $primaryKey = 'purchase_id';

    protected $table = 'book_purchases';

    protected $casts = [
        'price_paid' => 'decimal:2',
        'first_downloaded_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'download_token_expires_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($purchase) {
            if (empty($purchase->download_token)) {
                $purchase->download_token = Str::random(32);
                $purchase->download_token_expires_at = now()->addDays(7); // Token expires in 7 days
            }
        });
    }

    /**
     * Get the listing that was purchased
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }

    /**
     * Get the customer who made the purchase
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Check if the download token is valid
     */
    public function isDownloadTokenValid(): bool
    {
        return $this->payment_status === 'completed' 
            && $this->download_token 
            && $this->download_token_expires_at 
            && $this->download_token_expires_at > now();
    }

    /**
     * Check if the purchase allows downloads
     */
    public function canDownload(): bool
    {
        return $this->payment_status === 'completed' 
            && ($this->download_attempts < 5 || $this->download_token_expires_at > now());
    }

    /**
     * Increment download count and update timestamps
     */
    public function recordDownload(string $ipAddress = null): bool
    {
        if (!$this->canDownload()) {
            return false;
        }

        $this->download_attempts++;
        $this->total_downloads++;
        $this->last_downloaded_at = now();
        
        if (empty($this->first_downloaded_at)) {
            $this->first_downloaded_at = now();
        }
        
        if ($ipAddress) {
            $this->ip_address = $ipAddress;
        }

        // Update listing download count
        if ($this->listing) {
            $this->listing->increment('download_count');
            $this->listing->update(['last_downloaded_at' => now()]);
        }

        return $this->save();
    }

    /**
     * Regenerate download token
     */
    public function regenerateDownloadToken(): void
    {
        $this->download_token = Str::random(32);
        $this->download_token_expires_at = now()->addDays(7);
        $this->download_attempts = 0;
        $this->save();
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(string $paymentMethod = null): void
    {
        $this->payment_status = 'completed';
        $this->payment_method = $paymentMethod;
        $this->save();
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->payment_status = 'failed';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
    }

    /**
     * Refund the purchase
     */
    public function refund(string $reason = null): void
    {
        $this->payment_status = 'refunded';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Refunded: {$reason}";
        }
        $this->save();
    }

    /**
     * Scope to get completed purchases
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope to get pending purchases
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope to get failed purchases
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Scope to get refunded purchases
     */
    public function scopeRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }

    /**
     * Scope to get purchases with valid download tokens
     */
    public function scopeWithValidToken($query)
    {
        return $query->where('payment_status', 'completed')
                    ->whereNotNull('download_token')
                    ->where('download_token_expires_at', '>', now());
    }

    /**
     * Get download URL for the purchased file
     */
    public function getDownloadUrl(): string
    {
        if (!$this->isDownloadTokenValid()) {
            return '';
        }

        return route('books.download', ['token' => $this->download_token]);
    }
}
