<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyEnquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'type',
        'contacted',
        'contacted_at',
    ];

    protected $casts = [
        'contacted' => 'boolean',
        'contacted_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getTypes(): array
    {
        return [
            'general' => 'General Inquiry',
            'schedule_viewing' => 'Schedule Viewing',
            'price_inquiry' => 'Price Inquiry',
            'financing' => 'Financing Information',
        ];
    }
}
