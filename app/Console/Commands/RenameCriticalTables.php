<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RenameCriticalTables extends Command
{
    protected $signature = 'db:rename-critical {--force : Force rename even if there are issues}';
    protected $description = 'Rename critical tables for application to work';

    public function handle()
    {
        $this->info('🚨 Renaming critical tables...');
        $this->info('================================');

        // Critical tables that MUST exist for the app to work
        $criticalTables = [
            'ea_users' => 'users',
            'ea_books' => 'books',
            'ea_services' => 'services',
            'ea_customer' => 'customer',
            'ea_authors' => 'authors',
            'ea_banner' => 'banner',
            'ea_listing' => 'listing',
        ];

        // Secondary important tables
        $secondaryTables = [
            'ea_service_categories' => 'service_categories',
            'ea_service_media' => 'service_media',
            'ea_service_packages' => 'service_packages',
            'ea_affiliate_posts' => 'affiliate_posts',
            'ea_affiliate_post_upsells' => 'affiliate_post_upsells',
            'ea_banner_categories' => 'banner_categories',
            'ea_banner_ads' => 'banner_ads',
            'ea_resorts_travel_adverts' => 'resorts_travel_adverts',
            'ea_resorts_travel_categories' => 'resorts_travel_categories',
        ];

        $allTables = array_merge($criticalTables, $secondaryTables);
        $force = $this->option('force');

        $renamed = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($allTables as $old => $new) {
            $isCritical = isset($criticalTables[$old]);
            $prefix = $isCritical ? "🔥" : "📋";
            
            try {
                // Check if old table exists
                $exists = DB::select("SHOW TABLES LIKE '$old'");
                
                if (empty($exists)) {
                    $this->line("$prefix ⏭️  $old (doesn't exist)");
                    $skipped++;
                    continue;
                }
                
                // Check if new table already exists
                $newExists = DB::select("SHOW TABLES LIKE '$new'");
                
                if (!empty($newExists)) {
                    $this->line("$prefix ⚠️  $old (target $new already exists)");
                    $skipped++;
                    continue;
                }
                
                // Get table info before rename
                $status = DB::select("SHOW TABLE STATUS LIKE '$old'");
                $info = $status[0];
                
                if (!$force && ($info->Engine === null || $info->Rows === null)) {
                    $this->warn("$prefix ⚠️  $old has engine issues (use --force to override)");
                    $skipped++;
                    continue;
                }
                
                // Rename the table
                DB::statement("RENAME TABLE `$old` TO `$new`");
                $this->info("$prefix ✅ $old -> $new ({$info->Engine}, {$info->Rows} rows)");
                $renamed++;
                
            } catch (\Exception $e) {
                $this->error("$prefix ❌ $old -> $new (" . $e->getMessage() . ")");
                $errors++;
            }
        }

        $this->info("\n📊 Summary:");
        $this->info("Renamed: $renamed tables");
        $this->info("Skipped: $skipped tables");
        $this->info("Errors: $errors tables");

        // Test critical models
        $this->info("\n🧪 Testing critical models:");
        
        $criticalModels = [
            'User' => 'users',
            'Book' => 'books', 
            'Service' => 'services',
            'Customer' => 'customer'
        ];

        foreach ($criticalModels as $model => $table) {
            try {
                $modelClass = "App\\Models\\$model";
                $count = $modelClass::count();
                $this->info("✅ $model model works: $count records");
            } catch (\Exception $e) {
                $this->error("❌ $model model error: " . $e->getMessage());
            }
        }

        if ($errors > 0) {
            $this->warn("\n⚠️  Some tables couldn't be renamed. You may need to:");
            $this->warn("1. Check MySQL storage engine configuration");
            $this->warn("2. Run: php artisan db:rename-critical --force");
            $this->warn("3. Manually handle problematic tables");
        }

        return $errors > 0 ? 1 : 0;
    }
}
