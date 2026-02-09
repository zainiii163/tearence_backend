<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Location;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AllCategoryPostsSeeder extends Seeder
{
    public function run(): void
    {
        // Get required data
        $customers = Customer::take(10)->get();
        $locations = Location::take(20)->get();
        $currency = Currency::where('code', 'USD')->first();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');
            return;
        }

        if ($currency === null) {
            $this->command->warn('No USD currency found. Please run CurrencySeeder first.');
            return;
        }

        // Get all categories including child categories
        $categories = Category::all();

        foreach ($categories as $category) {
            $this->createPostsForCategory($category, $customers, $locations, $currency);
        }

        $this->command->info('Sample posts created for all categories');
    }

    private function createPostsForCategory($category, $customers, $locations, $currency): void
    {
        $postsPerCategory = rand(3, 8);
        
        for ($i = 0; $i < $postsPerCategory; $i++) {
            $customer = $customers->random();
            $location = $locations->random();
            
            $listingData = $this->generateListingData($category, $customer, $location, $currency, $i);
            
            Listing::create($listingData);
        }
    }

    private function generateListingData($category, $customer, $location, $currency, $index): array
    {
        $baseData = [
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'status' => 'active',
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now()->subDays(rand(1, 30)),
            'post_type' => rand(0, 10) > 8 ? 'sponsored' : 'regular',
            'is_admin_post' => false,
            'is_harmful' => false,
            'created_at' => now()->subDays(rand(1, 60)),
            'updated_at' => now()->subDays(rand(0, 30)),
        ];

        // Generate specific data based on category
        switch ($category->slug) {
            case 'property':
            case 'houses-for-sale':
            case 'houses-for-rent':
            case 'commercial-property-for-sale':
            case 'commercial-property-for-rent':
                return $this->generatePropertyListing($baseData, $category, $index);
            
            case 'vehicles':
            case 'cars-for-sale':
            case 'vehicle-hire':
            case 'commercial-vehicles':
                return $this->generateVehicleListing($baseData, $category, $index);
            
            case 'events':
            case 'conferences':
            case 'social-events':
            case 'workshops':
                return $this->generateEventListing($baseData, $category, $index);
            
            case 'buy-and-sell':
            case 'items-for-sale':
            case 'items-for-swap':
            case 'free-items':
                return $this->generateBuySellListing($baseData, $category, $index);
            
            case 'books':
            case 'physical-books':
            case 'pdf-downloads':
            case 'audiobooks':
                return $this->generateBookListing($baseData, $category, $index);
            
            case 'funding':
            case 'business-investment':
            case 'partnerships':
            case 'startup-funding':
                return $this->generateFundingListing($baseData, $category, $index);
            
            case 'charities-and-donations':
            case 'humanitarian-causes':
            case 'medical-causes':
            case 'education-causes':
            case 'disaster-relief':
                return $this->generateDonationListing($baseData, $category, $index);
            
            case 'banner':
                return $this->generateBannerListing($baseData, $category, $index);
            
            case 'sponsored-ads':
                return $this->generateSponsoredAdListing($baseData, $category, $index);
            
            case 'jobs-and-vacancies':
            case 'full-time-jobs':
            case 'part-time-jobs':
            case 'contract-work':
            case 'freelance-opportunities':
                return $this->generateJobListing($baseData, $category, $index);
            
            case 'services':
            case 'digital-services':
            case 'local-services':
            case 'consulting':
            case 'creative-services':
                return $this->generateServiceListing($baseData, $category, $index);
            
            case 'business-and-stores':
                return $this->generateBusinessListing($baseData, $category, $index);
            
            case 'affiliate-programs':
                return $this->generateAffiliateListing($baseData, $category, $index);
            
            case 'hotel-resorts-travel':
            case 'hotels':
            case 'bandb':
            case 'transport-services':
            case 'tour-services':
                return $this->generateTravelListing($baseData, $category, $index);
            
            default:
                return $this->generateGenericListing($baseData, $category, $index);
        }
    }

    private function generatePropertyListing($baseData, $category, $index): array
    {
        $propertyTypes = ['residential', 'commercial'];
        $listingTypes = ['sale', 'rent'];
        $titles = [
            'Luxury Villa with Ocean View',
            'Modern Downtown Apartment',
            'Spacious Family Home',
            'Commercial Office Space',
            'Beachfront Property',
            'Mountain Retreat Cabin',
            'City Center Loft',
            'Suburban Family House'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Beautiful property located in prime location. Features modern amenities, spacious rooms, and excellent accessibility. Perfect for families or professionals. Property Type: ' . $propertyTypes[array_rand($propertyTypes)] . ', Listing Type: ' . $listingTypes[array_rand($listingTypes)] . ', Bedrooms: ' . rand(1, 6) . ', Bathrooms: ' . rand(1, 4) . ', Area: ' . rand(800, 5000) . ' sq ft.',
            'price' => rand(50000, 5000000),
            'type' => $listingTypes[array_rand($listingTypes)]
        ]);
    }

    private function generateVehicleListing($baseData, $category, $index): array
    {
        $vehicleTypes = ['car', 'motorcycle', 'truck', 'van'];
        $listingTypes = ['sale', 'hire', 'commercial'];
        $makes = ['Toyota', 'Honda', 'Ford', 'BMW', 'Mercedes', 'Tesla', 'Nissan', 'Hyundai'];
        $titles = [
            'Luxury Sedan - Excellent Condition',
            'Family SUV - Low Mileage',
            'Sports Car - Well Maintained',
            'Commercial Van - Ready for Work',
            'Electric Vehicle - Eco Friendly',
            'Motorcycle - Great for Commuting'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Well-maintained vehicle in excellent condition. Regular service history, clean interior, and runs perfectly. Great value for money. Vehicle Type: ' . $vehicleTypes[array_rand($vehicleTypes)] . ', Make: ' . $makes[array_rand($makes)] . ', Year: ' . rand(2015, 2024) . ', Mileage: ' . rand(5000, 150000) . ' miles.',
            'price' => rand(5000, 80000),
            'type' => $listingTypes[array_rand($listingTypes)]
        ]);
    }

    private function generateEventListing($baseData, $category, $index): array
    {
        $eventTypes = ['conference', 'social', 'workshop', 'other'];
        $titles = [
            'Annual Tech Conference 2024',
            'Business Networking Event',
            'Creative Writing Workshop',
            'Music Festival Weekend',
            'Startup Pitch Competition',
            'Professional Development Seminar'
        ];

        $eventDate = now()->addDays(rand(7, 90));
        
        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Join us for an amazing event filled with learning, networking, and opportunities. Perfect for professionals and enthusiasts alike. Event Type: ' . $eventTypes[array_rand($eventTypes)] . ', Date: ' . $eventDate->format('Y-m-d') . ', Time: ' . $eventDate->format('H:i') . ', Location: Convention Center, Organizer: Event Organizer Inc.',
            'price' => rand(25, 500),
            'type' => 'event'
        ]);
    }

    private function generateBuySellListing($baseData, $category, $index): array
    {
        $itemTypes = ['sale', 'swap', 'free'];
        $conditions = ['new', 'like_new', 'good', 'fair'];
        $titles = [
            'Electronics Bundle - Great Deal',
            'Furniture Set - Moving Sale',
            'Designer Clothes - Like New',
            'Kitchen Appliances - Excellent Condition',
            'Sports Equipment - Barely Used',
            'Home Decor Items - Modern Style'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'High-quality items in great condition. Selling due to relocation/upgrade. All items tested and working perfectly. Item Type: ' . $itemTypes[array_rand($itemTypes)] . ', Condition: ' . $conditions[array_rand($conditions)] . ', Category: General, Brand: Premium Brand.',
            'price' => rand(10, 1000),
            'type' => 'sale'
        ]);
    }

    private function generateBookListing($baseData, $category, $index): array
    {
        $genres = ['fiction', 'non_fiction', 'education', 'thriller', 'business', 'self_help'];
        $formats = ['physical', 'e_book', 'audiobook'];
        $titles = [
            'Bestselling Novel - Gripping Story',
            'Business Success Guide',
            'Self-Help Book - Life Changing',
            'Educational Textbook - Latest Edition',
            'Thriller Mystery - Page Turner',
            'Science Fiction Adventure'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Engaging and informative book in excellent condition. Perfect for readers looking for quality content and entertainment. Genre: ' . $genres[array_rand($genres)] . ', Author: Author Name, Format: ' . $formats[array_rand($formats)] . ', Condition: good.',
            'price' => rand(5, 50),
            'type' => 'sale'
        ]);
    }

    private function generateFundingListing($baseData, $category, $index): array
    {
        $fundingTypes = ['personal', 'business', 'community'];
        $titles = [
            'Innovative Tech Startup Seeking Investment',
            'Community Project - Local Development',
            'Creative Arts Project Funding',
            'Sustainable Business Initiative',
            'Educational Platform Development',
            'Healthcare Innovation Project'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Exciting opportunity with high potential returns. Well-researched project with experienced team and clear roadmap. Funding Type: ' . $fundingTypes[array_rand($fundingTypes)] . ', Goal: $' . rand(50000, 500000) . ', Current: $' . rand(5000, 100000) . ', Deadline: ' . now()->addDays(rand(30, 180))->format('Y-m-d') . ', Organizer: Project Organizer.',
            'price' => rand(10000, 1000000),
            'type' => 'funding'
        ]);
    }

    private function generateDonationListing($baseData, $category, $index): array
    {
        $charityTypes = ['humanitarian', 'medical', 'education', 'disaster'];
        $titles = [
            'Emergency Medical Fund',
            'Education for Underprivileged Children',
            'Disaster Relief Campaign',
            'Community Food Bank Support',
            'Clean Water Initiative',
            'Medical Equipment Fund'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Help make a difference in someone\'s life. Your donation will directly support those in need and create positive impact. Charity Type: ' . $charityTypes[array_rand($charityTypes)] . ', Goal: $' . rand(10000, 200000) . ', Current: $' . rand(1000, 50000) . ', Organizer: Charity Organization.',
            'price' => rand(1000, 100000),
            'type' => 'donation'
        ]);
    }

    private function generateBannerListing($baseData, $category, $index): array
    {
        $titles = [
            'Premium Banner Advertisement',
            'Website Header Banner',
            'Product Promotion Banner',
            'Event Announcement Banner',
            'Brand Awareness Campaign'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'High-visibility banner advertisement for maximum brand exposure and audience engagement. Banner Type: image, Target URL: https://example.com, Duration: ' . rand(7, 30) . ' days.',
            'price' => rand(500, 5000),
            'type' => 'advertisement'
        ]);
    }

    private function generateSponsoredAdListing($baseData, $category, $index): array
    {
        $adTypes = ['sponsored', 'featured', 'promoted'];
        $titles = [
            'Premium Sponsored Placement',
            'Featured Product Listing',
            'Promoted Service Offer',
            'Highlighted Business Profile',
            'Top Position Advertisement'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Enhanced visibility advertisement with premium placement and increased audience reach. Ad Type: ' . $adTypes[array_rand($adTypes)] . ', Target URL: https://example.com, Duration: ' . rand(14, 60) . ' days, Budget: $' . rand(500, 3000) . '.',
            'price' => rand(200, 2000),
            'type' => 'advertisement'
        ]);
    }

    private function generateJobListing($baseData, $category, $index): array
    {
        $jobTypes = ['full_time', 'part_time', 'contract', 'freelance'];
        $experienceLevels = ['entry', 'mid', 'senior', 'executive'];
        $titles = [
            'Senior Software Developer',
            'Marketing Manager Position',
            'Data Analyst Role',
            'Project Manager Opportunity',
            'UX Designer Position',
            'Business Development Manager'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Exciting career opportunity with competitive salary and benefits. Join our dynamic team and grow professionally. Job Type: ' . $jobTypes[array_rand($jobTypes)] . ', Experience Level: ' . $experienceLevels[array_rand($experienceLevels)] . ', Location: Remote/Hybrid Available, Company: Tech Company Inc.',
            'price' => rand(30000, 150000),
            'type' => 'job'
        ]);
    }

    private function generateServiceListing($baseData, $category, $index): array
    {
        $serviceTypes = ['digital', 'local', 'consulting', 'creative'];
        $priceTypes = ['fixed', 'hourly'];
        $titles = [
            'Professional Web Design Service',
            'Digital Marketing Expert',
            'Business Consulting Services',
            'Creative Design Solutions',
            'SEO Optimization Service',
            'Content Writing Professional'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Professional service provider with expertise and experience. High-quality work delivered on time with excellent results. Service Type: ' . $serviceTypes[array_rand($serviceTypes)] . ', Price Type: ' . $priceTypes[array_rand($priceTypes)] . ', Delivery: ' . rand(1, 14) . ' days.',
            'price' => rand(50, 500),
            'type' => 'service'
        ]);
    }

    private function generateBusinessListing($baseData, $category, $index): array
    {
        $businessTypes = ['store', 'local', 'service'];
        $titles = [
            'Online Electronics Store',
            'Local Restaurant Business',
            'Professional Services Firm',
            'Fashion Boutique Online',
            'Home Services Company',
            'Digital Agency Business'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Established business with excellent reputation and customer base. Great opportunity for partnership or investment. Business Type: ' . $businessTypes[array_rand($businessTypes)] . ', Website: https://example.com, Phone: +1' . rand(1000000000, 9999999999) . ', Address: Business Address.',
            'price' => rand(10000, 500000),
            'type' => 'business'
        ]);
    }

    private function generateAffiliateListing($baseData, $category, $index): array
    {
        $programTypes = ['product', 'service', 'digital'];
        $titles = [
            'High-Ticket Product Affiliate',
            'Digital Service Affiliate Program',
            'Software Product Partnership',
            'E-commerce Affiliate Opportunity',
            'Online Course Affiliate',
            'Subscription Service Affiliate'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Lucrative affiliate program with high commission rates and excellent conversion rates. Join our successful network. Program Type: ' . $programTypes[array_rand($programTypes)] . ', Commission: ' . rand(5, 30) . '%, Link: https://example.com/affiliate.',
            'price' => 0,
            'type' => 'affiliate'
        ]);
    }

    private function generateTravelListing($baseData, $category, $index): array
    {
        $serviceTypes = ['hotel', 'bandb', 'transport', 'tour'];
        $priceTypes = ['per_night', 'per_person', 'per_trip', 'per_hour'];
        $titles = [
            'Luxury Hotel Resort',
            'Cozy Bed & Breakfast',
            'Airport Transfer Service',
            'City Tour Package',
            'Beach Resort Stay',
            'Mountain Lodge Experience'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)],
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . uniqid(),
            'description' => 'Premium travel service with excellent amenities and professional staff. Unforgettable experience guaranteed. Service Type: ' . $serviceTypes[array_rand($serviceTypes)] . ', Price Type: ' . $priceTypes[array_rand($priceTypes)] . ', Location: Popular Tourist Destination, Contact: +1' . rand(1000000000, 9999999999) . '.',
            'price' => rand(50, 500),
            'type' => 'travel'
        ]);
    }

    private function generateGenericListing($baseData, $category, $index): array
    {
        $titles = [
            'Professional Service Offer',
            'Quality Product Listing',
            'Business Opportunity',
            'Special Promotion Deal',
            'Premium Item Available'
        ];

        return array_merge($baseData, [
            'title' => $titles[$index % count($titles)] . ' - ' . $category->name,
            'slug' => Str::slug($titles[$index % count($titles)]) . '-' . $category->slug . '-' . uniqid(),
            'description' => "High-quality offering in {$category->name}. Excellent value and professional service guaranteed.",
            'price' => rand(50, 5000),
            'type' => 'general'
        ]);
    }
}
