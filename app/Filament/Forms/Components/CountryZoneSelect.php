<?php

namespace App\Filament\Forms\Components;

use App\Models\Country;
use App\Models\Zone;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Schema;

/**
 * Cascading Country → Zone (City/State) select fields.
 * Usage: CountryZoneSelect::make('country_id', 'zone_id')
 */
class CountryZoneSelect
{
    public static function make(string $countryField = 'country_id', string $zoneField = 'zone_id'): array
    {
        return [
            Select::make($countryField)
                ->label('Country')
                ->options(fn () => static::countryQuery()->pluck('name', 'country_id')->toArray())
                ->searchable()
                ->preload()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get) {
                    $set($zoneField, null);
                })
                ->required(),

            Select::make($zoneField)
                ->label('City / State')
                ->options(function (Get $get) use ($countryField) {
                    $countryId = $get($countryField);
                    if (! $countryId) {
                        return [];
                    }
                    return static::zoneQuery($countryId)->pluck('name', 'zone_id')->toArray();
                })
                ->searchable()
                ->preload()
                ->disabled(fn (Get $get) => ! $get($countryField))
                ->dehydrated(fn (Get $get) => (bool) $get($countryField))
                ->required(),
        ];
    }

    /** Keyed by ISO-2 code (for external APIs). */
    public static function makeIso(string $countryField = 'country', string $zoneField = 'zone'): array
    {
        return [
            Select::make($countryField)
                ->label('Country')
                ->options(fn () => static::countryQuery()->pluck('name', 'iso_code')->toArray())
                ->searchable()
                ->preload()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set) {
                    $set($zoneField, null);
                })
                ->required(),

            Select::make($zoneField)
                ->label('City / State')
                ->options(function (Get $get) use ($countryField) {
                    $isoCode = $get($countryField);
                    if (! $isoCode) {
                        return [];
                    }
                    $country = Country::where('iso_code', strtoupper($isoCode))->first();
                    if (! $country) {
                        return [];
                    }
                    return static::zoneQuery($country->country_id)->pluck('name', 'zone_id')->toArray();
                })
                ->searchable()
                ->preload()
                ->disabled(fn (Get $get) => ! $get($countryField))
                ->required(),
        ];
    }

    protected static function countryQuery()
    {
        $query = Country::query()->orderBy('name');
        if (Schema::hasColumn('country', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query;
    }

    protected static function zoneQuery($countryId)
    {
        $query = Zone::query()->where('country_id', $countryId);
        if (Schema::hasColumn('zone', 'is_active')) {
            $query->where('is_active', true);
        }
        if (Schema::hasColumn('zone', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        return $query->orderBy('name');
    }
}