<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_program_id',
        'ip_address',
        'user_agent',
        'referrer',
        'country',
        'city',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function affiliateProgram(): BelongsTo
    {
        return $this->belongsTo(AffiliateProgram::class);
    }
}
