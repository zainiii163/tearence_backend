<?php

namespace Database\Seeders;

use App\Models\EventsVenuesCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventsVenuesCategorySeeder extends Seeder
{
    /**
     * Seed event and venue categories for Explore Events / Explore Venues.
     */
    public function run(): void
    {
        $categories = [
            // Events
            ['name' => 'Concerts & Music', 'type' => 'event', 'description' => 'Live music, concerts and performances', 'sort_order' => 10],
            ['name' => 'Conferences & Workshops', 'type' => 'event', 'description' => 'Business conferences, seminars and workshops', 'sort_order' => 20],
            ['name' => 'Festivals', 'type' => 'event', 'description' => 'Cultural, food and community festivals', 'sort_order' => 30],
            ['name' => 'Sports & Fitness', 'type' => 'event', 'description' => 'Sporting events, matches and fitness gatherings', 'sort_order' => 40],
            ['name' => 'Parties & Nightlife', 'type' => 'event', 'description' => 'Parties, clubs and nightlife events', 'sort_order' => 50],
            ['name' => 'Networking', 'type' => 'event', 'description' => 'Networking meetups and professional events', 'sort_order' => 60],
            ['name' => 'Weddings & Celebrations', 'type' => 'event', 'description' => 'Weddings, birthdays and private celebrations', 'sort_order' => 70],
            ['name' => 'Charity & Community', 'type' => 'event', 'description' => 'Fundraisers and community events', 'sort_order' => 80],
            ['name' => 'Other Events', 'type' => 'event', 'description' => 'Other event types', 'sort_order' => 90],

            // Venues
            ['name' => 'Hotels & Ballrooms', 'type' => 'venue', 'description' => 'Hotels, ballrooms and banquet halls', 'sort_order' => 100],
            ['name' => 'Conference Centres', 'type' => 'venue', 'description' => 'Conference and meeting centres', 'sort_order' => 110],
            ['name' => 'Restaurants & Bars', 'type' => 'venue', 'description' => 'Restaurants, bars and private dining', 'sort_order' => 120],
            ['name' => 'Outdoor Spaces', 'type' => 'venue', 'description' => 'Gardens, parks and outdoor venues', 'sort_order' => 130],
            ['name' => 'Theatres & Auditoriums', 'type' => 'venue', 'description' => 'Theatres, auditoriums and stages', 'sort_order' => 140],
            ['name' => 'Community Halls', 'type' => 'venue', 'description' => 'Community and parish halls', 'sort_order' => 150],
            ['name' => 'Studios & Galleries', 'type' => 'venue', 'description' => 'Studios, galleries and creative spaces', 'sort_order' => 160],
            ['name' => 'Other Venues', 'type' => 'venue', 'description' => 'Other venue types', 'sort_order' => 170],
        ];

        foreach ($categories as $category) {
            $slug = Str::slug($category['name']);

            EventsVenuesCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
