<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResortsTravelSeeder extends Seeder
{
    public function run()
    {
        // Create Resorts Travel Categories
        $categories = [
            ['name' => 'Luxury Resorts', 'slug' => 'luxury-resorts', 'description' => 'Premium luxury resort accommodations', 'icon' => 'star', 'sort_order' => 1],
            ['name' => 'Beach Hotels', 'slug' => 'beach-hotels', 'description' => 'Beachfront hotels and coastal properties', 'icon' => 'umbrella-beach', 'sort_order' => 2],
            ['name' => 'Transport Services', 'slug' => 'transport-services', 'description' => 'Airport transfers and transportation', 'icon' => 'car', 'sort_order' => 3],
            ['name' => 'Tour Experiences', 'slug' => 'tour-experiences', 'description' => 'Guided tours and experiences', 'icon' => 'map-marked', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            DB::table('resorts_travel_categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Resorts Travel Adverts
        $adverts = [
            [
                'user_id' => 2,
                'category_id' => 1,
                'title' => 'Paradise Island Resort',
                'slug' => 'paradise-island-resort',
                'tagline' => 'Ultimate luxury in the Maldives',
                'advert_type' => 'accommodation',
                'accommodation_type' => 'resort',
                'country' => 'Maldives',
                'city' => 'Male',
                'address' => 'Paradise Island, North Male Atoll',
                'latitude' => 4.1755,
                'longitude' => 73.5093,
                'price_per_night' => 850.00,
                'currency' => 'USD',
                'availability_start' => Carbon::now()->addDays(7),
                'availability_end' => Carbon::now()->addMonths(6),
                'room_types' => json_encode(['Beach Villa', 'Ocean Villa', 'Presidential Suite']),
                'amenities' => json_encode(['Spa', 'Infinity Pool', 'Private Beach', 'Water Sports', 'Fine Dining']),
                'distance_to_city_centre' => 15,
                'check_in_time' => '14:00:00',
                'check_out_time' => '11:00:00',
                'guest_capacity' => 2,
                'description' => 'Experience unparalleled luxury at our exclusive resort with private villas overlooking crystal-clear waters.',
                'overview' => '5-star luxury resort with world-class amenities and service',
                'key_features' => 'Private villas, personal butler service, spa treatments',
                'why_travellers_love_this' => 'Stunning views, exceptional service, privacy',
                'nearby_attractions' => 'Coral reefs, diving spots, local islands',
                'contact_name' => 'Reservation Team',
                'business_name' => 'Paradise Island Resorts',
                'phone_number' => '+9601234567',
                'email' => 'reservations@paradiseisland.com',
                'website' => 'https://paradiseisland.com',
                'logo' => 'resorts/paradise-logo.png',
                'verified_business' => true,
                'images' => json_encode(['resorts/paradise-1.jpg', 'resorts/paradise-2.jpg', 'resorts/paradise-3.jpg']),
                'video_link' => 'https://youtube.com/watch?v=paradise-resort',
                'main_image' => 'resorts/paradise-main.jpg',
                'promotion_tier' => 'featured',
                'is_active' => true,
            ],
            [
                'user_id' => 3,
                'category_id' => 3,
                'title' => 'Premium Airport Transfer',
                'slug' => 'premium-airport-transfer',
                'tagline' => 'Comfortable and reliable airport transfers',
                'advert_type' => 'transport',
                'transport_type' => 'airport_transfer',
                'country' => 'United Kingdom',
                'city' => 'London',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'price_per_trip' => 65.00,
                'currency' => 'GBP',
                'vehicle_type' => 'Luxury Sedan',
                'passenger_capacity' => 4,
                'luggage_capacity' => 3,
                'service_area' => 'London Heathrow, Gatwick, Stansted airports',
                'operating_hours' => json_encode(['24/7']),
                'airport_pickup' => true,
                'description' => 'Professional airport transfer service with luxury vehicles and experienced drivers.',
                'contact_name' => 'Booking Team',
                'business_name' => 'London Premium Transfers',
                'phone_number' => '+44201234567',
                'email' => 'bookings@londontransfers.com',
                'verified_business' => true,
                'promotion_tier' => 'standard',
                'is_active' => true,
            ],
            [
                'user_id' => 4,
                'category_id' => 4,
                'title' => 'Historic City Walking Tour',
                'slug' => 'historic-city-walking-tour',
                'tagline' => 'Discover the city\'s rich history',
                'advert_type' => 'experience',
                'experience_type' => 'tours',
                'country' => 'Italy',
                'city' => 'Rome',
                'latitude' => 41.9028,
                'longitude' => 12.4964,
                'price_per_service' => 45.00,
                'currency' => 'EUR',
                'duration' => '3 hours',
                'group_size' => 15,
                'whats_included' => 'Professional guide, entrance fees, audio equipment',
                'what_to_bring' => 'Comfortable walking shoes, water, camera',
                'description' => 'Expert-guided walking tour covering Rome\'s most iconic historical sites.',
                'overview' => '3-hour guided tour of ancient Rome and Renaissance landmarks',
                'key_features' => 'Small groups, expert guides, skip-the-line access',
                'why_travellers_love_this' => 'Deep historical insights, great photo opportunities',
                'nearby_attractions' => 'Colosseum, Roman Forum, Pantheon',
                'contact_name' => 'Tour Office',
                'business_name' => 'Roma Historical Tours',
                'phone_number' => '+39061234567',
                'email' => 'info@romatours.com',
                'verified_business' => true,
                'promotion_tier' => 'promoted',
                'is_active' => true,
            ],
        ];

        foreach ($adverts as $advert) {
            DB::table('resorts_travel_adverts')->insert(array_merge($advert, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Resorts & Travel seeder completed successfully!');
    }
}
