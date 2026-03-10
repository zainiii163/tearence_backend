<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingFavorite extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'favorite_id';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['customer', 'listing'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'listing_favorite';

    /**
     * Get the customer that owns the listingFavorite.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the listing.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }
}
