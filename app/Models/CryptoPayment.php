<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoPayment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'pay_amount' => 'decimal:8',
        'mock' => 'boolean',
        'raw_provider_json' => 'array',
        'raw_webhook_json' => 'array',
        'completed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id', 'customer_id');
    }
}
