<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BookAdvertPurchase extends Model
{
    protected $table = 'book_advert_purchases';

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

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id');
    }

    public function isDownloadValid(): bool
    {
        return $this->payment_status === 'completed'
            && $this->download_token
            && $this->download_token_expires_at
            && $this->download_token_expires_at->isFuture();
    }

    public function markCompleted(string $paymentMethod = 'paypal'): void
    {
        $this->payment_status = 'completed';
        $this->payment_method = $paymentMethod;
        if (empty($this->download_token)) {
            $this->download_token = Str::random(40);
        }
        $this->download_token_expires_at = now()->addDays(30);
        $this->save();
    }
}
