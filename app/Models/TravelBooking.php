<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelBooking extends Model
{
    protected $table = 'travel_bookings';

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_per_unit' => 'decimal:2',
        'total_price' => 'decimal:2',
        'guests' => 'integer',
    ];

    public function advert(): BelongsTo
    {
        return $this->belongsTo(ResortsTravel::class, 'advert_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }
}