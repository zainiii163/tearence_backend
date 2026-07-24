<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TemplatePurchase extends Model
{
    protected $table = 'template_purchases';

    protected $guarded = ['id'];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'fee_percent' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'download_token_expires_at' => 'datetime',
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

    public function template()
    {
        return $this->belongsTo(BusinessTemplate::class, 'business_template_id');
    }

    public function customer()
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
        if ($method) {
            $this->payment_method = $method;
        }
        if (!$this->download_token) {
            $this->download_token = Str::random(40);
            $this->download_token_expires_at = now()->addDays(30);
        }
        $this->save();
    }
}
