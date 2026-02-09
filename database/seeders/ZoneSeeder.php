<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get countries
        $usa = Country::where('code', 'USA')->first();
        $canada = Country::where('code', 'CAN')->first();
        $uk = Country::where('code', 'GBR')->first();
        $australia = Country::where('code', 'AUS')->first();
        $germany = Country::where('code', 'DEU')->first();
        $france = Country::where('code', 'FRA')->first();
        $india = Country::where('code', 'IND')->first();

        $zones = [];

        // USA States
        if ($usa) {
            $usStates = [
                ['name' => 'California', 'code' => 'CA', 'sort_order' => 1],
                ['name' => 'Texas', 'code' => 'TX', 'sort_order' => 2],
                ['name' => 'Florida', 'code' => 'FL', 'sort_order' => 3],
                ['name' => 'New York', 'code' => 'NY', 'sort_order' => 4],
                ['name' => 'Illinois', 'code' => 'IL', 'sort_order' => 5],
                ['name' => 'Pennsylvania', 'code' => 'PA', 'sort_order' => 6],
                ['name' => 'Ohio', 'code' => 'OH', 'sort_order' => 7],
                ['name' => 'Georgia', 'code' => 'GA', 'sort_order' => 8],
                ['name' => 'North Carolina', 'code' => 'NC', 'sort_order' => 9],
                ['name' => 'Michigan', 'code' => 'MI', 'sort_order' => 10],
            ];

            foreach ($usStates as $state) {
                $zones[] = [
                    'name' => $state['name'],
                    'code' => $state['code'],
                    'country_id' => $usa->country_id,
                    'is_active' => true,
                    'sort_order' => $state['sort_order'],
                ];
            }
        }

        // Canada Provinces
        if ($canada) {
            $canadaProvinces = [
                ['name' => 'Ontario', 'code' => 'ON', 'sort_order' => 1],
                ['name' => 'Quebec', 'code' => 'QC', 'sort_order' => 2],
                ['name' => 'British Columbia', 'code' => 'BC', 'sort_order' => 3],
                ['name' => 'Alberta', 'code' => 'AB', 'sort_order' => 4],
                ['name' => 'Manitoba', 'code' => 'MB', 'sort_order' => 5],
            ];

            foreach ($canadaProvinces as $province) {
                $zones[] = [
                    'name' => $province['name'],
                    'code' => $province['code'],
                    'country_id' => $canada->country_id,
                    'is_active' => true,
                    'sort_order' => $province['sort_order'],
                ];
            }
        }

        // UK Regions
        if ($uk) {
            $ukRegions = [
                ['name' => 'England', 'code' => 'ENG', 'sort_order' => 1],
                ['name' => 'Scotland', 'code' => 'SCT', 'sort_order' => 2],
                ['name' => 'Wales', 'code' => 'WLS', 'sort_order' => 3],
                ['name' => 'Northern Ireland', 'code' => 'NIR', 'sort_order' => 4],
            ];

            foreach ($ukRegions as $region) {
                $zones[] = [
                    'name' => $region['name'],
                    'code' => $region['code'],
                    'country_id' => $uk->country_id,
                    'is_active' => true,
                    'sort_order' => $region['sort_order'],
                ];
            }
        }

        // Australia States
        if ($australia) {
            $ausStates = [
                ['name' => 'New South Wales', 'code' => 'NSW', 'sort_order' => 1],
                ['name' => 'Victoria', 'code' => 'VIC', 'sort_order' => 2],
                ['name' => 'Queensland', 'code' => 'QLD', 'sort_order' => 3],
                ['name' => 'Western Australia', 'code' => 'WA', 'sort_order' => 4],
                ['name' => 'South Australia', 'code' => 'SA', 'sort_order' => 5],
            ];

            foreach ($ausStates as $state) {
                $zones[] = [
                    'name' => $state['name'],
                    'code' => $state['code'],
                    'country_id' => $australia->country_id,
                    'is_active' => true,
                    'sort_order' => $state['sort_order'],
                ];
            }
        }

        // Germany States
        if ($germany) {
            $germanyStates = [
                ['name' => 'Bavaria', 'code' => 'BY', 'sort_order' => 1],
                ['name' => 'Berlin', 'code' => 'BE', 'sort_order' => 2],
                ['name' => 'Hamburg', 'code' => 'HH', 'sort_order' => 3],
                ['name' => 'North Rhine-Westphalia', 'code' => 'NW', 'sort_order' => 4],
            ];

            foreach ($germanyStates as $state) {
                $zones[] = [
                    'name' => $state['name'],
                    'code' => $state['code'],
                    'country_id' => $germany->country_id,
                    'is_active' => true,
                    'sort_order' => $state['sort_order'],
                ];
            }
        }

        // France Regions
        if ($france) {
            $franceRegions = [
                ['name' => 'Île-de-France', 'code' => 'IDF', 'sort_order' => 1],
                ['name' => 'Provence-Alpes-Côte d\'Azur', 'code' => 'PACA', 'sort_order' => 2],
                ['name' => 'Auvergne-Rhône-Alpes', 'code' => 'ARA', 'sort_order' => 3],
            ];

            foreach ($franceRegions as $region) {
                $zones[] = [
                    'name' => $region['name'],
                    'code' => $region['code'],
                    'country_id' => $france->country_id,
                    'is_active' => true,
                    'sort_order' => $region['sort_order'],
                ];
            }
        }

        // India States
        if ($india) {
            $indiaStates = [
                ['name' => 'Maharashtra', 'code' => 'MH', 'sort_order' => 1],
                ['name' => 'Karnataka', 'code' => 'KA', 'sort_order' => 2],
                ['name' => 'Tamil Nadu', 'code' => 'TN', 'sort_order' => 3],
                ['name' => 'Delhi', 'code' => 'DL', 'sort_order' => 4],
                ['name' => 'Gujarat', 'code' => 'GJ', 'sort_order' => 5],
            ];

            foreach ($indiaStates as $state) {
                $zones[] = [
                    'name' => $state['name'],
                    'code' => $state['code'],
                    'country_id' => $india->country_id,
                    'is_active' => true,
                    'sort_order' => $state['sort_order'],
                ];
            }
        }

        foreach ($zones as $zone) {
            Zone::create($zone);
        }
    }
}

