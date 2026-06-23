<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommunitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $categories = Category::all();

        // Get first user as creator
        $creator = User::first();

        if (!$creator) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        $communities = [
            [
                'name' => 'Property & Real Estate – UK',
                'description' => 'A community for UK property buyers, sellers, renters, and investors. Discuss market trends, share listings, and get advice on property transactions.',
                'category_name' => 'Property & Real Estate',
                'scope' => 'region',
                'region' => 'UK',
                'city' => null,
                'strict_moderation' => true,
                'beginner_friendly' => true,
                'is_featured' => true,
                'is_verified' => true,
                'rules' => [
                    'No spam or self-promotion without disclosure',
                    'Be respectful to all members',
                    'Include accurate pricing and location information',
                    'No scams or fraudulent listings',
                    'Follow UK property laws and regulations'
                ]
            ],
            [
                'name' => 'Funding & Investment – Startups',
                'description' => 'Connect startups with investors. Share funding opportunities, pitch decks, and investment advice. Network with entrepreneurs and VCs.',
                'category_name' => 'Funding & Investment',
                'scope' => 'global',
                'region' => null,
                'city' => null,
                'strict_moderation' => true,
                'beginner_friendly' => false,
                'is_featured' => true,
                'is_verified' => true,
                'rules' => [
                    'Verify all investment claims',
                    'No financial advice without proper disclaimer',
                    'Respect confidentiality of sensitive information',
                    'Be transparent about conflicts of interest',
                    'No guarantee of returns'
                ]
            ],
            [
                'name' => 'Charities & Donations – Global Causes',
                'description' => 'A hub for charitable organizations and donors. Share causes, fundraising campaigns, and volunteer opportunities. Make a difference together.',
                'category_name' => 'Charities & Donations',
                'scope' => 'global',
                'region' => null,
                'city' => null,
                'strict_moderation' => true,
                'beginner_friendly' => true,
                'is_featured' => true,
                'is_verified' => true,
                'rules' => [
                    'Verify all charity registrations',
                    'Be transparent about fund allocation',
                    'No political or religious discrimination',
                    'Respect donor privacy',
                    'Report misuse of funds'
                ]
            ],
            [
                'name' => 'Events & Entertainment – London',
                'description' => 'Discover and share events in London. Concerts, festivals, theatre, sports, and more. Connect with event organizers and attendees.',
                'category_name' => 'Events & Entertainment',
                'scope' => 'city',
                'region' => 'UK',
                'city' => 'London',
                'strict_moderation' => false,
                'beginner_friendly' => true,
                'is_featured' => true,
                'is_verified' => true,
                'rules' => [
                    'Include accurate event dates and times',
                    'No ticket scalping',
                    'Respect venue policies',
                    'Be helpful to newcomers',
                    'Share honest reviews'
                ]
            ],
            [
                'name' => 'Vehicles & Transport – EU',
                'description' => 'Buy, sell, and discuss vehicles across the European Union. Cars, motorcycles, commercial vehicles, and transport services.',
                'category_name' => 'Vehicles & Transport',
                'scope' => 'region',
                'region' => 'EU',
                'city' => null,
                'strict_moderation' => true,
                'beginner_friendly' => true,
                'is_featured' => false,
                'is_verified' => true,
                'rules' => [
                    'Include accurate vehicle specifications',
                    'No stolen vehicles',
                    'Follow EU vehicle regulations',
                    'Be honest about vehicle condition',
                    'Provide proper documentation'
                ]
            ],
            [
                'name' => 'Services & Solutions – Business',
                'description' => 'B2B services marketplace. Connect with service providers, share business solutions, and discuss industry best practices.',
                'category_name' => 'Services & Solutions',
                'scope' => 'global',
                'region' => null,
                'city' => null,
                'strict_moderation' => true,
                'beginner_friendly' => false,
                'is_featured' => true,
                'is_verified' => true,
                'rules' => [
                    'Verify service credentials',
                    'No false advertising',
                    'Maintain professional conduct',
                    'Respect client confidentiality',
                    'Deliver on promises'
                ]
            ],
            [
                'name' => 'Jobs & Vacancies – Remote Work',
                'description' => 'Remote job opportunities and career advice. Connect with remote workers, share job listings, and discuss remote work best practices.',
                'category_name' => 'Jobs & Vacancies',
                'scope' => 'global',
                'region' => null,
                'city' => null,
                'strict_moderation' => false,
                'beginner_friendly' => true,
                'is_featured' => true,
                'is_verified' => true,
                'rules' => [
                    'Include accurate job descriptions',
                    'No discrimination',
                    'Be transparent about compensation',
                    'Respect applicants time',
                    'Provide feedback when possible'
                ]
            ],
            [
                'name' => 'Buy & Sell – General Marketplace',
                'description' => 'General marketplace for buying and selling items. From electronics to furniture, find great deals and connect with sellers.',
                'category_name' => 'Buy & Sell',
                'scope' => 'global',
                'region' => null,
                'city' => null,
                'strict_moderation' => true,
                'beginner_friendly' => true,
                'is_featured' => false,
                'is_verified' => false,
                'rules' => [
                    'Describe items accurately',
                    'No prohibited items',
                    'Be honest about condition',
                    'Ship items promptly',
                    'Resolve disputes fairly'
                ]
            ],
            [
                'name' => 'Books & Literature – Readers Club',
                'description' => 'Book lovers community. Share reviews, recommendations, and discuss your favorite books. Connect with authors and fellow readers.',
                'category_name' => 'Books & Literature',
                'scope' => 'global',
                'region' => null,
                'city' => null,
                'strict_moderation' => false,
                'beginner_friendly' => true,
                'is_featured' => false,
                'is_verified' => true,
                'rules' => [
                    'No spoilers without warning',
                    'Respect different opinions',
                    'Cite sources when quoting',
                    'Support independent authors',
                    'Be constructive in criticism'
                ]
            ],
            [
                'name' => 'Resorts & Travel – Adventure Seekers',
                'description' => 'Travel enthusiasts community. Share travel experiences, resort reviews, and travel tips. Plan your next adventure together.',
                'category_name' => 'Resorts & Travel',
                'scope' => 'global',
                'region' => null,
                'city' => null,
                'strict_moderation' => false,
                'beginner_friendly' => true,
                'is_featured' => true,
                'is_verified' => true,
                'rules' => [
                    'Share honest travel experiences',
                    'Respect local cultures',
                    'Include accurate pricing',
                    'No fake reviews',
                    'Share safety tips'
                ]
            ],
            [
                'name' => 'Midlands Jobs – UK',
                'description' => 'Job opportunities in the Midlands region of the UK. Connect with local employers and job seekers.',
                'category_name' => 'Jobs & Vacancies',
                'scope' => 'region',
                'region' => 'UK',
                'city' => null,
                'strict_moderation' => false,
                'beginner_friendly' => true,
                'is_featured' => false,
                'is_verified' => false,
                'rules' => [
                    'Focus on Midlands-based opportunities',
                    'Include accurate job details',
                    'No discrimination',
                    'Be responsive to applicants',
                    'Share local insights'
                ]
            ],
            [
                'name' => 'UK Property Deals',
                'description' => 'Exclusive property deals and investment opportunities in the UK. Share off-market listings and investment strategies.',
                'category_name' => 'Property & Real Estate',
                'scope' => 'region',
                'region' => 'UK',
                'city' => null,
                'strict_moderation' => true,
                'beginner_friendly' => false,
                'is_featured' => true,
                'is_verified' => true,
                'rules' => [
                    'Verify all property information',
                    'No insider trading',
                    'Maintain confidentiality',
                    'Professional conduct required',
                    'Accurate financial projections'
                ]
            ],
        ];

        foreach ($communities as $communityData) {
            // Find category by name
            $category = $categories->firstWhere('name', $communityData['category_name']);
            
            // Generate slug
            $slug = Str::slug($communityData['name']);
            $originalSlug = $slug;
            $counter = 1;

            while (Community::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            Community::create([
                'community_id' => Str::uuid(),
                'name' => $communityData['name'],
                'slug' => $slug,
                'description' => $communityData['description'],
                'category_id' => $category ? $category->category_id : null,
                'scope' => $communityData['scope'],
                'region' => $communityData['region'],
                'city' => $communityData['city'],
                'strict_moderation' => $communityData['strict_moderation'],
                'beginner_friendly' => $communityData['beginner_friendly'],
                'is_featured' => $communityData['is_featured'],
                'is_verified' => $communityData['is_verified'],
                'rules' => $communityData['rules'],
                'created_by' => $creator->user_id,
                'members_count' => rand(100, 5000),
                'posts_count' => rand(50, 1000),
                'active_ads_count' => rand(20, 500),
            ]);
        }

        $this->command->info('Communities seeded successfully.');
    }
}
