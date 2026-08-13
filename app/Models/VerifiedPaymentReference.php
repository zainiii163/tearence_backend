<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifiedPaymentReference extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'verified_at' => 'datetime',
    ];
}
