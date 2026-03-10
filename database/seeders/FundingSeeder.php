<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FundingSeeder extends Seeder
{
    public function run()
    {
        // Create Funding Projects
        $projects = [
            [
                'customer_id' => 1,
                'title' => 'Eco-Friendly Water Bottle',
                'slug' => 'eco-friendly-water-bottle',
                'tagline' => 'Sustainable hydration solution',
                'project_type' => 'product',
                'category' => 'environment',
                'description' => 'A revolutionary water bottle made from 100% recycled materials with built-in filtration system.',
                'problem_solved' => 'Reduces plastic waste and provides clean drinking water anywhere.',
                'vision_mission' => 'To make sustainable hydration accessible to everyone while reducing environmental impact.',
                'why_matters_now' => 'Plastic pollution is at crisis levels - we need immediate action.',
                'funding_goal' => 50000.00,
                'minimum_contribution' => 10.00,
                'funding_model' => 'reward_based',
                'current_funded' => 32500.00,
                'backers_count' => 245,
                'funding_deadline' => Carbon::now()->addDays(15),
                'status' => 'active',
                'risk_level' => 'low',
                'is_verified' => true,
                'is_featured' => true,
                'is_promoted' => false,
                'is_sponsored' => false,
                'country' => 'United States',
                'region' => 'West Coast',
                'cover_image' => 'funding/eco-bottle-cover.jpg',
                'additional_images' => json_encode(['funding/eco-bottle-1.jpg', 'funding/eco-bottle-2.jpg']),
                'pitch_video_url' => 'https://youtube.com/watch?v=eco-bottle-demo',
                'team_members' => json_encode([
                    ['name' => 'Sarah Green', 'role' => 'CEO', 'experience' => '10 years in sustainable products'],
                    ['name' => 'Mike Chen', 'role' => 'CTO', 'experience' => 'Engineer with 5 patents']
                ]),
                'use_of_funds' => json_encode([
                    'Manufacturing' => 60,
                    'Marketing' => 20,
                    'Operations' => 15,
                    'Legal/Admin' => 5
                ]),
                'milestones' => json_encode([
                    ['date' => '2024-02-01', 'milestone' => 'Production begins'],
                    ['date' => '2024-03-15', 'milestone' => 'First batch shipped'],
                    ['date' => '2024-04-01', 'milestone' => 'Retail launch']
                ]),
                'social_links' => json_encode([
                    'twitter' => 'https://twitter.com/ecobottle',
                    'instagram' => 'https://instagram.com/ecobottle'
                ]),
                'revenue_model' => 'Direct-to-consumer sales through website and retail partnerships',
                'forecasts' => 'Projected $500K revenue in first year',
                'risk_disclosures' => 'Manufacturing delays, supply chain disruptions',
                'website' => 'https://ecobottle.com',
                'published_at' => Carbon::now()->subDays(10),
            ],
            [
                'customer_id' => 2,
                'title' => 'Smart Garden System',
                'slug' => 'smart-garden-system',
                'tagline' => 'Automated indoor gardening for everyone',
                'project_type' => 'product',
                'category' => 'technology',
                'description' => 'An AI-powered indoor garden system that automatically waters, lights, and monitors your plants.',
                'problem_solved' => 'Makes growing fresh herbs and vegetables easy for people without outdoor space.',
                'vision_mission' => 'To bring sustainable food production to every urban home.',
                'why_matters_now' => 'Food security and sustainability are increasingly important.',
                'funding_goal' => 75000.00,
                'minimum_contribution' => 25.00,
                'funding_model' => 'reward_based',
                'current_funded' => 15000.00,
                'backers_count' => 67,
                'funding_deadline' => Carbon::now()->addDays(25),
                'status' => 'active',
                'risk_level' => 'medium',
                'is_verified' => true,
                'is_featured' => false,
                'is_promoted' => true,
                'is_sponsored' => false,
                'country' => 'Canada',
                'region' => 'Ontario',
                'cover_image' => 'funding/smart-garden-cover.jpg',
                'pitch_video_url' => 'https://youtube.com/watch?v=smart-garden-demo',
                'team_members' => json_encode([
                    ['name' => 'Alex Kumar', 'role' => 'Founder', 'experience' => 'Agricultural tech specialist'],
                    ['name' => 'Lisa Park', 'role' => 'Lead Engineer', 'experience' => 'IoT and automation expert']
                ]),
                'use_of_funds' => json_encode([
                    'Tooling' => 40,
                    'Components' => 35,
                    'Software Development' => 20,
                    'Marketing' => 5
                ]),
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'customer_id' => 3,
                'title' => 'Community Art Space',
                'slug' => 'community-art-space',
                'tagline' => 'Bringing art to the neighborhood',
                'project_type' => 'community',
                'category' => 'creative_arts',
                'description' => 'A community-funded art space offering workshops, exhibitions, and studio space for local artists.',
                'problem_solved' => 'Lack of affordable art spaces and creative opportunities in underserved communities.',
                'vision_mission' => 'To make art accessible to everyone and support local creative talent.',
                'why_matters_now' => 'Arts funding is being cut while community need is growing.',
                'funding_goal' => 25000.00,
                'minimum_contribution' => 5.00,
                'funding_model' => 'donation',
                'current_funded' => 18500.00,
                'backers_count' => 189,
                'funding_deadline' => Carbon::now()->addDays(8),
                'status' => 'active',
                'risk_level' => 'low',
                'is_verified' => true,
                'is_featured' => false,
                'is_promoted' => false,
                'is_sponsored' => true,
                'country' => 'United Kingdom',
                'region' => 'Manchester',
                'cover_image' => 'funding/art-space-cover.jpg',
                'team_members' => json_encode([
                    ['name' => 'Emma Thompson', 'role' => 'Director', 'experience' => 'Gallery curator for 15 years'],
                    ['name' => 'James Wilson', 'role' => 'Operations', 'experience' => 'Community organizer']
                ]),
                'use_of_funds' => json_encode([
                    'Rent' => 50,
                    'Equipment' => 25,
                    'Programs' => 20,
                    'Utilities' => 5
                ]),
                'published_at' => Carbon::now()->subDays(12),
            ],
        ];

        foreach ($projects as $project) {
            DB::table('funding_projects')->insert(array_merge($project, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Funding Rewards
        $rewards = [
            [
                'funding_project_id' => 1,
                'title' => 'Early Bird Eco Bottle',
                'description' => 'Get the first eco-friendly water bottle at a special price',
                'minimum_contribution' => 25.00,
                'limit' => 100,
                'claimed_count' => 67,
                'estimated_delivery_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'funding_project_id' => 1,
                'title' => 'Eco Bottle + Filter Set',
                'description' => 'Water bottle plus extra filter cartridges',
                'minimum_contribution' => 50.00,
                'limit' => 50,
                'claimed_count' => 23,
                'estimated_delivery_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'funding_project_id' => 2,
                'title' => 'Smart Garden Basic',
                'description' => 'Basic smart garden system for growing herbs',
                'minimum_contribution' => 150.00,
                'limit' => 200,
                'claimed_count' => 45,
                'estimated_delivery_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'funding_project_id' => 2,
                'title' => 'Smart Garden Pro',
                'description' => 'Advanced system with larger growing capacity',
                'minimum_contribution' => 300.00,
                'limit' => 100,
                'claimed_count' => 12,
                'estimated_delivery_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'funding_project_id' => 3,
                'title' => 'Art Supporter',
                'description' => 'Your name on our supporter wall',
                'minimum_contribution' => 25.00,
                'limit' => null,
                'claimed_count' => 89,
                'estimated_delivery_date' => Carbon::now()->addMonths(1),
                'is_active' => true,
            ],
        ];

        foreach ($rewards as $reward) {
            DB::table('funding_rewards')->insert(array_merge($reward, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Funding Backers
        $backers = [
            [
                'funding_project_id' => 1,
                'customer_id' => 4,
                'amount' => 50.00,
                'status' => 'completed',
                'is_anonymous' => false,
                'funding_reward_id' => 2,
                'message' => 'Great initiative! Happy to support sustainable products.',
                'backed_at' => Carbon::now()->subDays(8),
            ],
            [
                'funding_project_id' => 1,
                'customer_id' => 5,
                'amount' => 25.00,
                'status' => 'completed',
                'is_anonymous' => true,
                'funding_reward_id' => 1,
                'message' => null,
                'backed_at' => Carbon::now()->subDays(5),
            ],
            [
                'funding_project_id' => 2,
                'customer_id' => 6,
                'amount' => 150.00,
                'status' => 'completed',
                'is_anonymous' => false,
                'funding_reward_id' => 3,
                'message' => 'Can\'t wait to start growing my own herbs!',
                'backed_at' => Carbon::now()->subDays(3),
            ],
            [
                'funding_project_id' => 3,
                'customer_id' => 7,
                'amount' => 50.00,
                'status' => 'pending',
                'is_anonymous' => false,
                'funding_reward_id' => 5,
                'message' => 'Supporting local arts is so important!',
                'backed_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($backers as $backer) {
            DB::table('funding_backers')->insert(array_merge($backer, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Funding Updates
        $updates = [
            [
                'funding_project_id' => 1,
                'title' => '50% Funded! Thank You!',
                'content' => 'We\'ve reached 50% of our funding goal thanks to your amazing support! The eco-bottles are going into production next week.',
                'images' => json_encode(['funding/production-start.jpg']),
                'update_type' => 'milestone',
                'is_public' => true,
            ],
            [
                'funding_project_id' => 2,
                'title' => 'Software Update',
                'content' => 'Our team has completed the mobile app interface. You can now monitor your garden from anywhere!',
                'images' => json_encode(['funding/app-interface.jpg']),
                'update_type' => 'progress',
                'is_public' => true,
            ],
            [
                'funding_project_id' => 3,
                'title' => 'Community Support',
                'content' => 'Local businesses have offered supplies and discounts for our art space. Community support has been overwhelming!',
                'update_type' => 'announcement',
                'is_public' => true,
            ],
        ];

        foreach ($updates as $update) {
            DB::table('funding_updates')->insert(array_merge($update, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Funding Upsells
        $upsells = [
            [
                'funding_project_id' => 1,
                'customer_id' => 1,
                'upsell_type' => 'featured',
                'price' => 100.00,
                'currency' => 'USD',
                'status' => 'active',
                'duration_days' => 7,
                'starts_at' => Carbon::now()->subDays(3),
                'ends_at' => Carbon::now()->addDays(4),
                'payment_reference' => 'txn_featured_001',
                'payment_date' => Carbon::now()->subDays(3),
            ],
            [
                'funding_project_id' => 2,
                'customer_id' => 2,
                'upsell_type' => 'promoted',
                'price' => 75.00,
                'currency' => 'USD',
                'status' => 'active',
                'duration_days' => 14,
                'starts_at' => Carbon::now()->subDays(1),
                'ends_at' => Carbon::now()->addDays(13),
                'payment_reference' => 'txn_promoted_002',
                'payment_date' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($upsells as $upsell) {
            DB::table('funding_upsells')->insert(array_merge($upsell, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Funding seeder completed successfully!');
    }
}
