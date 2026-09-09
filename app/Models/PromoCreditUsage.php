<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCreditUsage extends Model
{
    protected $fillable = [
        'customer_promo_credit_id',
        'customer_id',
        'listing_type',
        'listing_id',
        'tier',
    ];

    public function credit(): BelongsTo
    {
        return $this->belongsTo(CustomerPromoCredit::class, 'customer_promo_credit_id');
    }
}
