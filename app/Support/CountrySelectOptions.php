<?php

namespace App\Support;

use App\Models\Country;
use Illuminate\Support\Facades\Schema;

/**
 * Shared country select options for Filament forms (with flag emoji).
 */
class CountrySelectOptions
{
    /**
     * Options keyed by country name: "🇨🇦 Canada"
     *
     * @return array<string, string>
     */
    public static function byName(): array
    {
        return self::query()
            ->mapWithKeys(function (Country $country) {
                $label = trim(($country->flag_emoji ?? '🏳️') . ' ' . $country->name);

                return [$country->name => $label];
            })
            ->all();
    }

    /**
     * Options keyed by country_id: "🇨🇦 Canada"
     *
     * @return array<int|string, string>
     */
    public static function byId(): array
    {
        return self::query()
            ->mapWithKeys(function (Country $country) {
                $label = trim(($country->flag_emoji ?? '🏳️') . ' ' . $country->name);

                return [$country->country_id => $label];
            })
            ->all();
    }

    /**
     * Options keyed by ISO-2: "🇨🇦 Canada"
     *
     * @return array<string, string>
     */
    public static function byIso(): array
    {
        return self::query()
            ->filter(fn (Country $country) => filled($country->iso_code))
            ->mapWithKeys(function (Country $country) {
                $label = trim(($country->flag_emoji ?? '🏳️') . ' ' . $country->name);

                return [strtoupper($country->iso_code) => $label];
            })
            ->all();
    }

    /**
     * Fallback to static WorldCountries list if DB is empty / unavailable.
     *
     * @return array<string, string>
     */
    public static function byNameWithFallback(): array
    {
        $fromDb = self::byName();
        if (! empty($fromDb)) {
            return $fromDb;
        }

        $options = [];
        foreach (WorldCountries::all() as $row) {
            $options[$row['name']] = WorldCountries::flagEmoji($row['iso_code']) . ' ' . $row['name'];
        }
        ksort($options);

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function byIsoWithFallback(): array
    {
        $fromDb = self::byIso();
        if (! empty($fromDb)) {
            return $fromDb;
        }

        $options = [];
        foreach (WorldCountries::all() as $row) {
            $options[$row['iso_code']] = WorldCountries::flagEmoji($row['iso_code']) . ' ' . $row['name'];
        }
        ksort($options);

        return $options;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Country>
     */
    protected static function query()
    {
        if (! Schema::hasTable('country')) {
            return collect();
        }

        $query = Country::query()->orderBy('name');
        if (Schema::hasColumn('country', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->get();
    }
}
