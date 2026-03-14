<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingAnalytic extends Model
{
    use HasFactory;

    protected $table = 'funding_analytics';

    protected $fillable = [
        'funding_project_id',
        'views_today',
        'shares_today',
        'pledges_today',
        'revenue_today',
        'views_total',
        'shares_total',
        'tracked_date',
    ];

    protected $casts = [
        'views_today' => 'integer',
        'shares_today' => 'integer',
        'pledges_today' => 'integer',
        'revenue_today' => 'decimal:2',
        'views_total' => 'integer',
        'shares_total' => 'integer',
        'tracked_date' => 'date',
    ];

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }
}
