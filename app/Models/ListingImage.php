<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingImage extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'listing_image';

    public function getImagePathAttribute($value)
    {
        $path = str_replace("/uploads/images/listings/", "", $value);
        return $path;
    }
}
