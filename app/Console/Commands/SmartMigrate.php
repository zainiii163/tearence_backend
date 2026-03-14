<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SmartMigrate extends Command
{
    protected $signature = 'migrate:smart';
    protected $description = 'Run migrations while automatically handling duplicate tables';

    public function handle()
    {
        $this->info('🚀 Running smart migrations...');
        
        // Get all pending migrations
        $pendingMigrations = $this->getPendingMigrations();
        $processed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($pendingMigrations as $migration) {
            try {
                // Check if this is a duplicate table migration
                if ($this->isDuplicateTable($migration)) {
                    $this->line("⏭️  Skipping {$migration} (table already exists)");
                    $this->markMigrationAsRun($migration);
                    $skipped++;
                    $processed++;
                    continue;
                }

                // Run the migration
                $this->call('migrate', [
                    '--path' => "database/migrations/{$migration}.php",
                    '--force' => true
                ]);
                
                $this->info("✅ Processed {$migration}");
                $processed++;
                
            } catch (\Exception $e) {
                // Check if it's a duplicate table error
                if (str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'Base table or view already exists')) {
                    $this->line("⏭️  Skipping {$migration} (table already exists)");
                    $this->markMigrationAsRun($migration);
                    $skipped++;
                    $processed++;
                } else {
                    $this->error("❌ Error in {$migration}: " . $e->getMessage());
                    $errors++;
                }
            }
        }

        $this->info("\n📊 Migration Summary:");
        $this->info("✅ Processed: {$processed}");
        $this->info("⏭️  Skipped: {$skipped}");
        $this->info("❌ Errors: {$errors}");

        // Run seeders at the end
        if ($errors === 0) {
            $this->info("\n🌱 Running seeders...");
            $this->call('db:seed', ['--force' => true]);
        }

        return $errors === 0 ? 0 : 1;
    }

    private function getPendingMigrations()
    {
        $migrations = glob(database_path('migrations/*.php'));
        $pending = [];
        
        foreach ($migrations as $migration) {
            $fileName = basename($migration, '.php');
            
            // Check if migration hasn't run yet
            $exists = DB::table('migrations')
                ->where('migration', $fileName)
                ->exists();
                
            if (!$exists) {
                $pending[] = $fileName;
            }
        }
        
        sort($pending);
        return $pending;
    }

    private function isDuplicateTable($migration)
    {
        // List of migrations that create tables which might already exist
        $duplicateMigrations = [
            '2026_03_09_000000_create_resorts_travel_tables',
            '2026_03_09_174531_create_banner_categories_table',
            '2026_03_10_000001_create_vehicle_categories_table',
            '2026_03_10_000003_create_vehicles_table',
            '2026_03_10_120000_create_books_table',
            '2026_03_10_120000_create_promoted_adverts_table',
        ];

        return in_array($migration, $duplicateMigrations);
    }

    private function markMigrationAsRun($migration)
    {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
    }
}
