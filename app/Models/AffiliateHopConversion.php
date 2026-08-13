<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ledger row for a sale attributed via hop link / cookie (Ahrefs-style).
 */
class AffiliateHopConversion extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sale_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'meta' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AffiliateApplication::class, 'affiliate_application_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(BusinessAffiliateOffer::class, 'business_affiliate_offer_id');
    }
}
