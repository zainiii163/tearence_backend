<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuySellSavedAdvert extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'buysell_saved_adverts';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'advert_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id', 'customer_id');
    }

    public function advert(): BelongsTo
    {
        return $this->belongsTo(BuySellAdvert::class, 'advert_id');
    }
}
