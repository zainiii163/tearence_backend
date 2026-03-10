<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecreateTables extends Command
{
    protected $signature = 'db:recreate-tables {--force : Force recreation}';
    protected $description = 'Recreate tables using CREATE TABLE AS SELECT to bypass storage engine issues';

    public function handle()
    {
        $this->info('🔧 Recreating problematic tables...');
        $this->info('================================');

        // Tables that need recreation
        $problemTables = [
            'ea_books' => 'books',
            'ea_services' => 'services',
            'ea_customer' => 'customer',
            'ea_banner' => 'banner',
            'ea_listing' => 'listing',
            'ea_service_packages' => 'service_packages',
            'ea_users' => 'users',
        ];

        $force = $this->option('force');
        $recreated = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($problemTables as $old => $new) {
            try {
                // Check if old table exists
                $exists = DB::select("SHOW TABLES LIKE '$old'");
                
                if (empty($exists)) {
                    $this->line("⏭️  $old (doesn't exist)");
                    $skipped++;
                    continue;
                }
                
                // Check if new table already exists
                $newExists = DB::select("SHOW TABLES LIKE '$new'");
                
                if (!empty($newExists) && !$force) {
                    $this->line("⚠️  $old (target $new already exists)");
                    $skipped++;
                    continue;
                }
                
                // Drop new table if it exists and force is enabled
                if (!empty($newExists) && $force) {
                    DB::statement("DROP TABLE IF EXISTS `$new`");
                    $this->line("🗑️  Dropped existing $new");
                }
                
                // Get row count before recreation
                $count = DB::table($old)->count();
                
                // Recreate table using CREATE TABLE AS SELECT
                DB::statement("CREATE TABLE `$new` AS SELECT * FROM `$old`");
                
                // Verify the new table
                $newCount = DB::table($new)->count();
                
                $this->info("✅ $old -> $new ($count rows recreated)");
                $recreated++;
                
                // Optionally drop the old table after successful recreation
                if ($force) {
                    DB::statement("DROP TABLE `$old`");
                    $this->line("🗑️  Dropped original $old");
                }
                
            } catch (\Exception $e) {
                $this->error("❌ $old -> $new (" . $e->getMessage() . ")");
                $errors++;
            }
        }

        $this->info("\n📊 Summary:");
        $this->info("Recreated: $recreated tables");
        $this->info("Skipped: $skipped tables");
        $this->info("Errors: $errors tables");

        // Test the recreated tables
        $this->info("\n🧪 Testing recreated tables:");
        
        $testTables = ['users', 'books', 'services', 'customer'];
        
        foreach ($testTables as $table) {
            try {
                $count = DB::table($table)->count();
                $this->info("✅ $table: $count records");
            } catch (\Exception $e) {
                $this->error("❌ $table: " . $e->getMessage());
            }
        }

        // Test models
        $this->info("\n🧪 Testing models:");
        
        $models = ['User', 'Book', 'Service', 'Customer'];
        
        foreach ($models as $model) {
            try {
                $modelClass = "App\\Models\\$model";
                $count = $modelClass::count();
                $this->info("✅ $model model: $count records");
            } catch (\Exception $e) {
                $this->error("❌ $model model: " . $e->getMessage());
            }
        }

        if ($recreated > 0) {
            $this->info("\n💡 Next steps:");
            $this->info("1. Test your application");
            $this->info("2. If everything works, run: php artisan db:recreate-tables --force");
            $this->info("3. This will drop the old ea_ tables");
        }

        return $errors > 0 ? 1 : 0;
    }
}
