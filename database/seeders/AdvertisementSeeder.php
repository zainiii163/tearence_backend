<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if advertisement table exists
        if (!Schema::hasTable('advertisement')) {
            $this->command->warn('Advertisement table does not exist. Skipping AdvertisementSeeder.');
            return;
        }
        $advertisements = [
            [
                'title' => 'Premium Listing Promotion',
                'description' => 'Promote your listings and get more visibility with our premium advertising options.',
                'url' => 'https://example.com/premium',
                'image' => 'advertisements/premium-promo.jpg',
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
            ],
            [
                'title' => 'New User Special Offer',
                'description' => 'Special discount for new users. Start listing today and save!',
                'url' => 'https://example.com/special-offer',
                'image' => 'advertisements/new-user-offer.jpg',
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
            ],
            [
                'title' => 'Featured Listing Campaign',
                'description' => 'Make your listings stand out with featured placement options.',
                'url' => 'https://example.com/featured',
                'image' => 'advertisements/featured-campaign.jpg',
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(6),
            ],
        ];

        foreach ($advertisements as $ad) {
            Advertisement::create($ad);
        }
    }
}

