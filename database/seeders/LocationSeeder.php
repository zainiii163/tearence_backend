<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Country;
use App\Models\Zone;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usa = Country::where('code', 'USA')->first();
        $canada = Country::where('code', 'CAN')->first();
        $uk = Country::where('code', 'GBR')->first();

        $customers = Customer::take(5)->get();
        $zones = Zone::take(10)->get();

        $locations = [
            [
                'customer_id' => $customers->count() > 0 ? $customers[0]->customer_id : null,
                'country_id' => $usa ? $usa->country_id : null,
                'zone_id' => $zones->count() > 0 ? $zones[0]->zone_id : null,
                'city' => 'New York',
                'zip' => '10001',
                'latitude' => '40.7128',
                'longitude' => '-74.0060',
            ],
            [
                'customer_id' => $customers->count() > 1 ? $customers[1]->customer_id : null,
                'country_id' => $usa ? $usa->country_id : null,
                'zone_id' => $zones->count() > 1 ? $zones[1]->zone_id : null,
                'city' => 'Los Angeles',
                'zip' => '90001',
                'latitude' => '34.0522',
                'longitude' => '-118.2437',
            ],
            [
                'customer_id' => $customers->count() > 2 ? $customers[2]->customer_id : null,
                'country_id' => $canada ? $canada->country_id : null,
                'zone_id' => $zones->count() > 2 ? $zones[2]->zone_id : null,
                'city' => 'Toronto',
                'zip' => 'M5H 2N2',
                'latitude' => '43.6532',
                'longitude' => '-79.3832',
            ],
            [
                'customer_id' => $customers->count() > 3 ? $customers[3]->customer_id : null,
                'country_id' => $uk ? $uk->country_id : null,
                'zone_id' => $zones->count() > 3 ? $zones[3]->zone_id : null,
                'city' => 'London',
                'zip' => 'SW1A 1AA',
                'latitude' => '51.5074',
                'longitude' => '-0.1278',
            ],
            [
                'customer_id' => $customers->count() > 4 ? $customers[4]->customer_id : null,
                'country_id' => $usa ? $usa->country_id : null,
                'zone_id' => $zones->count() > 4 ? $zones[4]->zone_id : null,
                'city' => 'Chicago',
                'zip' => '60601',
                'latitude' => '41.8781',
                'longitude' => '-87.6298',
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}

