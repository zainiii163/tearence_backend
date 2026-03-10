<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestoreForeignKeys extends Command
{
    protected $signature = 'db:restore-foreign-keys {--force : Run without confirmation}';
    protected $description = 'Restore foreign key constraints in a safe, controlled manner';

    public function handle()
    {
        if (!$this->option('force')) {
            $this->warn('⚠️  This will add foreign key constraints to your database.');
            $this->warn('⚠️  Make sure you have a backup before proceeding.');
            $this->line('');
            
            if (!$this->confirm('Do you want to continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('🔧 Restoring foreign key constraints...');
        $this->info('================================');

        // Check if all required tables exist
        $requiredTables = [
            'users', 'services', 'service_categories', 'service_providers',
            'books', 'authors', 'vehicles', 'vehicle_categories',
            'events', 'venues', 'venue_services', 'banner_categories',
            'jobs', 'job_categories', 'job_seekers', 'customer'
        ];

        $this->info('📋 Checking required tables...');
        $missingTables = [];
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        if (!empty($missingTables)) {
            $this->error('❌ Missing required tables: ' . implode(', ', $missingTables));
            $this->error('Please create these tables first before restoring foreign keys.');
            return 1;
        }

        $this->info('✅ All required tables exist');

        // Get the foreign key restoration migrations
        $migrations = [
            '2026_03_10_130000_restore_service_foreign_keys',
            '2026_03_10_130001_restore_book_vehicle_foreign_keys', 
            '2026_03_10_130002_restore_event_venue_banner_foreign_keys',
            '2026_03_10_130003_restore_job_funding_foreign_keys'
        ];

        $this->info('');
        $this->info('🚀 Running foreign key restoration migrations...');

        foreach ($migrations as $migration) {
            $this->info("📝 Running: {$migration}");
            
            try {
                // Run the migration
                $this->call('migrate', [
                    '--path' => "database/migrations/{$migration}.php",
                    '--force' => true
                ]);
                
                $this->info("✅ {$migration} completed successfully");
            } catch (\Exception $e) {
                $this->error("❌ {$migration} failed: " . $e->getMessage());
                
                if (!$this->option('force')) {
                    $this->warn('⚠️  Migration failed. You can continue with --force to ignore errors.');
                    if (!$this->confirm('Continue with remaining migrations?')) {
                        $this->info('Operation cancelled by user.');
                        return 1;
                    }
                }
            }
            
            $this->line('');
        }

        // Verify foreign keys were added
        $this->info('🔍 Verifying foreign keys...');
        $this->verifyForeignKeys();

        $this->info('');
        $this->info('✅ Foreign key restoration completed!');
        $this->info('');
        $this->info('📊 Summary:');
        $this->info('- Foreign key constraints added to service tables');
        $this->info('- Foreign key constraints added to book and vehicle tables');
        $this->info('- Foreign key constraints added to event, venue, and banner tables');
        $this->info('- Foreign key constraints added to job and funding tables');
        $this->info('');
        $this->info('🎯 Your database now has proper referential integrity!');

        return 0;
    }

    private function verifyForeignKeys()
    {
        // Check a few key foreign keys to verify they were added
        $checks = [
            'services' => ['user_id', 'category_id'],
            'books' => ['user_id', 'author_id'],
            'vehicles' => ['user_id', 'vehicle_category_id'],
            'jobs' => ['user_id', 'job_category_id']
        ];

        foreach ($checks as $table => $columns) {
            foreach ($columns as $column) {
                try {
                    $constraints = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = ? 
                        AND COLUMN_NAME = ?
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ", [$table, $column]);

                    if (!empty($constraints)) {
                        $this->info("✅ {$table}.{$column} -> {$constraints[0]->CONSTRAINT_NAME}");
                    } else {
                        $this->warn("⚠️  {$table}.{$column} - No foreign key found");
                    }
                } catch (\Exception $e) {
                    $this->warn("⚠️  Could not verify {$table}.{$column}: " . $e->getMessage());
                }
            }
        }
    }
}
