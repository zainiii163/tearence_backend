<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellPurchase extends Model
{
    protected $table = 'buy_sell_purchases';

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'fee_percent' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function advert(): BelongsTo
    {
        return $this->belongsTo(BuySellAdvert::class, 'buysell_advert_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'buyer_id', 'customer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'seller_id', 'customer_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function markPaid(string $method, string $paymentId): void
    {
        $this->payment_status = 'paid';
        $this->payment_method = $method;
        $this->payment_id = $paymentId;
        $this->paid_at = now();
        $this->save();
    }
}
