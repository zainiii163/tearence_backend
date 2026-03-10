<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDatabaseStatus extends Command
{
    protected $signature = 'db:check-status';
    protected $description = 'Check database table status and identify issues';

    public function handle()
    {
        $this->info('🔍 Checking database status...');
        $this->info('================================');

        try {
            $tables = DB::select('SHOW TABLES');
            $this->info("Total tables: " . count($tables));
            
            $eaTables = [];
            $cleanTables = [];
            
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                if (str_starts_with($tableName, 'ea_')) {
                    $eaTables[] = $tableName;
                } else {
                    $cleanTables[] = $tableName;
                }
            }
            
            $this->info("\n📋 EA prefixed tables (" . count($eaTables) . "):");
            foreach ($eaTables as $table) {
                $this->line("  - $table");
            }
            
            $this->info("\n✅ Clean tables (" . count($cleanTables) . "):");
            foreach ($cleanTables as $table) {
                $this->line("  - $table");
            }
            
            // Check specific important tables
            $this->info("\n🎯 Checking important tables:");
            $importantTables = ['ea_users', 'users', 'ea_books', 'books', 'ea_services', 'services'];
            
            foreach ($importantTables as $table) {
                try {
                    $status = DB::select("SHOW TABLE STATUS LIKE '$table'");
                    if (!empty($status)) {
                        $info = $status[0];
                        $this->line("  $table: {$info->Engine} engine, {$info->Rows} rows");
                    } else {
                        $this->line("  $table: NOT FOUND");
                    }
                } catch (\Exception $e) {
                    $this->error("  $table: ERROR - " . $e->getMessage());
                }
            }
            
            // Check if we can create a simple test table
            $this->info("\n🧪 Testing table creation...");
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS test_rename (id INT PRIMARY KEY)");
                $this->info("✅ Can create tables");
                
                // Test rename
                DB::statement("RENAME TABLE test_rename TO test_rename_new");
                $this->info("✅ Can rename tables");
                
                // Clean up
                DB::statement("DROP TABLE IF EXISTS test_rename_new");
                $this->info("✅ Can drop tables");
                
            } catch (\Exception $e) {
                $this->error("❌ Table operations failed: " . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            $this->error("Database error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
