<?php

namespace App\Filament\Forms\Components;

use App\Support\CountrySelectOptions;
use Filament\Forms\Components\Select;

/**
 * Searchable worldwide country dropdown with flag emoji labels.
 */
class CountrySelect
{
    public static function make(string $name = 'country'): Select
    {
        return Select::make($name)
            ->label('Country')
            ->options(fn () => CountrySelectOptions::byNameWithFallback())
            ->searchable()
            ->preload();
    }

    /** Options keyed by ISO-2 (US, GB, …). */
    public static function makeIso(string $name = 'country'): Select
    {
        return Select::make($name)
            ->label('Country')
            ->options(fn () => CountrySelectOptions::byIsoWithFallback())
            ->searchable()
            ->preload();
    }

    /** Options keyed by country_id. */
    public static function makeId(string $name = 'country_id'): Select
    {
        return Select::make($name)
            ->label('Country')
            ->options(fn () => CountrySelectOptions::byId())
            ->searchable()
            ->preload();
    }
}
