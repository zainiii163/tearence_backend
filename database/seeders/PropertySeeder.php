<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class PropertySeeder extends Seeder
{
    public function run()
    {
        // Get sample users
        $users = User::take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // Create sample properties
        $properties = [
            [
                'user_id' => $users[0]->id,
                'title' => 'Modern Downtown Apartment with City Views',
                'tagline' => 'Luxury living in the heart of the city',
                'category' => 'rent',
                'property_type' => 'residential',
                'country' => 'United States',
                'city' => 'New York',
                'address' => '350 5th Ave, New York, NY 10118',
                'latitude' => 40.7614,
                'longitude' => -73.9776,
                'price' => 3500.00,
                'currency' => 'USD',
                'negotiable' => false,
                'deposit' => 7000.00,
                'service_charges' => 150.00,
                'maintenance_fees' => 200.00,
                'cover_image' => 'properties/cover/apartment1.jpg',
                'additional_images' => json_encode([
                    'properties/additional/apartment1_1.jpg',
                    'properties/additional/apartment1_2.jpg',
                    'properties/additional/apartment1_3.jpg'
                ]),
                'video_tour_link' => 'https://www.youtube.com/watch?v=example1',
                'description' => 'Experience luxury living in this stunning modern apartment located in the heart of Manhattan. This beautifully designed residence features floor-to-ceiling windows offering breathtaking city views, high-end finishes, and state-of-the-art amenities throughout.',
                'specifications' => json_encode([
                    'bedrooms' => 2,
                    'bathrooms' => 2,
                    'square_feet' => 1200,
                    'parking_spaces' => 1,
                    'furnished' => true,
                    'year_built' => 2020
                ]),
                'amenities' => json_encode([
                    'Gym', 'Pool', 'Concierge', 'Security', 'Parking', 'Storage', 'Balcony', 'In-unit Laundry'
                ]),
                'location_highlights' => json_encode([
                    'Walking distance to Central Park',
                    'Near Times Square',
                    'Excellent restaurants nearby',
                    'Shopping districts'
                ]),
                'transport_links' => json_encode([
                    'Subway station 2 blocks away',
                    'Bus stop on corner',
                    'Easy highway access'
                ]),
                'seller_name' => 'John Smith',
                'seller_company' => 'Manhattan Real Estate',
                'seller_phone' => '+1 555-0123',
                'seller_email' => 'john@manhattanrealestate.com',
                'seller_website' => 'https://manhattanrealestate.com',
                'seller_logo' => 'properties/logos/manhattan_logo.jpg',
                'verified_agent' => true,
                'advert_type' => 'featured',
                'featured_until' => Carbon::now()->addDays(14),
                'views' => 245,
                'saves' => 18,
                'enquiries' => 7,
                'active' => true,
                'approved' => true,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'user_id' => $users[1]->id,
                'title' => 'Spacious Family Home in Suburban Paradise',
                'tagline' => 'Perfect for growing families',
                'category' => 'buy',
                'property_type' => 'residential',
                'country' => 'United States',
                'city' => 'Los Angeles',
                'address' => '1234 Oak Street, Beverly Hills, CA 90210',
                'latitude' => 34.0736,
                'longitude' => -118.4004,
                'price' => 1250000.00,
                'currency' => 'USD',
                'negotiable' => true,
                'deposit' => null,
                'service_charges' => null,
                'maintenance_fees' => 300.00,
                'cover_image' => 'properties/cover/house1.jpg',
                'additional_images' => json_encode([
                    'properties/additional/house1_1.jpg',
                    'properties/additional/house1_2.jpg',
                    'properties/additional/house1_3.jpg',
                    'properties/additional/house1_4.jpg'
                ]),
                'video_tour_link' => null,
                'description' => 'This beautiful family home offers the perfect blend of comfort and luxury. Located in the prestigious Beverly Hills area, this property features spacious living areas, a beautiful backyard, and modern amenities that make it ideal for families.',
                'specifications' => json_encode([
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'square_feet' => 3200,
                    'lot_size' => 8500,
                    'garage_spaces' => 2,
                    'year_built' => 2018,
                    'stories' => 2
                ]),
                'amenities' => json_encode([
                    'Swimming Pool', 'Garden', 'Garage', 'Fireplace', 'Gourmet Kitchen', 'Home Office', 'Walk-in Closet'
                ]),
                'location_highlights' => json_encode([
                    'Top-rated schools nearby',
                    'Shopping centers',
                    'Parks and recreation',
                    'Quiet neighborhood'
                ]),
                'transport_links' => json_encode([
                    'Near major highways',
                    'Public transportation available',
                    '30 minutes to downtown LA'
                ]),
                'seller_name' => 'Sarah Johnson',
                'seller_company' => 'Beverly Hills Luxury Homes',
                'seller_phone' => '+1 555-0124',
                'seller_email' => 'sarah@beverlyhillshomes.com',
                'seller_website' => 'https://beverlyhillshomes.com',
                'seller_logo' => 'properties/logos/beverly_logo.jpg',
                'verified_agent' => true,
                'advert_type' => 'sponsored',
                'sponsored_until' => Carbon::now()->addDays(30),
                'views' => 512,
                'saves' => 34,
                'enquiries' => 12,
                'active' => true,
                'approved' => true,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $users[2]->id,
                'title' => 'Prime Commercial Office Space',
                'tagline' => 'Excellent business location',
                'category' => 'lease',
                'property_type' => 'commercial',
                'country' => 'United Kingdom',
                'city' => 'London',
                'address' => '100 Bishopsgate, London EC2N 4AG',
                'latitude' => 51.5155,
                'longitude' => -0.0756,
                'price' => 8500.00,
                'currency' => 'GBP',
                'negotiable' => false,
                'deposit' => 17000.00,
                'service_charges' => 500.00,
                'maintenance_fees' => 300.00,
                'cover_image' => 'properties/cover/office1.jpg',
                'additional_images' => json_encode([
                    'properties/additional/office1_1.jpg',
                    'properties/additional/office1_2.jpg'
                ]),
                'video_tour_link' => null,
                'description' => 'Modern commercial office space in the heart of London\'s financial district. This premium location offers excellent connectivity, modern facilities, and prestigious address for your business.',
                'specifications' => json_encode([
                    'floor_area' => 2500,
                    'floor' => 12,
                    'meeting_rooms' => 4,
                    'parking_spaces' => 10,
                    'elevator_access' => true,
                    'year_renovated' => 2021
                ]),
                'amenities' => json_encode([
                    'Reception', 'Meeting Rooms', 'Kitchen', 'High-Speed Internet', 'Security', 'Air Conditioning', 'Disabled Access'
                ]),
                'location_highlights' => json_encode([
                    'Liverpool Street Station nearby',
                    'Bank of England',
                    'Multiple restaurants and cafes',
                    'Premium business address'
                ]),
                'transport_links' => json_encode([
                    'Liverpool Street Station (2 min walk)',
                    'Bank Station (5 min walk)',
                    'Multiple bus routes',
                    'Easy access to airports'
                ]),
                'seller_name' => 'Michael Brown',
                'seller_company' => 'London Commercial Properties',
                'seller_phone' => '+44 20 7123 4567',
                'seller_email' => 'michael@londoncommercial.co.uk',
                'seller_website' => 'https://londoncommercial.co.uk',
                'seller_logo' => 'properties/logos/london_commercial.jpg',
                'verified_agent' => false,
                'advert_type' => 'promoted',
                'promoted_until' => Carbon::now()->addDays(7),
                'views' => 128,
                'saves' => 8,
                'enquiries' => 4,
                'active' => true,
                'approved' => true,
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $users[3]->id,
                'title' => 'Luxury Beachfront Villa',
                'tagline' => 'Ocean views and private beach access',
                'category' => 'buy',
                'property_type' => 'luxury',
                'country' => 'United Arab Emirates',
                'city' => 'Dubai',
                'address' => 'Palm Jumeirah, Dubai',
                'latitude' => 25.0767,
                'longitude' => 55.1343,
                'price' => 5500000.00,
                'currency' => 'AED',
                'negotiable' => false,
                'deposit' => null,
                'service_charges' => 15000.00,
                'maintenance_fees' => 8000.00,
                'cover_image' => 'properties/cover/villa1.jpg',
                'additional_images' => json_encode([
                    'properties/additional/villa1_1.jpg',
                    'properties/additional/villa1_2.jpg',
                    'properties/additional/villa1_3.jpg',
                    'properties/additional/villa1_4.jpg',
                    'properties/additional/villa1_5.jpg'
                ]),
                'video_tour_link' => 'https://www.youtube.com/watch?v=example2',
                'description' => 'Ultra-luxury beachfront villa offering unparalleled ocean views and direct beach access. This magnificent property features world-class amenities, private pool, and the ultimate in luxury living.',
                'specifications' => json_encode([
                    'bedrooms' => 6,
                    'bathrooms' => 7,
                    'square_meters' => 850,
                    'lot_size' => 1200,
                    'pool_size' => 'Large',
                    'staff_quarters' => true,
                    'year_built' => 2022
                ]),
                'amenities' => json_encode([
                    'Private Beach', 'Infinity Pool', 'Home Cinema', 'Wine Cellar', 'Gym', 'Spa', 'Smart Home', 'Maid Room'
                ]),
                'location_highlights' => json_encode([
                    'Private beach access',
                    'Palm Jumeirah location',
                    'Near Atlantis Hotel',
                    'Dubai Marina nearby'
                ]),
                'transport_links' => json_encode([
                    '15 minutes to Dubai Mall',
                    '25 minutes to Dubai Airport',
                    'Helipad available',
                    'Yacht access'
                ]),
                'seller_name' => 'Ahmed Al Maktoum',
                'seller_company' => 'Dubai Luxury Estates',
                'seller_phone' => '+971 4 123 4567',
                'seller_email' => 'ahmed@dubailuxury.ae',
                'seller_website' => 'https://dubailuxury.ae',
                'seller_logo' => 'properties/logos/dubai_luxury.jpg',
                'verified_agent' => true,
                'advert_type' => 'sponsored',
                'sponsored_until' => Carbon::now()->addDays(60),
                'views' => 892,
                'saves' => 67,
                'enquiries' => 23,
                'active' => true,
                'approved' => true,
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'user_id' => $users[4]->id,
                'title' => 'Development Land Plot',
                'tagline' => 'Perfect for commercial development',
                'category' => 'buy',
                'property_type' => 'land',
                'country' => 'Canada',
                'city' => 'Toronto',
                'address' => 'Development Area, North York, Toronto',
                'latitude' => 43.7615,
                'longitude' => -79.4111,
                'price' => 2500000.00,
                'currency' => 'CAD',
                'negotiable' => true,
                'deposit' => null,
                'service_charges' => null,
                'maintenance_fees' => null,
                'cover_image' => 'properties/cover/land1.jpg',
                'additional_images' => json_encode([
                    'properties/additional/land1_1.jpg',
                    'properties/additional/land1_2.jpg'
                ]),
                'video_tour_link' => null,
                'description' => 'Prime development land in the rapidly growing North York area. This plot offers excellent potential for commercial or residential development with easy access to major transportation routes.',
                'specifications' => json_encode([
                    'land_size_acres' => 5.2,
                    'zoning' => 'Commercial/Residential Mixed',
                    'road_access' => true,
                    'utilities_available' => true,
                    'frontage_feet' => 450
                ]),
                'amenities' => json_encode([
                    'Road Access', 'Utilities Available', 'Zoning Approved', 'Surveyed'
                ]),
                'location_highlights' => json_encode([
                    'High growth area',
                    'Near major highways',
                    'Close to subway line',
                    'Development incentives'
                ]),
                'transport_links' => json_encode([
                    'Highway 401 nearby',
                    'Subway extension planned',
                    'Bus routes available'
                ]),
                'seller_name' => 'Robert Chen',
                'seller_company' => 'Toronto Development Group',
                'seller_phone' => '+1 416 555 0123',
                'seller_email' => 'robert@torontodev.ca',
                'seller_website' => 'https://torontodev.ca',
                'seller_logo' => 'properties/logos/toronto_dev.jpg',
                'verified_agent' => true,
                'advert_type' => 'standard',
                'views' => 67,
                'saves' => 12,
                'enquiries' => 5,
                'active' => true,
                'approved' => true,
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(8),
            ]
        ];

        foreach ($properties as $property) {
            DB::table('properties')->insert($property);
        }

        // Create Property Analytics
        $analytics = [
            [
                'property_id' => 1,
                'event_type' => 'view',
                'user_id' => $users[1]->id,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => json_encode(['source' => 'search_results', 'duration' => 45]),
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'property_id' => 1,
                'event_type' => 'enquiry',
                'user_id' => $users[2]->id,
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'metadata' => json_encode(['message_type' => 'property_question', 'response_time' => 120]),
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'property_id' => 2,
                'event_type' => 'save',
                'user_id' => $users[1]->id,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'metadata' => json_encode(['saved_from' => 'property_details']),
                'created_at' => Carbon::now()->subHours(5),
            ],
            [
                'property_id' => 2,
                'event_type' => 'contact',
                'user_id' => $users[3]->id,
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15',
                'metadata' => json_encode(['contact_method' => 'phone', 'call_duration' => 300]),
                'created_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($analytics as $analytic) {
            DB::table('property_analytics')->insert($analytic);
        }

        // Create Property Favourites
        $favourites = [
            ['property_id' => 1, 'user_id' => $users[1]->id],
            ['property_id' => 1, 'user_id' => $users[4]->id],
            ['property_id' => 2, 'user_id' => $users[1]->id],
            ['property_id' => 2, 'user_id' => $users[2]->id],
            ['property_id' => 2, 'user_id' => $users[3]->id],
            ['property_id' => 3, 'user_id' => $users[4]->id],
            ['property_id' => 3, 'user_id' => $users[0]->id],
            ['property_id' => 4, 'user_id' => $users[2]->id],
            ['property_id' => 4, 'user_id' => $users[3]->id],
            ['property_id' => 4, 'user_id' => $users[4]->id],
        ];

        foreach ($favourites as $favourite) {
            DB::table('property_favourites')->insert(array_merge($favourite, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Property Enquiries
        $enquiries = [
            [
                'property_id' => 1,
                'user_id' => $users[1]->id,
                'name' => 'Alice Wilson',
                'email' => 'alice@example.com',
                'phone' => '+1 555-0101',
                'message' => 'I am interested in this apartment. Can you provide more information about the lease terms?',
                'type' => 'general',
                'contacted' => true,
                'contacted_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'property_id' => 2,
                'user_id' => null,
                'name' => 'Bob Martinez',
                'email' => 'bob@example.com',
                'phone' => '+1 555-0102',
                'message' => 'I would like to schedule a viewing of this property.',
                'type' => 'schedule_viewing',
                'contacted' => false,
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'property_id' => 3,
                'user_id' => $users[3]->id,
                'name' => 'Carol Davis',
                'email' => 'carol@example.com',
                'phone' => '+44 20 7123 0101',
                'message' => 'What are the service charges and are there any additional fees?',
                'type' => 'price_inquiry',
                'contacted' => true,
                'contacted_at' => Carbon::now()->subMinutes(30),
                'created_at' => Carbon::now()->subHours(2),
            ],
        ];

        foreach ($enquiries as $enquiry) {
            DB::table('property_enquiries')->insert($enquiry);
        }

        $this->command->info('Property seeder completed successfully!');
        $this->command->info('Created ' . count($properties) . ' properties with analytics and interactions.');
    }
}
