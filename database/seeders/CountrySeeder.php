<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'United States',
                'code' => 'USA',
                'iso_code' => 'US',
                'flag' => 'us',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'United Kingdom',
                'code' => 'GBR',
                'iso_code' => 'GB',
                'flag' => 'gb',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Canada',
                'code' => 'CAN',
                'iso_code' => 'CA',
                'flag' => 'ca',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Australia',
                'code' => 'AUS',
                'iso_code' => 'AU',
                'flag' => 'au',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Germany',
                'code' => 'DEU',
                'iso_code' => 'DE',
                'flag' => 'de',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'France',
                'code' => 'FRA',
                'iso_code' => 'FR',
                'flag' => 'fr',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Italy',
                'code' => 'ITA',
                'iso_code' => 'IT',
                'flag' => 'it',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Spain',
                'code' => 'ESP',
                'iso_code' => 'ES',
                'flag' => 'es',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Japan',
                'code' => 'JPN',
                'iso_code' => 'JP',
                'flag' => 'jp',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'China',
                'code' => 'CHN',
                'iso_code' => 'CN',
                'flag' => 'cn',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'India',
                'code' => 'IND',
                'iso_code' => 'IN',
                'flag' => 'in',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Brazil',
                'code' => 'BRA',
                'iso_code' => 'BR',
                'flag' => 'br',
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'Mexico',
                'code' => 'MEX',
                'iso_code' => 'MX',
                'flag' => 'mx',
                'is_active' => true,
                'sort_order' => 13,
            ],
            [
                'name' => 'Singapore',
                'code' => 'SGP',
                'iso_code' => 'SG',
                'flag' => 'sg',
                'is_active' => true,
                'sort_order' => 14,
            ],
            [
                'name' => 'United Arab Emirates',
                'code' => 'ARE',
                'iso_code' => 'AE',
                'flag' => 'ae',
                'is_active' => true,
                'sort_order' => 15,
            ],
            [
                'name' => 'Saudi Arabia',
                'code' => 'SAU',
                'iso_code' => 'SA',
                'flag' => 'sa',
                'is_active' => true,
                'sort_order' => 16,
            ],
            [
                'name' => 'South Africa',
                'code' => 'ZAF',
                'iso_code' => 'ZA',
                'flag' => 'za',
                'is_active' => true,
                'sort_order' => 17,
            ],
            [
                'name' => 'South Korea',
                'code' => 'KOR',
                'iso_code' => 'KR',
                'flag' => 'kr',
                'is_active' => true,
                'sort_order' => 18,
            ],
            [
                'name' => 'Netherlands',
                'code' => 'NLD',
                'iso_code' => 'NL',
                'flag' => 'nl',
                'is_active' => true,
                'sort_order' => 19,
            ],
            [
                'name' => 'Switzerland',
                'code' => 'CHE',
                'iso_code' => 'CH',
                'flag' => 'ch',
                'is_active' => true,
                'sort_order' => 20,
            ],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}

