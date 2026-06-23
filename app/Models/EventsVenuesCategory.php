<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventsVenuesCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'icon',
        'image',
        'sort_order',
        'is_active',
        'adverts_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function adverts(): HasMany
    {
        return $this->hasMany(EventsVenuesAdvert::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeEvents($query)
    {
        return $query->where('type', 'event')
                     ->orWhere('type', 'both');
    }

    public function scopeVenues($query)
    {
        return $query->where('type', 'venue')
                     ->orWhere('type', 'both');
    }
}
