<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'funding_project_id',
    ];

    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }
}
