<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsoredCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'description',
    ];

    /**
     * Get the adverts for this category.
     */
    public function adverts()
    {
        return $this->hasMany(SponsoredAdvert::class);
    }

    /**
     * Get the adverts count for this category.
     */
    public function getAdvertsCountAttribute()
    {
        return $this->adverts()->active()->count();
    }
}
