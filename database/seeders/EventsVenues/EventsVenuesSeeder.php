<?php

namespace Database\Seeders\EventsVenues;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Venue;
use App\Models\VenueService;

class EventsVenuesSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a test user
        $user = User::firstOrCreate(
            ['email' => 'events@example.com'],
            [
                'name' => 'Events Demo User',
                'password' => bcrypt('password'),
            ]
        );

        // Create sample venues
        $venues = $this->createSampleVenues($user);
        
        // Create sample events
        $this->createSampleEvents($user, $venues);
        
        // Create sample venue services
        $this->createSampleVenueServices($user);
    }

    private function createSampleVenues(User $user): array
    {
        $venues = [];

        $venueData = [
            [
                'name' => 'Grand Ballroom Plaza',
                'venue_type' => 'wedding_hall',
                'country' => 'United States',
                'city' => 'New York',
                'capacity' => 500,
                'min_price' => 5000,
                'max_price' => 15000,
                'description' => 'Luxurious ballroom perfect for weddings and corporate events. Features crystal chandeliers, marble floors, and state-of-the-art lighting systems.',
                'amenities' => ['wi_fi', 'parking', 'catering', 'av_equipment', 'air_conditioning', 'sound_system', 'bar', 'restrooms'],
                'indoor' => true,
                'outdoor' => false,
                'catering_available' => true,
                'parking_available' => true,
                'accessibility' => true,
                'opening_hours' => 'Mon-Sun: 8AM-11PM',
                'contact_email' => 'info@grandballroom.com',
                'social_links' => ['https://facebook.com/grandballroom', 'https://instagram.com/grandballroom'],
                'images' => [
                    'https://images.unsplash.com/photo-1469371670287-ac92ce23df18?w=800',
                    'https://images.unsplash.com/photo-1519167758201-4175745b7622?w=800',
                ],
                'video_link' => 'https://www.youtube.com/watch?v=example',
                'promotion_tier' => 'featured',
                'is_active' => true,
            ],
            [
                'name' => 'Tech Conference Center',
                'venue_type' => 'conference_centre',
                'country' => 'United States',
                'city' => 'San Francisco',
                'capacity' => 1000,
                'min_price' => 3000,
                'max_price' => 8000,
                'description' => 'Modern conference facility with cutting-edge technology. Perfect for tech conferences, seminars, and corporate meetings.',
                'amenities' => ['wi_fi', 'parking', 'av_equipment', 'air_conditioning', 'sound_system', 'lighting', 'stage', 'elevator'],
                'indoor' => true,
                'outdoor' => false,
                'catering_available' => false,
                'parking_available' => true,
                'accessibility' => true,
                'opening_hours' => 'Mon-Fri: 7AM-10PM, Sat-Sun: 8AM-8PM',
                'contact_email' => 'bookings@techconfcenter.com',
                'social_links' => ['https://twitter.com/techconfcenter'],
                'images' => [
                    'https://images.unsplash.com/photo-1497366214041-512025aae3b4?w=800',
                    'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800',
                ],
                'promotion_tier' => 'sponsored',
                'is_active' => true,
            ],
            [
                'name' => 'Sunset Garden Venue',
                'venue_type' => 'outdoor_space',
                'country' => 'United States',
                'city' => 'Los Angeles',
                'capacity' => 300,
                'min_price' => 2000,
                'max_price' => 6000,
                'description' => 'Beautiful outdoor garden venue with stunning sunset views. Ideal for romantic weddings and intimate gatherings.',
                'amenities' => ['parking', 'catering', 'lighting', 'restrooms', 'security'],
                'indoor' => false,
                'outdoor' => true,
                'catering_available' => true,
                'parking_available' => true,
                'accessibility' => false,
                'opening_hours' => 'Mon-Sun: 9AM-10PM',
                'contact_email' => 'events@sunsetgarden.com',
                'social_links' => ['https://instagram.com/sunsetgarden'],
                'images' => [
                    'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800',
                    'https://images.unsplash.com/photo-1475924156734-496f6cac6ec1?w=800',
                ],
                'promotion_tier' => 'promoted',
                'is_active' => true,
            ],
            [
                'name' => 'The Jazz Club',
                'venue_type' => 'bar_restaurant',
                'country' => 'United States',
                'city' => 'New Orleans',
                'capacity' => 150,
                'min_price' => 1000,
                'max_price' => 3000,
                'description' => 'Intimate jazz club with authentic New Orleans atmosphere. Perfect for private parties and live music events.',
                'amenities' => ['bar', 'sound_system', 'air_conditioning', 'restrooms'],
                'indoor' => true,
                'outdoor' => false,
                'catering_available' => true,
                'parking_available' => false,
                'accessibility' => false,
                'opening_hours' => 'Tue-Sun: 6PM-2AM',
                'contact_email' => 'bookings@jazzclub.com',
                'social_links' => ['https://facebook.com/jazzclub'],
                'images' => [
                    'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800',
                    'https://images.unsplash.com/photo-1493220457104-ef3db64d69b5?w=800',
                ],
                'promotion_tier' => 'standard',
                'is_active' => true,
            ],
            [
                'name' => 'Sports Complex Arena',
                'venue_type' => 'sports_venue',
                'country' => 'United States',
                'city' => 'Chicago',
                'capacity' => 2000,
                'min_price' => 4000,
                'max_price' => 12000,
                'description' => 'Multi-purpose sports arena suitable for concerts, sports events, and large exhibitions.',
                'amenities' => ['parking', 'catering', 'sound_system', 'lighting', 'stage', 'security', 'elevator'],
                'indoor' => true,
                'outdoor' => true,
                'catering_available' => true,
                'parking_available' => true,
                'accessibility' => true,
                'opening_hours' => 'Mon-Sun: 7AM-11PM',
                'contact_email' => 'events@sportsarena.com',
                'social_links' => ['https://twitter.com/sportsarena'],
                'images' => [
                    'https://images.unsplash.com/photo-1541252240753-6a8d87108f55?w=800',
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800',
                ],
                'promotion_tier' => 'featured',
                'is_active' => true,
            ],
        ];

        foreach ($venueData as $data) {
            $data['user_id'] = $user->id;
            $venues[] = Venue::create($data);
        }

        return $venues;
    }

    private function createSampleEvents(User $user, array $venues): void
    {
        $eventData = [
            [
                'title' => 'Annual Tech Summit 2024',
                'category' => 'conference',
                'date_time' => now()->addDays(30),
                'country' => 'United States',
                'city' => 'San Francisco',
                'venue_name' => 'Tech Conference Center',
                'ticket_price' => 299.99,
                'price_type' => 'paid',
                'description' => 'Join industry leaders and innovators for the biggest tech summit of the year. Featuring keynote speakers, workshops, and networking opportunities.',
                'schedule' => '9AM: Registration\n10AM: Keynote\n12PM: Lunch\n2PM: Workshops\n6PM: Networking Dinner',
                'age_restrictions' => '18+',
                'dress_code' => 'Business Casual',
                'expected_attendance' => 800,
                'ticket_link' => 'https://eventbrite.com/e/techsummit2024',
                'contact_email' => 'info@techsummit.com',
                'social_links' => ['https://twitter.com/techsummit', 'https://linkedin.com/company/techsummit'],
                'images' => [
                    'https://images.unsplash.com/photo-1540575609-7bd2f7388b92?w=800',
                    'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800',
                ],
                'video_link' => 'https://www.youtube.com/watch?v=techsummit',
                'promotion_tier' => 'sponsored',
                'is_active' => true,
                'venue_id' => $venues[1]->id, // Tech Conference Center
            ],
            [
                'title' => 'Summer Music Festival',
                'category' => 'concert',
                'date_time' => now()->addDays(45),
                'country' => 'United States',
                'city' => 'Los Angeles',
                'venue_name' => 'Sunset Garden Venue',
                'ticket_price' => 89.99,
                'price_type' => 'paid',
                'description' => 'Experience an unforgettable evening of live music featuring top artists from around the world. Food trucks and craft beer available.',
                'schedule' => '2PM: Gates Open\n3PM: Opening Acts\n6PM: Main Performers\n10PM: Headliner\n12AM: Close',
                'age_restrictions' => '21+',
                'dress_code' => 'Festival Casual',
                'expected_attendance' => 250,
                'ticket_link' => 'https://summerfest2024.com/tickets',
                'contact_email' => 'info@summerfest2024.com',
                'social_links' => ['https://instagram.com/summerfest2024'],
                'images' => [
                    'https://images.unsplash.com/photo-1459749411177-4fde5e8c023e?w=800',
                    'https://images.unsplash.com/photo-1470225620780-dba8ba27b2df?w=800',
                ],
                'promotion_tier' => 'featured',
                'is_active' => true,
                'venue_id' => $venues[2]->id, // Sunset Garden Venue
            ],
            [
                'title' => 'Charity Gala Dinner',
                'category' => 'charity',
                'date_time' => now()->addDays(20),
                'country' => 'United States',
                'city' => 'New York',
                'venue_name' => 'Grand Ballroom Plaza',
                'ticket_price' => 0,
                'price_type' => 'donation',
                'description' => 'Elegant charity gala dinner supporting local education initiatives. Three-course meal, live auction, and entertainment.',
                'schedule' => '6PM: Cocktails\n7PM: Dinner\n8PM: Auction\n9PM: Dancing\n11PM: Close',
                'age_restrictions' => '21+',
                'dress_code' => 'Black Tie',
                'expected_attendance' => 400,
                'ticket_link' => 'https://charitygala.org/donate',
                'contact_email' => 'events@charitygala.org',
                'social_links' => ['https://facebook.com/charitygala'],
                'images' => [
                    'https://images.unsplash.com/photo-1519167758201-4175745b7622?w=800',
                    'https://images.unsplash.com/photo-1469371670287-ac92ce23df18?w=800',
                ],
                'promotion_tier' => 'promoted',
                'is_active' => true,
                'venue_id' => $venues[0]->id, // Grand Ballroom Plaza
            ],
            [
                'title' => 'Jazz Night Live',
                'category' => 'party',
                'date_time' => now()->addDays(10),
                'country' => 'United States',
                'city' => 'New Orleans',
                'venue_name' => 'The Jazz Club',
                'ticket_price' => 25.00,
                'price_type' => 'paid',
                'description' => 'An intimate evening of authentic New Orleans jazz. Featuring local musicians and special guests.',
                'schedule' => '8PM: Doors Open\n9PM: First Set\n10:30PM: Second Set\n12AM: Late Night Jam',
                'age_restrictions' => '21+',
                'dress_code' => 'Smart Casual',
                'expected_attendance' => 120,
                'ticket_link' => 'https://jazzclub.com/tickets',
                'contact_email' => 'bookings@jazzclub.com',
                'social_links' => ['https://facebook.com/jazzclub'],
                'images' => [
                    'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800',
                    'https://images.unsplash.com/photo-1493220457104-ef3db64d69b5?w=800',
                ],
                'promotion_tier' => 'standard',
                'is_active' => true,
                'venue_id' => $venues[3]->id, // The Jazz Club
            ],
            [
                'title' => 'Business Networking Breakfast',
                'category' => 'conference',
                'date_time' => now()->addDays(7),
                'country' => 'United States',
                'city' => 'Chicago',
                'venue_name' => 'Sports Complex Arena',
                'ticket_price' => 0,
                'price_type' => 'free',
                'description' => 'Monthly business networking breakfast with guest speakers and opportunities to connect with local entrepreneurs.',
                'schedule' => '7:30AM: Coffee & Networking\n8AM: Breakfast\n8:30AM: Guest Speaker\n9AM: Open Networking\n9:30AM: Close',
                'age_restrictions' => '',
                'dress_code' => 'Business Casual',
                'expected_attendance' => 150,
                'ticket_link' => '',
                'contact_email' => 'networking@businesschicago.com',
                'social_links' => ['https://linkedin.com/company/businesschicago'],
                'images' => [
                    'https://images.unsplash.com/photo-1517041899536-9a241eaceb3d?w=800',
                    'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800',
                ],
                'promotion_tier' => 'standard',
                'is_active' => true,
                'venue_id' => $venues[4]->id, // Sports Complex Arena
            ],
        ];

        foreach ($eventData as $data) {
            $data['user_id'] = $user->id;
            Event::create($data);
        }
    }

    private function createSampleVenueServices(User $user): void
    {
        $serviceData = [
            [
                'name' => 'Gourmet Catering Co.',
                'category' => 'catering',
                'country' => 'United States',
                'city' => 'New York',
                'min_price' => 25,
                'max_price' => 150,
                'description' => 'Premium catering service specializing in gourmet cuisine for events of all sizes. Custom menus available.',
                'packages_offered' => 'Silver Package: $25/person\nGold Package: $50/person\nPlatinum Package: $100/person',
                'availability' => 'Available 7 days a week',
                'contact_email' => 'info@gourmetcatering.com',
                'social_links' => ['https://instagram.com/gourmetcatering'],
                'images' => [
                    'https://images.unsplash.com/photo-1556656793-08538906a9f8?w=800',
                    'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=800',
                ],
                'video_link' => 'https://www.youtube.com/watch?v=cateringdemo',
                'promotion_tier' => 'featured',
                'is_active' => true,
            ],
            [
                'name' => 'Elegant Event Decor',
                'category' => 'decor',
                'country' => 'United States',
                'city' => 'Los Angeles',
                'min_price' => 1000,
                'max_price' => 5000,
                'description' => 'Professional event decoration and styling services. From intimate gatherings to grand celebrations.',
                'packages_offered' => 'Basic Decor: $1000\nPremium Decor: $2500\nLuxury Decor: $5000',
                'availability' => 'Available by appointment',
                'contact_email' => 'design@elegantdecor.com',
                'social_links' => ['https://pinterest.com/elegantdecor'],
                'images' => [
                    'https://images.unsplash.com/photo-1533105079780-92b9d4889f5b?w=800',
                    'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800',
                ],
                'promotion_tier' => 'promoted',
                'is_active' => true,
            ],
            [
                'name' => 'DJ Master Mix',
                'category' => 'dj',
                'country' => 'United States',
                'city' => 'Chicago',
                'min_price' => 500,
                'max_price' => 2000,
                'description' => 'Professional DJ services for all types of events. Extensive music library and state-of-the-art equipment.',
                'packages_offered' => '4 Hours: $500\n6 Hours: $750\n8 Hours: $1000',
                'availability' => 'Available Fri-Sun',
                'contact_email' => 'bookings@djmastermix.com',
                'social_links' => ['https://soundcloud.com/djmastermix'],
                'images' => [
                    'https://images.unsplash.com/photo-1470225620780-dba8ba27b2df?w=800',
                    'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800',
                ],
                'promotion_tier' => 'sponsored',
                'is_active' => true,
            ],
            [
                'name' => 'Capture Moments Photography',
                'category' => 'photography',
                'country' => 'United States',
                'city' => 'San Francisco',
                'min_price' => 800,
                'max_price' => 3000,
                'description' => 'Professional photography services for weddings, events, and corporate functions. Experienced photographers with artistic vision.',
                'packages_offered' => 'Basic Package: 4 hours, 200 photos\nPremium Package: 8 hours, 400 photos + video\nLuxury Package: Full day, unlimited photos + video + album',
                'availability' => 'Available 7 days a week',
                'contact_email' => 'info@capturemoments.com',
                'social_links' => ['https://instagram.com/capturemoments'],
                'images' => [
                    'https://images.unsplash.com/photo-1599687350236-43c864c9d2c1?w=800',
                    'https://images.unsplash.com/photo-1497366214041-512025aae3b4?w=800',
                ],
                'promotion_tier' => 'featured',
                'is_active' => true,
            ],
            [
                'name' => 'Secure Events Security',
                'category' => 'security',
                'country' => 'United States',
                'city' => 'New York',
                'min_price' => 50,
                'max_price' => 200,
                'description' => 'Professional security services for events and venues. Licensed and insured security personnel.',
                'packages_offered' => 'Basic Security: $50/hour\nPremium Security: $100/hour\nExecutive Protection: $200/hour',
                'availability' => 'Available 24/7',
                'contact_email' => 'security@secureevents.com',
                'social_links' => ['https://linkedin.com/company/secureevents'],
                'images' => [
                    'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800',
                    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
                ],
                'promotion_tier' => 'standard',
                'is_active' => true,
            ],
            [
                'name' => 'Perfect Event Planning',
                'category' => 'event_planning',
                'country' => 'United States',
                'city' => 'Los Angeles',
                'min_price' => 2000,
                'max_price' => 10000,
                'description' => 'Full-service event planning company. From concept to execution, we handle every detail of your special event.',
                'packages_offered' => 'Basic Planning: $2000\nFull Service Planning: $5000\nLuxury Planning: $10000',
                'availability' => 'Available by appointment',
                'contact_email' => 'hello@perfectevents.com',
                'social_links' => ['https://facebook.com/perfectevents'],
                'images' => [
                    'https://images.unsplash.com/photo-1519167758201-4175745b7622?w=800',
                    'https://images.unsplash.com/photo-1469371670287-ac92ce23df18?w=800',
                ],
                'promotion_tier' => 'promoted',
                'is_active' => true,
            ],
        ];

        foreach ($serviceData as $data) {
            $data['user_id'] = $user->id;
            VenueService::create($data);
        }
    }
}
