<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'location';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'location_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'listing_id',
        'country_id',
        'zone_id',
        'city',
        'zip',
        'latitude',
        'longitude',
    ];

    // Automatically append these attributes when the model is serialized
    protected $appends = ['country_name', 'zone_name'];

    // Accessor for country_name
    public function getCountryNameAttribute()
    {
        // Query the ea_country table to get the country_name
        $country = DB::table('country')
                     ->where('country_id', $this->country_id)
                     ->value('name');

        return $country ?: null;
    }

    // Accessor for zone_name
    public function getZoneNameAttribute()
    {
        // Query the ea_zone table to get the zone_name
        $zone = DB::table('zone')
                  ->where('zone_id', $this->zone_id)
                  ->value('name');

        return $zone ?: null;
    }

    /**
     * Get the customer that owns the location.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the listing associated with the location.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }
}
