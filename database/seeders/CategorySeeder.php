<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent Categories
        $propertyCategory = Category::create([
            'name' => 'Property',
            'slug' => Str::slug('Property'),
            'description' => 'Property listings - houses, commercial properties for sale or rent',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'property_type' => ['type' => 'select', 'label' => 'Property Type', 'options' => ['residential' => 'Residential', 'commercial' => 'Commercial'], 'required' => true],
                    'listing_type' => ['type' => 'select', 'label' => 'Listing Type', 'options' => ['sale' => 'For Sale', 'rent' => 'For Rent'], 'required' => true],
                    'price' => ['type' => 'number', 'label' => 'Price', 'required' => true],
                    'bedrooms' => ['type' => 'number', 'label' => 'Bedrooms', 'required' => false],
                    'bathrooms' => ['type' => 'number', 'label' => 'Bathrooms', 'required' => false],
                    'area' => ['type' => 'text', 'label' => 'Area (sq ft/m²)', 'required' => true],
                    'location' => ['type' => 'text', 'label' => 'Location', 'required' => true],
                ]
            ]),
        ]);

        $vehiclesCategory = Category::create([
            'name' => 'Vehicles',
            'slug' => Str::slug('Vehicles'),
            'description' => 'Vehicles for sale, hire, and commercial use',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'vehicle_type' => ['type' => 'select', 'label' => 'Vehicle Type', 'options' => ['car' => 'Car', 'motorcycle' => 'Motorcycle', 'truck' => 'Truck', 'van' => 'Van'], 'required' => true],
                    'listing_type' => ['type' => 'select', 'label' => 'Listing Type', 'options' => ['sale' => 'For Sale', 'hire' => 'For Hire', 'commercial' => 'Commercial'], 'required' => true],
                    'price' => ['type' => 'number', 'label' => 'Price', 'required' => true],
                    'make' => ['type' => 'text', 'label' => 'Make', 'required' => true],
                    'model' => ['type' => 'text', 'label' => 'Model', 'required' => true],
                    'year' => ['type' => 'number', 'label' => 'Year', 'required' => true],
                    'mileage' => ['type' => 'number', 'label' => 'Mileage', 'required' => false],
                    'condition' => ['type' => 'select', 'label' => 'Condition', 'options' => ['new' => 'New', 'used' => 'Used', 'refurbished' => 'Refurbished'], 'required' => true],
                ]
            ]),
        ]);

        $eventsCategory = Category::create([
            'name' => 'Events',
            'slug' => Str::slug('Events'),
            'description' => 'Events with dates, locations, and times',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'event_name' => ['type' => 'text', 'label' => 'Event Name', 'required' => true],
                    'event_date' => ['type' => 'date', 'label' => 'Event Date', 'required' => true],
                    'event_time' => ['type' => 'time', 'label' => 'Event Time', 'required' => true],
                    'location' => ['type' => 'text', 'label' => 'Location', 'required' => true],
                    'event_type' => ['type' => 'select', 'label' => 'Event Type', 'options' => ['conference' => 'Conference', 'social' => 'Social', 'workshop' => 'Workshop', 'other' => 'Other'], 'required' => true],
                    'ticket_price' => ['type' => 'number', 'label' => 'Ticket Price', 'required' => false],
                    'organizer' => ['type' => 'text', 'label' => 'Organizer', 'required' => true],
                ]
            ]),
        ]);

        $buySellCategory = Category::create([
            'name' => 'Buy and Sell',
            'slug' => Str::slug('Buy and Sell'),
            'description' => 'General selling posts - items for sale, swap, or free',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'item_type' => ['type' => 'select', 'label' => 'Listing Type', 'options' => ['sale' => 'For Sale', 'swap' => 'For Swap', 'free' => 'Free'], 'required' => true],
                    'price' => ['type' => 'number', 'label' => 'Price', 'required' => false],
                    'condition' => ['type' => 'select', 'label' => 'Condition', 'options' => ['new' => 'New', 'like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair'], 'required' => true],
                    'category' => ['type' => 'text', 'label' => 'Item Category', 'required' => true],
                    'brand' => ['type' => 'text', 'label' => 'Brand', 'required' => false],
                ]
            ]),
        ]);

        $booksCategory = Category::create([
            'name' => 'Books',
            'slug' => Str::slug('Books'),
            'description' => 'Books for sale, PDF downloads, and audiobooks with genre filtering',
            'is_active' => true,
            'sort_order' => 5,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'listing_type' => ['type' => 'select', 'label' => 'Listing Type', 'options' => ['sale' => 'For Sale', 'swap' => 'For Swap'], 'required' => true],
                    'book_type' => ['type' => 'select', 'label' => 'Book Type', 'options' => ['physical' => 'Physical Book', 'pdf' => 'PDF Download', 'audiobook' => 'Audiobook'], 'required' => true],
                    'price' => ['type' => 'number', 'label' => 'Price', 'required' => false],
                    'genre' => ['type' => 'select', 'label' => 'Genre', 'options' => ['action' => 'Action', 'education' => 'Education', 'drama' => 'Drama', 'thriller' => 'Thriller', 'fiction' => 'Fiction', 'non_fiction' => 'Non-Fiction', 'textbook' => 'Textbook', 'romance' => 'Romance', 'mystery' => 'Mystery', 'scifi' => 'Sci-Fi', 'fantasy' => 'Fantasy', 'biography' => 'Biography', 'self_help' => 'Self-Help', 'business' => 'Business', 'children' => 'Children'], 'required' => true],
                    'author' => ['type' => 'text', 'label' => 'Author', 'required' => true],
                    'isbn' => ['type' => 'text', 'label' => 'ISBN', 'required' => false],
                    'format' => ['type' => 'select', 'label' => 'Format', 'options' => ['physical' => 'Physical Book', 'e_book' => 'E-book', 'audiobook' => 'Audiobook'], 'required' => true],
                    'condition' => ['type' => 'select', 'label' => 'Condition', 'options' => ['new' => 'New', 'like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair'], 'required' => false],
                    'file_upload' => ['type' => 'file', 'label' => 'Upload File (PDF/Audio)', 'required' => false, 'accept' => '.pdf,.mp3,.m4a,.wav'],
                    'website_url' => ['type' => 'url', 'label' => 'External Website URL', 'required' => false],
                    'is_downloadable' => ['type' => 'checkbox', 'label' => 'Allow download after purchase', 'required' => false],
                ]
            ]),
            'filter_config' => json_encode([
                'genres' => [
                    'action' => 'Action',
                    'education' => 'Education', 
                    'drama' => 'Drama',
                    'thriller' => 'Thriller',
                    'fiction' => 'Fiction',
                    'non_fiction' => 'Non-Fiction',
                    'textbook' => 'Textbook',
                    'romance' => 'Romance',
                    'mystery' => 'Mystery',
                    'scifi' => 'Sci-Fi',
                    'fantasy' => 'Fantasy',
                    'biography' => 'Biography',
                    'self_help' => 'Self-Help',
                    'business' => 'Business',
                    'children' => 'Children'
                ],
                'book_types' => [
                    'physical' => 'Physical Books',
                    'pdf' => 'PDF Downloads',
                    'audiobook' => 'Audiobooks'
                ],
                'formats' => [
                    'physical' => 'Physical Book',
                    'e_book' => 'E-book',
                    'audiobook' => 'Audiobook'
                ],
                'conditions' => [
                    'new' => 'New',
                    'like_new' => 'Like New',
                    'good' => 'Good',
                    'fair' => 'Fair'
                ],
                'price_range' => true,
                'author_search' => true,
                'isbn_search' => true,
                'date_posted' => true,
                'sort_options' => ['newest', 'oldest', 'price_low', 'price_high', 'relevance', 'author_az', 'author_za', 'title_az', 'title_za']
            ])
        ]);

        $fundingCategory = Category::create([
            'name' => 'Funding',
            'slug' => Str::slug('Funding'),
            'description' => 'Funding and crowdfunding opportunities',
            'is_active' => true,
            'sort_order' => 6,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'funding_type' => ['type' => 'select', 'label' => 'Funding Type', 'options' => ['personal' => 'Personal Project', 'business' => 'Business', 'community' => 'Community Project'], 'required' => true],
                    'goal_amount' => ['type' => 'number', 'label' => 'Funding Goal', 'required' => true],
                    'current_amount' => ['type' => 'number', 'label' => 'Current Amount', 'required' => false],
                    'deadline' => ['type' => 'date', 'label' => 'Funding Deadline', 'required' => true],
                    'project_description' => ['type' => 'textarea', 'label' => 'Project Description', 'required' => true],
                    'organizer_name' => ['type' => 'text', 'label' => 'Organizer Name', 'required' => true],
                ]
            ]),
        ]);

        $donationsCategory = Category::create([
            'name' => 'Charities and Donations',
            'slug' => Str::slug('Charities and Donations'),
            'description' => 'Charitable organizations and donation requests',
            'is_active' => true,
            'sort_order' => 7,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'charity_type' => ['type' => 'select', 'label' => 'Charity Type', 'options' => ['humanitarian' => 'Humanitarian', 'medical' => 'Medical', 'education' => 'Education', 'disaster' => 'Disaster Relief'], 'required' => true],
                    'goal_amount' => ['type' => 'number', 'label' => 'Funding Goal', 'required' => true],
                    'current_amount' => ['type' => 'number', 'label' => 'Current Amount', 'required' => false],
                    'deadline' => ['type' => 'date', 'label' => 'Donation Deadline', 'required' => false],
                    'description' => ['type' => 'textarea', 'label' => 'Description', 'required' => true],
                    'organizer_name' => ['type' => 'text', 'label' => 'Organizer Name', 'required' => true],
                ]
            ]),
        ]);

        $bannerCategory = Category::create([
            'name' => 'Banner',
            'slug' => Str::slug('Banner'),
            'description' => 'Banner advertisements and promotions',
            'is_active' => true,
            'sort_order' => 8,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'banner_type' => ['type' => 'select', 'label' => 'Banner Type', 'options' => ['image' => 'Image Banner', 'video' => 'Video Banner'], 'required' => true],
                    'target_url' => ['type' => 'url', 'label' => 'Target URL', 'required' => true],
                    'duration_days' => ['type' => 'number', 'label' => 'Duration (Days)', 'required' => true],
                    'banner_file' => ['type' => 'file', 'label' => 'Banner File', 'required' => true, 'accept' => '.jpg,.jpeg,.png,.gif,.mp4'],
                ]
            ]),
        ]);

        $sponsoredCategory = Category::create([
            'name' => 'Sponsored Ads',
            'slug' => Str::slug('Sponsored Ads'),
            'description' => 'Sponsored, featured, and promoted advertisements',
            'is_active' => true,
            'sort_order' => 9,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'ad_type' => ['type' => 'select', 'label' => 'Ad Type', 'options' => ['sponsored' => 'Sponsored', 'featured' => 'Featured', 'promoted' => 'Promoted'], 'required' => true],
                    'target_url' => ['type' => 'url', 'label' => 'Target URL', 'required' => false],
                    'duration_days' => ['type' => 'number', 'label' => 'Duration (Days)', 'required' => true],
                    'budget' => ['type' => 'number', 'label' => 'Budget', 'required' => true],
                ]
            ]),
        ]);

        $jobsCategory = Category::create([
            'name' => 'Jobs and Vacancies',
            'slug' => Str::slug('Jobs and Vacancies'),
            'description' => 'Job postings and career opportunities',
            'is_active' => true,
            'sort_order' => 10,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'job_type' => ['type' => 'select', 'label' => 'Job Type', 'options' => ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'freelance' => 'Freelance'], 'required' => true],
                    'salary' => ['type' => 'number', 'label' => 'Salary', 'required' => false],
                    'experience_level' => ['type' => 'select', 'label' => 'Experience Level', 'options' => ['entry' => 'Entry Level', 'mid' => 'Mid Level', 'senior' => 'Senior Level', 'executive' => 'Executive'], 'required' => true],
                    'location' => ['type' => 'text', 'label' => 'Location', 'required' => true],
                    'company_name' => ['type' => 'text', 'label' => 'Company Name', 'required' => true],
                ]
            ]),
        ]);

        $servicesCategory = Category::create([
            'name' => 'Services',
            'slug' => Str::slug('Services'),
            'description' => 'Fiverr/PeoplePerHour style marketplace for services',
            'is_active' => true,
            'sort_order' => 11,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'service_type' => ['type' => 'select', 'label' => 'Service Type', 'options' => ['digital' => 'Digital Service', 'local' => 'Local Service', 'consulting' => 'Consulting', 'creative' => 'Creative Service'], 'required' => true],
                    'price_type' => ['type' => 'select', 'label' => 'Price Type', 'options' => ['fixed' => 'Fixed Price', 'hourly' => 'Hourly Rate'], 'required' => true],
                    'price' => ['type' => 'number', 'label' => 'Price', 'required' => true],
                    'delivery_time' => ['type' => 'text', 'label' => 'Delivery Time', 'required' => true],
                    'service_description' => ['type' => 'textarea', 'label' => 'Service Description', 'required' => true],
                ]
            ]),
        ]);

        $businessCategory = Category::create([
            'name' => 'Business and Stores',
            'slug' => Str::slug('Business and Stores'),
            'description' => 'Business listings and online stores',
            'is_active' => true,
            'sort_order' => 12,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'business_type' => ['type' => 'select', 'label' => 'Business Type', 'options' => ['store' => 'Online Store', 'local' => 'Local Business', 'service' => 'Service Business'], 'required' => true],
                    'website_url' => ['type' => 'url', 'label' => 'Website URL', 'required' => false],
                    'phone' => ['type' => 'text', 'label' => 'Phone Number', 'required' => true],
                    'address' => ['type' => 'text', 'label' => 'Address', 'required' => false],
                    'business_description' => ['type' => 'textarea', 'label' => 'Business Description', 'required' => true],
                ]
            ]),
        ]);

        $affiliateCategory = Category::create([
            'name' => 'Affiliate Programs',
            'slug' => Str::slug('Affiliate Programs'),
            'description' => 'User affiliate links and program joining',
            'is_active' => true,
            'sort_order' => 13,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'program_type' => ['type' => 'select', 'label' => 'Program Type', 'options' => ['product' => 'Product Affiliate', 'service' => 'Service Affiliate', 'digital' => 'Digital Product'], 'required' => true],
                    'affiliate_link' => ['type' => 'url', 'label' => 'Affiliate Link', 'required' => true],
                    'commission_rate' => ['type' => 'text', 'label' => 'Commission Rate', 'required' => true],
                    'program_description' => ['type' => 'textarea', 'label' => 'Program Description', 'required' => true],
                ]
            ]),
        ]);

        $hotelTravelCategory = Category::create([
            'name' => 'Hotel, Resorts & Travel',
            'slug' => Str::slug('Hotel, Resorts & Travel'),
            'description' => 'Combined category for hotels, B&B, transport services, and tourist activities',
            'is_active' => true,
            'sort_order' => 14,
            'parent_id' => null,
            'posting_form_config' => json_encode([
                'fields' => [
                    'service_type' => ['type' => 'select', 'label' => 'Service Type', 'options' => ['hotel' => 'Hotel', 'bandb' => 'B&B', 'transport' => 'Transport Service', 'tour' => 'Tour Service'], 'required' => true],
                    'price_type' => ['type' => 'select', 'label' => 'Price Type', 'options' => ['per_night' => 'Per Night', 'per_person' => 'Per Person', 'per_trip' => 'Per Trip', 'per_hour' => 'Per Hour'], 'required' => true],
                    'price' => ['type' => 'number', 'label' => 'Price', 'required' => true],
                    'location' => ['type' => 'text', 'label' => 'Location', 'required' => true],
                    'contact' => ['type' => 'text', 'label' => 'Contact Information', 'required' => true],
                ]
            ]),
        ]);

        // Child Categories for Property
        Category::create([
            'name' => 'Houses for Sale',
            'slug' => Str::slug('Houses for Sale'),
            'description' => 'Residential houses for sale',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $propertyCategory->category_id,
        ]);

        Category::create([
            'name' => 'Houses for Rent',
            'slug' => Str::slug('Houses for Rent'),
            'description' => 'Residential houses for rent',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $propertyCategory->category_id,
        ]);

        Category::create([
            'name' => 'Commercial Property for Sale',
            'slug' => Str::slug('Commercial Property for Sale'),
            'description' => 'Commercial properties for sale',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $propertyCategory->category_id,
        ]);

        Category::create([
            'name' => 'Commercial Property for Rent',
            'slug' => Str::slug('Commercial Property for Rent'),
            'description' => 'Commercial properties for rent',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $propertyCategory->category_id,
        ]);

        // Child Categories for Vehicles
        Category::create([
            'name' => 'Cars for Sale',
            'slug' => Str::slug('Cars for Sale'),
            'description' => 'Cars and automobiles for sale',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $vehiclesCategory->category_id,
        ]);

        Category::create([
            'name' => 'Vehicle Hire',
            'slug' => Str::slug('Vehicle Hire'),
            'description' => 'Vehicle rental and hire services',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $vehiclesCategory->category_id,
        ]);

        Category::create([
            'name' => 'Commercial Vehicles',
            'slug' => Str::slug('Commercial Vehicles'),
            'description' => 'Commercial and industrial vehicles',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $vehiclesCategory->category_id,
        ]);

        // Child Categories for Events
        Category::create([
            'name' => 'Conferences',
            'slug' => Str::slug('Conferences'),
            'description' => 'Business and professional conferences',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $eventsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Social Events',
            'slug' => Str::slug('Social Events'),
            'description' => 'Social gatherings and parties',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $eventsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Workshops',
            'slug' => Str::slug('Workshops'),
            'description' => 'Educational workshops and seminars',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $eventsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Event Venues',
            'slug' => Str::slug('Event Venues'),
            'description' => 'Venues available for events around the world',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $eventsCategory->category_id,
            'posting_form_config' => json_encode([
                'fields' => [
                    'venue_name' => ['type' => 'text', 'label' => 'Venue Name', 'required' => true],
                    'venue_type' => ['type' => 'select', 'label' => 'Venue Type', 'options' => ['conference_hall' => 'Conference Hall', 'banquet_hall' => 'Banquet Hall', 'outdoor' => 'Outdoor Venue', 'restaurant' => 'Restaurant', 'hotel' => 'Hotel', 'stadium' => 'Stadium', 'theater' => 'Theater', 'gallery' => 'Art Gallery', 'community_center' => 'Community Center', 'other' => 'Other'], 'required' => true],
                    'capacity' => ['type' => 'number', 'label' => 'Capacity', 'required' => true],
                    'location' => ['type' => 'text', 'label' => 'Location', 'required' => true],
                    'country' => ['type' => 'text', 'label' => 'Country', 'required' => true],
                    'price_per_hour' => ['type' => 'number', 'label' => 'Price Per Hour', 'required' => false],
                    'price_per_day' => ['type' => 'number', 'label' => 'Price Per Day', 'required' => false],
                    'facilities' => ['type' => 'checkbox', 'label' => 'Available Facilities', 'options' => ['wifi' => 'WiFi', 'parking' => 'Parking', 'projector' => 'Projector', 'sound_system' => 'Sound System', 'catering' => 'Catering', 'air_conditioning' => 'Air Conditioning', 'wheelchair_accessible' => 'Wheelchair Accessible'], 'required' => false],
                    'contact_email' => ['type' => 'email', 'label' => 'Contact Email', 'required' => true],
                    'contact_phone' => ['type' => 'text', 'label' => 'Contact Phone', 'required' => false],
                    'website' => ['type' => 'url', 'label' => 'Website', 'required' => false],
                    'description' => ['type' => 'textarea', 'label' => 'Venue Description', 'required' => true],
                ]
            ]),
            'filter_config' => json_encode([
                'venue_types' => [
                    'conference_hall' => 'Conference Hall',
                    'banquet_hall' => 'Banquet Hall',
                    'outdoor' => 'Outdoor Venue',
                    'restaurant' => 'Restaurant',
                    'hotel' => 'Hotel',
                    'stadium' => 'Stadium',
                    'theater' => 'Theater',
                    'gallery' => 'Art Gallery',
                    'community_center' => 'Community Center',
                    'other' => 'Other'
                ],
                'capacity_range' => true,
                'price_range' => true,
                'facilities' => [
                    'wifi' => 'WiFi',
                    'parking' => 'Parking',
                    'projector' => 'Projector',
                    'sound_system' => 'Sound System',
                    'catering' => 'Catering',
                    'air_conditioning' => 'Air Conditioning',
                    'wheelchair_accessible' => 'Wheelchair Accessible'
                ],
                'countries' => true,
                'sort_options' => ['newest', 'oldest', 'price_low', 'price_high', 'capacity_low', 'capacity_high', 'relevance']
            ])
        ]);

        // Child Categories for Buy and Sell
        Category::create([
            'name' => 'Items for Sale',
            'slug' => Str::slug('Items for Sale'),
            'description' => 'Various items for sale',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $buySellCategory->category_id,
        ]);

        Category::create([
            'name' => 'Items for Swap',
            'slug' => Str::slug('Items for Swap'),
            'description' => 'Items available for exchange/swap',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $buySellCategory->category_id,
        ]);

        Category::create([
            'name' => 'Free Items',
            'slug' => Str::slug('Free Items'),
            'description' => 'Items to give away for free',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $buySellCategory->category_id,
        ]);

        // Child Categories for Books
        Category::create([
            'name' => 'Physical Books',
            'slug' => Str::slug('Physical Books'),
            'description' => 'Physical books for sale or swap',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $booksCategory->category_id,
        ]);

        Category::create([
            'name' => 'PDF Downloads',
            'slug' => Str::slug('PDF Downloads'),
            'description' => 'Digital PDF books for download',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $booksCategory->category_id,
        ]);

        Category::create([
            'name' => 'Audiobooks',
            'slug' => Str::slug('Audiobooks'),
            'description' => 'Audiobook downloads and streaming',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $booksCategory->category_id,
        ]);

        // Child Categories for Funding
        Category::create([
            'name' => 'Business Investment',
            'slug' => Str::slug('Business Investment'),
            'description' => 'Business and startup funding opportunities',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $fundingCategory->category_id,
        ]);

        Category::create([
            'name' => 'Partnerships',
            'slug' => Str::slug('Partnerships'),
            'description' => 'Business partnership opportunities',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $fundingCategory->category_id,
        ]);

        Category::create([
            'name' => 'Startup Funding',
            'slug' => Str::slug('Startup Funding'),
            'description' => 'Early-stage startup funding',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $fundingCategory->category_id,
        ]);

        // Child Categories for Charities and Donations
        Category::create([
            'name' => 'Humanitarian Causes',
            'slug' => Str::slug('Humanitarian Causes'),
            'description' => 'Humanitarian aid and relief efforts',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $donationsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Medical Causes',
            'slug' => Str::slug('Medical Causes'),
            'description' => 'Medical and health-related donations',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $donationsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Education Causes',
            'slug' => Str::slug('Education Causes'),
            'description' => 'Educational donations and scholarships',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $donationsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Disaster Relief',
            'slug' => Str::slug('Disaster Relief'),
            'description' => 'Disaster relief and emergency funds',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $donationsCategory->category_id,
        ]);

        // Child Categories for Hotel, Resorts & Travel
        Category::create([
            'name' => 'Hotels',
            'slug' => Str::slug('Hotels'),
            'description' => 'Hotel accommodations and services',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $hotelTravelCategory->category_id,
        ]);

        Category::create([
            'name' => 'B&B',
            'slug' => Str::slug('B&B'),
            'description' => 'Bed and Breakfast accommodations',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $hotelTravelCategory->category_id,
        ]);

        Category::create([
            'name' => 'Transport Services',
            'slug' => Str::slug('Transport Services'),
            'description' => 'Transportation services for tourists',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $hotelTravelCategory->category_id,
        ]);

        Category::create([
            'name' => 'Tour Services',
            'slug' => Str::slug('Tour Services'),
            'description' => 'Tour guides and tour packages',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $hotelTravelCategory->category_id,
        ]);

        // Child Categories for Jobs and Vacancies
        Category::create([
            'name' => 'Full Time Jobs',
            'slug' => Str::slug('Full Time Jobs'),
            'description' => 'Full-time employment opportunities',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $jobsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Part Time Jobs',
            'slug' => Str::slug('Part Time Jobs'),
            'description' => 'Part-time employment opportunities',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $jobsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Contract Work',
            'slug' => Str::slug('Contract Work'),
            'description' => 'Contract and temporary positions',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $jobsCategory->category_id,
        ]);

        Category::create([
            'name' => 'Freelance Opportunities',
            'slug' => Str::slug('Freelance Opportunities'),
            'description' => 'Freelance and gig work',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $jobsCategory->category_id,
        ]);

        // Child Categories for Services
        Category::create([
            'name' => 'Digital Services',
            'slug' => Str::slug('Digital Services'),
            'description' => 'Online and digital service offerings',
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $servicesCategory->category_id,
        ]);

        Category::create([
            'name' => 'Local Services',
            'slug' => Str::slug('Local Services'),
            'description' => 'Local in-person services',
            'is_active' => true,
            'sort_order' => 2,
            'parent_id' => $servicesCategory->category_id,
        ]);

        Category::create([
            'name' => 'Consulting',
            'slug' => Str::slug('Consulting'),
            'description' => 'Professional consulting services',
            'is_active' => true,
            'sort_order' => 3,
            'parent_id' => $servicesCategory->category_id,
        ]);

        Category::create([
            'name' => 'Creative Services',
            'slug' => Str::slug('Creative Services'),
            'description' => 'Creative and artistic services',
            'is_active' => true,
            'sort_order' => 4,
            'parent_id' => $servicesCategory->category_id,
        ]);
    }
}

