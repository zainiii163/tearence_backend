<?php

/**
 * Database Table Renaming Script
 * 
 * This script removes the 'ea_' prefix from all database tables
 * Run this script in your Laravel environment: php artisan tinker
 * Or execute it directly: php rename_tables.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Table mappings: old_name => new_name
$tableMappings = [
    // Books tables
    'ea_books' => 'books',
    'ea_book_categories' => 'book_categories',
    'ea_book_purchases' => 'book_purchases',
    'ea_book_saves' => 'book_saves',
    'ea_book_upsells' => 'book_upsells',

    // Service tables
    'ea_services' => 'services',
    'ea_service_categories' => 'service_categories',
    'ea_service_media' => 'service_media',
    'ea_service_packages' => 'service_packages',
    'ea_service_addons' => 'service_addons',
    'ea_service_providers' => 'service_providers',
    'ea_service_promotions' => 'service_promotions',

    // Affiliate tables
    'ea_affiliate_links' => 'affiliate_links',
    'ea_affiliate_posts' => 'affiliate_posts',
    'ea_affiliate_post_upsells' => 'affiliate_post_upsells',
    'ea_affiliate_upsell_plans' => 'affiliate_upsell_plans',

    // User and customer tables
    'ea_users' => 'users',
    'ea_customer' => 'customer',
    'ea_customer_business' => 'customer_business',
    'ea_customer_store' => 'customer_store',
    'ea_user_analytics' => 'user_analytics',

    // Venue and event tables
    'ea_venues' => 'venues',
    'ea_venue_services' => 'venue_services',
    'ea_events' => 'events',

    // Banner tables
    'ea_banner' => 'banner',
    'ea_banner_ads' => 'banner_ads',
    'ea_banner_categories' => 'banner_categories',

    // Listing tables
    'ea_listing' => 'listing',
    'ea_listing_analytics' => 'listing_analytics',
    'ea_listing_favorite' => 'listing_favorite',
    'ea_listing_image' => 'listing_image',
    'ea_listing_upsells' => 'listing_upsells',

    // Job tables
    'ea_job_alerts' => 'job_alerts',
    'ea_job_upsells' => 'job_upsells',

    // Candidate tables
    'ea_candidate_profiles' => 'candidate_profiles',
    'ea_candidate_upsells' => 'candidate_upsells',

    // Resorts and travel tables
    'ea_resorts_travel_adverts' => 'resorts_travel_adverts',
    'ea_resorts_travel_categories' => 'resorts_travel_categories',

    // Other tables
    'ea_authors' => 'authors',
    'ea_analytics_reports' => 'analytics_reports',
    'ea_campaign' => 'campaign',
    'ea_dashboard_permissions' => 'dashboard_permissions',
    'ea_donor' => 'donor',
    'ea_group' => 'group',
    'ea_blog' => 'blog',
    'ea_location' => 'location',
    'ea_revenue_tracking' => 'revenue_tracking',
    'ea_staff_management' => 'staff_management',
    'ea_system_analytics' => 'system_analytics',
    'ea_advertisement' => 'advertisement',
];

echo "Starting database table renaming process...\n";
echo "========================================\n";

try {
    // Get all current tables
    $currentTables = DB::select('SHOW TABLES');
    $currentTableNames = array_map('current', $currentTables);

    echo "Found " . count($currentTableNames) . " tables in database\n\n";

    $renamedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;

    foreach ($tableMappings as $oldName => $newName) {
        echo "Processing: $oldName -> $newName ... ";

        // Check if old table exists
        if (!in_array($oldName, $currentTableNames)) {
            echo "SKIPPED (table doesn't exist)\n";
            $skippedCount++;
            continue;
        }

        // Check if new table already exists
        if (in_array($newName, $currentTableNames)) {
            echo "SKIPPED (target table already exists)\n";
            $skippedCount++;
            continue;
        }

        try {
            // Rename the table
            DB::statement("RENAME TABLE `$oldName` TO `$newName`");
            echo "SUCCESS\n";
            $renamedCount++;
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }

    echo "\n========================================\n";
    echo "Renaming Summary:\n";
    echo "Successfully renamed: $renamedCount tables\n";
    echo "Skipped: $skippedCount tables\n";
    echo "Errors: $errorCount tables\n";

    if ($errorCount > 0) {
        echo "\n⚠️  Some tables couldn't be renamed. Please check the errors above.\n";
        echo "You may need to manually handle foreign key constraints or other dependencies.\n";
    } else {
        echo "\n✅ All table renames completed successfully!\n";
    }

    // Update foreign key constraints if needed
    echo "\nChecking for foreign key constraints that need updating...\n";
    
    $constraints = DB::select("
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
        AND REFERENCED_TABLE_NAME LIKE 'ea_%'
    ");

    if (count($constraints) > 0) {
        echo "Found " . count($constraints) . " foreign key constraints referencing old table names:\n";
        foreach ($constraints as $constraint) {
            echo "- Table: {$constraint->TABLE_NAME}, Constraint: {$constraint->CONSTRAINT_NAME}\n";
            echo "  References: {$constraint->REFERENCED_TABLE_NAME}({$constraint->COLUMN_NAME})\n";
        }
        echo "\n⚠️  You'll need to manually update these foreign key constraints.\n";
    } else {
        echo "No foreign key constraints need updating.\n";
    }

} catch (\Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Please check your database connection and permissions.\n";
}

echo "\n========================================\n";
echo "Process completed.\n";
