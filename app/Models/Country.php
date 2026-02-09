<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'country';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'country_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'iso_code',
        'flag',
        'is_active',
        'sort_order',
    ];

    /**
     * Set the country code attribute to uppercase.
     *
     * @param  string  $value
     * @return void
     */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value ?? '');
    }

    /**
     * Set the ISO code attribute to uppercase.
     *
     * @param  string  $value
     * @return void
     */
    public function setIsoCodeAttribute($value)
    {
        $this->attributes['iso_code'] = $value ? strtoupper($value) : null;
    }
}
