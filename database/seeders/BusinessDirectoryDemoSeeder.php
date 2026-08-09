<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Business directory demos — includes live restaurant (RecipesBible) + automotive (Car Services)
 * examples with category-specific profile JSON (hours, booking, menu/services).
 * Safe to re-run (upserts by slug).
 */
class BusinessDirectoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        $john = Customer::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password_hash' => Hash::make('password123'),
                'phone' => '+447700900001',
                'email_verified_at' => now(),
                'customer_uid' => Str::random(10),
            ]
        );

        $vikas = Customer::firstOrCreate(
            ['email' => 'vikas@worldwideadverts.info'],
            [
                'first_name' => 'Vikas',
                'last_name' => 'Admin',
                'password_hash' => Hash::make('Admin@123'),
                'phone' => '+447700900002',
                'email_verified_at' => now(),
                'user_type' => 'business',
                'customer_uid' => Str::random(10),
            ]
        );

        $categoryId = function (string $slug) {
            return Category::where('slug', $slug)->value('category_id')
                ?? Category::where('slug', 'like', '%' . explode('-', $slug)[0] . '%')->value('category_id');
        };

        $posts = [
            [
                'customer_id' => $john->customer_id,
                'slug' => 'doe-digital-retail-hub',
                'business_name' => 'Doe Digital Retail Hub',
                'business_description' => 'Online and high-street retail partner for worldwide brands — sourcing, store ops and marketplace fulfilment.',
                'business_phone_number' => '+44 20 7946 0101',
                'business_address' => '12 Market Street, London, UK',
                'city' => 'London',
                'country' => 'United Kingdom',
                'business_email' => 'hello@doeretail.example.com',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'John Doe',
                'business_category_slug' => 'retail',
                'category_id' => $categoryId('retail-shopping'),
                'status' => 'active',
                'category_profile' => [
                    'opening_hours' => [
                        'monday' => '09:00 – 18:00',
                        'tuesday' => '09:00 – 18:00',
                        'wednesday' => '09:00 – 18:00',
                        'thursday' => '09:00 – 18:00',
                        'friday' => '09:00 – 19:00',
                        'saturday' => '10:00 – 17:00',
                        'sunday' => 'Closed',
                    ],
                    'booking_slots' => ['Click & collect', 'Store visit'],
                    'highlights' => ['In-store & click-and-collect', 'Trade accounts welcome'],
                ],
            ],
            [
                'customer_id' => $john->customer_id,
                'slug' => 'recipesbible-kitchen-house',
                'business_name' => 'RecipesBible Kitchen House',
                'business_description' => 'Featured restaurant from the RecipesBible food platform — seasonal tasting menus, chef-led dining, and marketplace-ready hospitality. Inspired by the Featured Restaurant spotlight on recipesbible.com.',
                'business_phone_number' => '+44 20 7946 0182',
                'business_address' => '48 Borough Market Row, London SE1 9AQ, United Kingdom',
                'city' => 'London',
                'country' => 'United Kingdom',
                'business_email' => 'bookings@recipesbible-kitchen.example',
                'business_website' => 'https://recipesbible.com',
                'booking_url' => 'https://recipesbible.com',
                'business_owner' => 'Kitchen House Team',
                'business_category_slug' => 'restaurants',
                'category_id' => $categoryId('restaurants-food'),
                'business_logo' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=400&q=80',
                'cover_image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1200&q=80',
                'status' => 'active',
                'category_profile' => [
                    'cuisine' => ['Modern British', 'Seasonal', 'Seafood'],
                    'price_range' => '£££',
                    'seating_capacity' => 86,
                    'outdoor_seating' => true,
                    'delivery' => true,
                    'takeaway' => true,
                    'reservations_required' => true,
                    'booking_url' => 'https://recipesbible.com',
                    'booking_phone' => '+44 20 7946 0182',
                    'dietary' => ['Vegetarian', 'Vegan options', 'Gluten-free on request'],
                    'highlights' => [
                        'Chef spotlight evenings',
                        'Market-fresh seasonal plates',
                        'Private dining for 12',
                        'RecipesBible partner kitchen',
                    ],
                    'menu_samples' => [
                        ['name' => 'Snail Spring Rolls', 'note' => 'Crispy seafood appetizer', 'price' => '£12'],
                        ['name' => 'Sinigang Salmon Belly', 'note' => 'Tamarind broth', 'price' => '£24'],
                        ['name' => 'Chef tasting menu', 'note' => '5 courses', 'price' => '£65'],
                    ],
                    'opening_hours' => [
                        'monday' => '12:00 – 22:00',
                        'tuesday' => '12:00 – 22:00',
                        'wednesday' => '12:00 – 22:00',
                        'thursday' => '12:00 – 23:00',
                        'friday' => '12:00 – 23:30',
                        'saturday' => '11:00 – 23:30',
                        'sunday' => '11:00 – 21:00',
                    ],
                    'booking_slots' => [
                        'Lunch 12:00–14:30',
                        'Early dinner 17:30–19:00',
                        'Prime dinner 19:00–21:30',
                    ],
                ],
            ],
            [
                'customer_id' => $vikas->customer_id,
                'slug' => 'carservices-elite-garage',
                'business_name' => 'CarServices Elite Garage',
                'business_description' => 'Full-service automotive garage for MOT, servicing, diagnostics and repairs — Car Services style profile for the Business Automotive category (not a generic tech listing).',
                'business_phone_number' => '+44 121 496 0288',
                'business_address' => '17 Industrial Way, Aston, Birmingham B6 7RT, United Kingdom',
                'city' => 'Birmingham',
                'country' => 'United Kingdom',
                'business_email' => 'bookings@carservices-elite.example',
                'business_website' => 'https://www.carservices.com',
                'booking_url' => 'https://www.carservices.com',
                'business_owner' => 'Workshop Manager',
                'business_category_slug' => 'automotive',
                'category_id' => $categoryId('automotive'),
                'business_logo' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&w=400&q=80',
                'cover_image' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=80',
                'status' => 'active',
                'category_profile' => [
                    'services' => [
                        'MOT testing',
                        'Full / interim service',
                        'Diagnostics & ECU',
                        'Brakes & suspension',
                        'Tyres & alignment',
                        'Air conditioning recharge',
                        'Courtesy car available',
                    ],
                    'makes_serviced' => ['All makes', 'BMW', 'Mercedes', 'Ford', 'Vauxhall', 'Toyota', 'VW'],
                    'warranties' => '12-month parts & labour on most repairs',
                    'emergency_tow' => true,
                    'tow_phone' => '+44 121 496 0299',
                    'booking_url' => 'https://www.carservices.com',
                    'booking_phone' => '+44 121 496 0288',
                    'opening_hours' => [
                        'monday' => '08:00 – 18:00',
                        'tuesday' => '08:00 – 18:00',
                        'wednesday' => '08:00 – 18:00',
                        'thursday' => '08:00 – 18:00',
                        'friday' => '08:00 – 18:00',
                        'saturday' => '08:00 – 13:00',
                        'sunday' => 'Closed',
                    ],
                    'booking_slots' => [
                        'Drop-off from 08:00',
                        'Morning bay 09:00–12:00',
                        'Afternoon bay 13:00–17:00',
                        'Saturday MOT slots (book ahead)',
                    ],
                    'highlights' => [
                        'Class 4 & 7 MOT',
                        'Manufacturer-spec servicing',
                        'Online booking',
                        'Live job updates by SMS',
                    ],
                ],
            ],
            [
                'customer_id' => $john->customer_id,
                'slug' => 'john-doe-professional-services',
                'business_name' => 'John Doe Professional Services',
                'business_description' => 'Consulting, bookkeeping support and business setup services for startups and SMEs.',
                'business_phone_number' => '+44 20 7946 0102',
                'business_address' => '88 Commerce Road, Manchester, UK',
                'city' => 'Manchester',
                'country' => 'United Kingdom',
                'business_email' => 'services@johndoe.example.com',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'John Doe',
                'business_category_slug' => 'services',
                'category_id' => $categoryId('professional-services'),
                'status' => 'active',
                'category_profile' => [
                    'opening_hours' => [
                        'monday' => '09:00 – 17:30',
                        'tuesday' => '09:00 – 17:30',
                        'wednesday' => '09:00 – 17:30',
                        'thursday' => '09:00 – 17:30',
                        'friday' => '09:00 – 17:00',
                        'saturday' => 'Closed',
                        'sunday' => 'Closed',
                    ],
                    'booking_slots' => ['Consultation call', 'On-site visit'],
                    'highlights' => ['Free initial consultation'],
                ],
            ],
            [
                'customer_id' => $vikas->customer_id,
                'slug' => 'vikas-global-tech-solutions',
                'business_name' => 'Vikas Global Tech Solutions',
                'business_description' => 'IT services, software consulting and digital transformation for growing companies worldwide.',
                'business_phone_number' => '+44 20 7946 2200',
                'business_address' => '200 Innovation Way, Birmingham, UK',
                'city' => 'Birmingham',
                'country' => 'United Kingdom',
                'business_email' => 'vikas@worldwideadverts.info',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'Vikas',
                'business_category_slug' => 'technology',
                'category_id' => $categoryId('technology-electronics'),
                'status' => 'active',
                'category_profile' => [
                    'opening_hours' => [
                        'monday' => '09:00 – 18:00',
                        'tuesday' => '09:00 – 18:00',
                        'wednesday' => '09:00 – 18:00',
                        'thursday' => '09:00 – 18:00',
                        'friday' => '09:00 – 17:00',
                        'saturday' => 'Closed',
                        'sunday' => 'Closed',
                    ],
                    'booking_slots' => ['Support tickets & demos', 'Consultation call'],
                    'highlights' => ['Enterprise & SME packs'],
                ],
            ],
            [
                'customer_id' => $vikas->customer_id,
                'slug' => 'vikas-healthcare-wellness-group',
                'business_name' => 'Vikas Healthcare & Wellness Group',
                'business_description' => 'Clinics, wellness centres and health-service partners listed for global clients.',
                'business_phone_number' => '+44 20 7946 2201',
                'business_address' => '45 Care Avenue, Leeds, UK',
                'city' => 'Leeds',
                'country' => 'United Kingdom',
                'business_email' => 'vikas.health@worldwideadverts.info',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'Vikas',
                'business_category_slug' => 'healthcare',
                'category_id' => $categoryId('healthcare-wellness'),
                'status' => 'active',
                'category_profile' => [
                    'opening_hours' => [
                        'monday' => '08:00 – 18:00',
                        'tuesday' => '08:00 – 18:00',
                        'wednesday' => '08:00 – 18:00',
                        'thursday' => '08:00 – 18:00',
                        'friday' => '08:00 – 17:00',
                        'saturday' => '09:00 – 13:00',
                        'sunday' => 'Closed',
                    ],
                    'booking_slots' => ['Morning appointment', 'Afternoon appointment'],
                    'specialties' => ['General practice', 'Wellness'],
                    'highlights' => ['Appointments available', 'Patient-first care'],
                ],
            ],
            [
                'customer_id' => $vikas->customer_id,
                'slug' => 'vikas-education-training-academy',
                'business_name' => 'Vikas Education & Training Academy',
                'business_description' => 'Corporate training, courses and skills programmes for teams and individuals.',
                'business_phone_number' => '+44 20 7946 2202',
                'business_address' => '9 Learning Lane, Bristol, UK',
                'city' => 'Bristol',
                'country' => 'United Kingdom',
                'business_email' => 'vikas.learn@worldwideadverts.info',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'Vikas',
                'business_category_slug' => 'education',
                'category_id' => $categoryId('education-training'),
                'status' => 'active',
                'category_profile' => [
                    'opening_hours' => [
                        'monday' => '09:00 – 17:00',
                        'tuesday' => '09:00 – 17:00',
                        'wednesday' => '09:00 – 17:00',
                        'thursday' => '09:00 – 17:00',
                        'friday' => '09:00 – 16:00',
                        'saturday' => 'Closed',
                        'sunday' => 'Closed',
                    ],
                    'booking_slots' => ['Enrolment open', 'Course advice'],
                    'courses' => ['Leadership', 'Digital skills'],
                    'highlights' => ['Enrolment open', 'Course advice available'],
                ],
            ],
        ];

        foreach ($posts as $post) {
            CustomerBusiness::updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }

        // Soft-clean obvious faker junk + wrong tech-as-automotive placeholders
        CustomerBusiness::where('customer_id', $john->customer_id)
            ->whereIn('business_name', ['Kaseem Torres', 'Kieran Salazar', 'Jamal Kinney'])
            ->update(['status' => 'inactive']);

        CustomerBusiness::where('business_category_slug', 'automotive')
            ->where(function ($q) {
                $q->where('business_name', 'like', '%Tech Solution%')
                    ->orWhere('business_name', 'like', '%IT Consulting%')
                    ->orWhere('business_description', 'like', '%software development%');
            })
            ->update(['status' => 'inactive']);

        $this->command?->info('Business directory demos ready (incl. RecipesBible restaurant + CarServices automotive).');
    }
}
