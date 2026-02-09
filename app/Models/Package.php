<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'package_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'listing_package';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'listing_days',
        'promo_days',
        'promo_show_promoted_area',
        'promo_show_featured_area',
        'promo_show_at_top',
        'promo_sign',
        'recommended_sign',
        'auto_renewal',
        'pictures',
        'duration_days',
        'max_listings',
        'is_active',
    ];
}
