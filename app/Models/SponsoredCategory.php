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
        'is_active',
        'sort_order',
    ];

    /**
     * Get the adverts for this category.
     */
    public function adverts()
    {
        return $this->hasMany(SponsoredAdvert::class, 'category_id');
    }

    /**
     * Get active adverts count for this category.
     */
    public function getActiveAdvertsCountAttribute()
    {
        return $this->adverts()->active()->count();
    }
}
