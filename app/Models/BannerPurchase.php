<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BannerPurchase extends Model
{
    protected $table = 'banner_purchases';

    protected $guarded = ['id'];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'download_token_expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $purchase) {
            if (empty($purchase->download_token)) {
                $purchase->download_token = Str::random(40);
                $purchase->download_token_expires_at = now()->addDays(30);
            }
        });
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(BannerAd::class, 'banner_ad_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function isDownloadValid(): bool
    {
        return $this->payment_status === 'completed'
            && $this->download_token
            && $this->download_token_expires_at
            && $this->download_token_expires_at->isFuture();
    }

    public function markCompleted(?string $method = null): void
    {
        $this->payment_status = 'completed';
        $this->paid_at = now();
        if ($method) {
            $this->payment_method = $method;
        }
        if (!$this->download_token) {
            $this->download_token = Str::random(40);
        }
        $this->download_token_expires_at = now()->addDays(30);
        $this->save();
    }
}
