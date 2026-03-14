<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'event_type',
        'ip_address',
        'user_agent',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getEventTypes(): array
    {
        return [
            'view' => 'View',
            'save' => 'Save',
            'enquiry' => 'Enquiry',
            'contact' => 'Contact',
        ];
    }
}
