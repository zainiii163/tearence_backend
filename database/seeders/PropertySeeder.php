<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PropertySeeder extends Seeder
{
    public function run()
    {
        // Create Property Analytics
        $analytics = [
            [
                'property_id' => 1,
                'event_type' => 'view',
                'user_id' => 2,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'country' => 'United States',
                'city' => 'New York',
                'metadata' => json_encode(['source' => 'search_results', 'duration' => 45]),
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'property_id' => 1,
                'event_type' => 'inquiry',
                'user_id' => 3,
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'country' => 'United States',
                'city' => 'Boston',
                'metadata' => json_encode(['message_type' => 'property_question', 'response_time' => 120]),
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'property_id' => 2,
                'event_type' => 'save',
                'user_id' => 2,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'country' => 'United States',
                'city' => 'New York',
                'metadata' => json_encode(['saved_from' => 'property_details']),
                'created_at' => Carbon::now()->subHours(5),
            ],
            [
                'property_id' => 2,
                'event_type' => 'contact_agent',
                'user_id' => 4,
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15',
                'country' => 'Canada',
                'city' => 'Toronto',
                'metadata' => json_encode(['contact_method' => 'phone', 'call_duration' => 300]),
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'property_id' => 3,
                'event_type' => 'share',
                'ip_address' => '192.168.1.103',
                'user_agent' => 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
                'country' => 'United Kingdom',
                'city' => 'London',
                'metadata' => json_encode(['share_platform' => 'facebook', 'share_type' => 'property_link']),
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'property_id' => 1,
                'event_type' => 'gallery_view',
                'user_id' => null,
                'ip_address' => '192.168.1.104',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'country' => 'United States',
                'city' => 'Chicago',
                'metadata' => json_encode(['images_viewed' => 8, 'total_duration' => 120]),
                'created_at' => Carbon::now()->subDays(3),
            ],
        ];

        foreach ($analytics as $analytic) {
            DB::table('property_analytics')->insert(array_merge($analytic, [
                'created_at' => $analytic['created_at'] ?? now(),
            ]));
        }

        // Create Property Saved
        $saved_properties = [
            [
                'property_id' => 1,
                'user_id' => 2,
            ],
            [
                'property_id' => 1,
                'user_id' => 5,
            ],
            [
                'property_id' => 2,
                'user_id' => 2,
            ],
            [
                'property_id' => 2,
                'user_id' => 6,
            ],
            [
                'property_id' => 2,
                'user_id' => 7,
            ],
            [
                'property_id' => 3,
                'user_id' => 5,
            ],
            [
                'property_id' => 3,
                'user_id' => 8,
            ],
            [
                'property_id' => 4,
                'user_id' => 6,
            ],
        ];

        foreach ($saved_properties as $saved) {
            DB::table('property_saved')->insert(array_merge($saved, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Property seeder completed successfully!');
    }
}
