<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ImageAdvertPurchase extends Model
{
    protected $table = 'image_advert_purchases';

    protected $guarded = [];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'download_token_expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $purchase) {
            if (empty($purchase->download_token)) {
                $purchase->download_token = Str::random(40);
                $purchase->download_token_expires_at = now()->addDays(30);
            }
        });
    }

    public function isValid(): bool
    {
        return $this->payment_status === 'completed'
            && $this->download_token
            && $this->download_token_expires_at
            && $this->download_token_expires_at->isFuture();
    }

    public function markCompleted(string $method = 'paypal'): void
    {
        $this->payment_status = 'completed';
        $this->payment_method = $method;
        if (empty($this->download_token)) {
            $this->download_token = Str::random(40);
        }
        $this->download_token_expires_at = now()->addDays(30);
        $this->save();
    }
}
