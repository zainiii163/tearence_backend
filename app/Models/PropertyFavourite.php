<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyFavourite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
    ];

    public function user(): BelongsTo
    {
        // Marketplace JWT auth stores customer_id in user_id
        return $this->belongsTo(Customer::class, 'user_id', 'customer_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
