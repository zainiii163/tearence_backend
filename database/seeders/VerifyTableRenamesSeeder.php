<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyTableRenamesSeeder extends Seeder
{
    /**
     * Run the database seeds to verify table renames.
     */
    public function run()
    {
        $this->command->info('🔍 Verifying table renames...');
        $this->command->info('================================');

        // Expected table names (without ea_ prefix)
        $expectedTables = [
            // Books tables
            'books', 'book_categories', 'book_purchases', 'book_saves', 'book_upsells',
            
            // Service tables
            'services', 'service_categories', 'service_media', 'service_packages',
            'service_addons', 'service_providers', 'service_promotions',
            
            // Affiliate tables
            'affiliate_links', 'affiliate_posts', 'affiliate_post_upsells', 'affiliate_upsell_plans',
            
            // User and customer tables
            'users', 'customer', 'customer_business', 'customer_store', 'user_analytics',
            
            // Venue and event tables
            'venues', 'venue_services', 'events',
            
            // Banner tables
            'banner', 'banner_ads', 'banner_categories',
            
            // Listing tables
            'listing', 'listing_analytics', 'listing_favorite', 'listing_image', 'listing_upsells',
            
            // Job tables
            'job_alerts', 'job_upsells',
            
            // Candidate tables
            'candidate_profiles', 'candidate_upsells',
            
            // Resorts and travel tables
            'resorts_travel_adverts', 'resorts_travel_categories',
            
            // Other tables
            'authors', 'analytics_reports', 'campaign', 'dashboard_permissions',
            'donor', 'group', 'blog', 'location', 'revenue_tracking',
            'staff_management', 'system_analytics', 'advertisement',
        ];

        // Get current tables
        $currentTables = DB::select('SHOW TABLES');
        $currentTableNames = array_map('current', $currentTables);

        $found = 0;
        $missing = [];

        foreach ($expectedTables as $table) {
            if (in_array($table, $currentTableNames)) {
                $this->command->info("✅ $table");
                $found++;
            } else {
                $this->command->error("❌ $table (missing)");
                $missing[] = $table;
            }
        }

        // Check for any remaining ea_ tables
        $remainingEaTables = array_filter($currentTableNames, function($table) {
            return str_starts_with($table, 'ea_');
        });

        $this->command->info('================================');
        $this->command->info("Found: $found/" . count($expectedTables) . " expected tables");

        if (!empty($missing)) {
            $this->command->error("Missing " . count($missing) . " tables:");
            foreach ($missing as $table) {
                $this->command->error("  - $table");
            }
        }

        if (!empty($remainingEaTables)) {
            $this->command->warn("Found " . count($remainingEaTables) . " remaining ea_ tables:");
            foreach ($remainingEaTables as $table) {
                $this->command->warn("  - $table");
            }
        } else {
            $this->command->info("✅ No ea_ prefixed tables remaining!");
        }

        // Test basic model functionality
        $this->testModelFunctionality();

        $this->command->info('================================');
        $this->command->info('Verification completed!');
    }

    /**
     * Test basic model functionality
     */
    protected function testModelFunctionality()
    {
        $this->command->info("\n🧪 Testing model functionality...");

        try {
            // Test User model
            $userCount = \App\Models\User::count();
            $this->command->info("✅ User model: $userCount records");

            // Test Service model
            $serviceCount = \App\Models\Service::count();
            $this->command->info("✅ Service model: $serviceCount records");

            // Test Book model
            $bookCount = \App\Models\Book::count();
            $this->command->info("✅ Book model: $bookCount records");

            // Test Customer model
            $customerCount = \App\Models\Customer::count();
            $this->command->info("✅ Customer model: $customerCount records");

        } catch (\Exception $e) {
            $this->command->error("❌ Model test failed: " . $e->getMessage());
        }
    }
}
