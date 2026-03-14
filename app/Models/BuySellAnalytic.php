<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'date',
        'views',
        'contacts',
        'saves',
        'shares',
        'search_terms',
    ];

    protected $casts = [
        'date' => 'date',
        'views' => 'integer',
        'contacts' => 'integer',
        'saves' => 'integer',
        'shares' => 'integer',
        'search_terms' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(BuySellItem::class, 'item_id');
    }
}
