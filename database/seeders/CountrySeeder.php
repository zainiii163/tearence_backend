<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Support\WorldCountries;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Seed the full worldwide country list with ISO codes and flag codes.
     */
    public function run(): void
    {
        $sort = 1;

        foreach (WorldCountries::all() as $row) {
            Country::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'iso_code' => $row['iso_code'],
                    'flag' => strtolower($row['iso_code']),
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]
            );
        }
    }
}
