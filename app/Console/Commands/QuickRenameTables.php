<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QuickRenameTables extends Command
{
    protected $signature = 'db:quick-rename';
    protected $description = 'Quick rename essential tables';

    public function handle()
    {
        $tables = [
            'ea_books' => 'books',
            'ea_services' => 'services', 
            'ea_users' => 'users',
            'ea_customer' => 'customer',
            'ea_banner' => 'banner',
            'ea_listing' => 'listing',
            'ea_authors' => 'authors',
            'ea_affiliate_links' => 'affiliate_links',
            'ea_affiliate_posts' => 'affiliate_posts',
            'ea_affiliate_post_upsells' => 'affiliate_post_upsells',
            'ea_affiliate_upsell_plans' => 'affiliate_upsell_plans',
            'ea_service_categories' => 'service_categories',
            'ea_service_media' => 'service_media',
            'ea_service_packages' => 'service_packages',
            'ea_service_addons' => 'service_addons',
            'ea_service_providers' => 'service_providers',
            'ea_service_promotions' => 'service_promotions',
            'ea_customer_business' => 'customer_business',
            'ea_customer_store' => 'customer_store',
            'ea_user_analytics' => 'user_analytics',
            'ea_venues' => 'venues',
            'ea_venue_services' => 'venue_services',
            'ea_events' => 'events',
            'ea_banner_ads' => 'banner_ads',
            'ea_banner_categories' => 'banner_categories',
            'ea_listing_analytics' => 'listing_analytics',
            'ea_listing_upsells' => 'listing_upsells',
            'ea_job_alerts' => 'job_alerts',
            'ea_job_upsells' => 'job_upsells',
            'ea_candidate_profiles' => 'candidate_profiles',
            'ea_candidate_upsells' => 'candidate_upsells',
            'ea_resorts_travel_adverts' => 'resorts_travel_adverts',
            'ea_resorts_travel_categories' => 'resorts_travel_categories',
            'ea_analytics_reports' => 'analytics_reports',
            'ea_dashboard_permissions' => 'dashboard_permissions',
            'ea_group' => 'group',
            'ea_blog' => 'blog',
            'ea_location' => 'location',
            'ea_revenue_tracking' => 'revenue_tracking',
            'ea_staff_management' => 'staff_management',
            'ea_system_analytics' => 'system_analytics',
            'ea_advertisement' => 'advertisement'
        ];

        $this->info('Starting quick table rename...');
        
        $renamed = 0;
        $errors = 0;

        foreach ($tables as $old => $new) {
            try {
                $exists = DB::select("SHOW TABLES LIKE '$old'");
                
                if (empty($exists)) {
                    $this->line("⏭️  $old (doesn't exist)");
                    continue;
                }
                
                $newExists = DB::select("SHOW TABLES LIKE '$new'");
                
                if (!empty($newExists)) {
                    $this->line("⚠️  $old (target $new already exists)");
                    continue;
                }
                
                DB::statement("RENAME TABLE `$old` TO `$new`");
                $this->info("✅ $old -> $new");
                $renamed++;
                
            } catch (\Exception $e) {
                $this->error("❌ $old -> $new (" . $e->getMessage() . ")");
                $errors++;
            }
        }

        $this->info("\nSummary:");
        $this->info("Renamed: $renamed tables");
        $this->info("Errors: $errors tables");

        // Test basic models
        try {
            $userCount = \App\Models\User::count();
            $this->info("✅ User model: $userCount users");
        } catch (\Exception $e) {
            $this->error("❌ User model error: " . $e->getMessage());
        }

        return 0;
    }
}
