<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalAreaAlert extends Model
{
    protected $table = 'local_area_alerts';

    protected $fillable = [
        'customer_id',
        'type',
        'title',
        'message',
        'city',
        'country',
        'area',
        'latitude',
        'longitude',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public const TYPE_PARKING = 'parking';
    public const TYPE_TRAFFIC = 'traffic';

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeInArea($query, ?string $city, ?string $country)
    {
        $city = mb_strtolower(trim((string) $city));
        $country = \App\Support\GeoIpLocator::normalizeCountry($country);

        return $query->where(function ($q) use ($city, $country) {
            if ($city !== '') {
                $q->whereRaw('LOWER(city) = ?', [$city]);
                if ($country !== '') {
                    $q->orWhere(function ($inner) use ($country) {
                        $inner->where(function ($emptyCity) {
                            $emptyCity->whereNull('city')->orWhere('city', '');
                        })->whereRaw('LOWER(country) = ?', [$country]);
                    });
                }
            } elseif ($country !== '') {
                $q->whereRaw('LOWER(country) = ?', [$country]);
            } else {
                $q->whereRaw('1 = 0');
            }
        });
    }
}
