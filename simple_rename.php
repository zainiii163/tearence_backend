<?php

/**
 * Simple table rename script using Laravel DB
 * Run with: php artisan tinker
 * Then paste this code
 */

// Tables that exist and need renaming (based on our verification)
$tablesToRename = [
    'ea_books' => 'books',
    'ea_services' => 'services',
    'ea_service_categories' => 'service_categories',
    'ea_service_media' => 'service_media',
    'ea_service_packages' => 'service_packages',
    'ea_service_addons' => 'service_addons',
    'ea_service_providers' => 'service_providers',
    'ea_service_promotions' => 'service_promotions',
    'ea_affiliate_links' => 'affiliate_links',
    'ea_affiliate_upsell_plans' => 'affiliate_upsell_plans',
    'ea_users' => 'users',
    'ea_customer' => 'customer',
    'ea_customer_business' => 'customer_business',
    'ea_customer_store' => 'customer_store',
    'ea_user_analytics' => 'user_analytics',
    'ea_venues' => 'venues',
    'ea_venue_services' => 'venue_services',
    'ea_events' => 'events',
    'ea_banner' => 'banner',
    'ea_listing' => 'listing',
    'ea_listing_analytics' => 'listing_analytics',
    'ea_listing_upsells' => 'listing_upsells',
    'ea_job_alerts' => 'job_alerts',
    'ea_job_upsells' => 'job_upsells',
    'ea_candidate_profiles' => 'candidate_profiles',
    'ea_candidate_upsells' => 'candidate_upsells',
    'ea_analytics_reports' => 'analytics_reports',
    'ea_dashboard_permissions' => 'dashboard_permissions',
    'ea_group' => 'group',
    'ea_blog' => 'blog',
    'ea_location' => 'location',
    'ea_revenue_tracking' => 'revenue_tracking',
    'ea_staff_management' => 'staff_management',
    'ea_system_analytics' => 'system_analytics',
    'ea_advertisement' => 'advertisement',
];

echo "Starting table renaming process...\n";

$renamed = 0;
$errors = 0;

foreach ($tablesToRename as $oldName => $newName) {
    try {
        // Check if old table exists
        $exists = DB::select("SHOW TABLES LIKE '$oldName'");
        
        if (empty($exists)) {
            echo "⏭️  $oldName (doesn't exist)\n";
            continue;
        }
        
        // Check if new table already exists
        $newExists = DB::select("SHOW TABLES LIKE '$newName'");
        
        if (!empty($newExists)) {
            echo "⚠️  $oldName (target $newName already exists)\n";
            continue;
        }
        
        // Rename the table
        DB::statement("RENAME TABLE `$oldName` TO `$newName`");
        echo "✅ $oldName -> $newName\n";
        $renamed++;
        
    } catch (\Exception $e) {
        echo "❌ $oldName -> $newName (Error: " . $e->getMessage() . ")\n";
        $errors++;
    }
}

echo "\nSummary:\n";
echo "Renamed: $renamed tables\n";
echo "Errors: $errors tables\n";

echo "\nVerifying with model test...\n";

try {
    $userCount = \App\Models\User::count();
    echo "✅ User model works: $userCount users\n";
} catch (\Exception $e) {
    echo "❌ User model error: " . $e->getMessage() . "\n";
}

try {
    $serviceCount = \App\Models\Service::count();
    echo "✅ Service model works: $serviceCount services\n";
} catch (\Exception $e) {
    echo "❌ Service model error: " . $e->getMessage() . "\n";
}

echo "\nProcess completed!\n";
