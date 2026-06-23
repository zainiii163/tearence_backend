<?php

// Fix affiliate migrations by marking them as ran in the migrations table
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

echo "Fixing affiliate migrations...\n";

$migrations = [
    '2026_03_08_000001_create_affiliate_posts_table',
    '2026_03_08_000002_create_affiliate_upsell_plans_table', 
    '2026_03_08_000003_create_affiliate_post_upsells_table',
    '2026_03_10_140000_create_affiliate_categories_table',
    '2026_03_10_140001_create_business_affiliate_offers_table',
    '2026_03_10_140002_create_user_affiliate_posts_table',
    '2026_03_10_140004_create_affiliate_post_upsells_table',
    '2026_03_10_140005_create_affiliate_analytics_table',
    '2026_03_10_140006_create_affiliate_applications_table',
    '2026_04_02_174200_fix_affiliate_upsell_plans_table',
    '2026_04_02_174300_add_top_category_placement_to_affiliate_upsell_plans'
];

foreach ($migrations as $migration) {
    try {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => 1
        ]);
        echo "✓ Marked $migration as ran\n";
    } catch (\Exception $e) {
        echo "- $migration already exists\n";
    }
}

echo "Migration fix complete!\n";
