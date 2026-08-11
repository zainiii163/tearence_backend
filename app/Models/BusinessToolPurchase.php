<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BusinessToolPurchase extends Model
{
    protected $table = 'business_tool_purchases';

    protected $fillable = [
        'tool_id',
        'customer_id',
        'amount',
        'currency',
        'payment_method',
        'payment_reference',
        'status',
        'download_token',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(BusinessTool::class, 'tool_id');
    }

    public static function mintToken(): string
    {
        return Str::random(48);
    }
}
