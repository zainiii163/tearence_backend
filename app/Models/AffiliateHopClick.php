<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateHopClick extends Model
{
    protected $guarded = [];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AffiliateApplication::class, 'affiliate_application_id');
    }
}
