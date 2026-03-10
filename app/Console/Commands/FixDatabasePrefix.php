<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDatabasePrefix extends Command
{
    protected $signature = 'db:fix-prefix';
    protected $description = 'Fix database prefix issues and recreate tables';

    public function handle()
    {
        $this->info('🔧 Fixing database prefix issues...');
        $this->info('================================');

        // Check current database prefix
        $connection = DB::connection();
        $prefix = $connection->getTablePrefix();
        $this->info("Current database prefix: '$prefix'");

        // Show all tables without prefix
        $this->info("\n📋 All tables in database:");
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $this->line("  - $tableName");
        }

        // Try to access tables directly without prefix
        $this->info("\n🧪 Testing direct table access:");
        
        $testTables = ['ea_books', 'ea_services', 'ea_users', 'ea_customer'];
        
        foreach ($testTables as $table) {
            try {
                // Use raw SQL to bypass Laravel's prefix handling
                $result = DB::select("SELECT COUNT(*) as count FROM `$table`");
                $count = $result[0]->count;
                $this->info("✅ $table: $count records");
            } catch (\Exception $e) {
                $this->error("❌ $table: " . $e->getMessage());
            }
        }

        // Try to create new tables without prefix issues
        $this->info("\n🔧 Creating clean tables:");
        
        $recreations = [
            'ea_books' => 'books',
            'ea_services' => 'services',
            'ea_customer' => 'customer',
        ];

        foreach ($recreations as $old => $new) {
            try {
                // Check if old table exists and has data
                $result = DB::select("SELECT COUNT(*) as count FROM `$old`");
                $count = $result[0]->count;
                
                if ($count > 0) {
                    // Drop new table if it exists
                    DB::statement("DROP TABLE IF EXISTS `$new`");
                    
                    // Create new table with same structure and data
                    DB::statement("CREATE TABLE `$new` LIKE `$old`");
                    DB::statement("INSERT INTO `$new` SELECT * FROM `$old`");
                    
                    $this->info("✅ $old -> $new ($count records copied)");
                } else {
                    $this->line("⏭️  $old (no data)");
                }
                
            } catch (\Exception $e) {
                $this->error("❌ $old -> $new: " . $e->getMessage());
            }
        }

        // Test the new tables
        $this->info("\n🧪 Testing new tables:");
        
        foreach ($recreations as $old => $new) {
            try {
                $result = DB::select("SELECT COUNT(*) as count FROM `$new`");
                $count = $result[0]->count;
                $this->info("✅ $new: $count records");
            } catch (\Exception $e) {
                $this->error("❌ $new: " . $e->getMessage());
            }
        }

        return 0;
    }
}
