<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixServiceMigrations extends Command
{
    protected $signature = 'db:fix-service-migrations';
    protected $description = 'Fix foreign key issues in service migrations';

    public function handle()
    {
        $this->info('🔧 Fixing service migrations...');
        
        $migrationFiles = [
            '2026_03_07_000003_create_service_activities_table.php',
            '2026_03_07_000004_create_service_saved_table.php',
            '2026_03_07_201432_create_service_packages_table.php',
            '2026_03_07_201439_create_service_media_table.php',
        ];

        $migrationsPath = database_path('migrations/');
        $fixed = 0;

        foreach ($migrationFiles as $file) {
            $filePath = $migrationsPath . $file;
            
            if (!File::exists($filePath)) {
                $this->line("⏭️  $file (not found)");
                continue;
            }

            $content = File::get($filePath);
            $originalContent = $content;

            // Replace unsignedInteger with unsignedBigInteger for service_id
            $content = preg_replace(
                '/\$table->unsignedInteger\(\'service_id\'\)/',
                '$table->unsignedBigInteger(\'service_id\')',
                $content
            );

            // Remove foreign key constraints for services
            $content = preg_replace(
                '/\$table->foreign\(\'service_id\'\)->references\(\'id\'\)->on\(\'services\'\)->onDelete\(\'cascade\'\);[\s\n]*\/\/ Indexes/',
                '// Indexes',
                $content
            );

            if ($content !== $originalContent) {
                File::put($filePath, $content);
                $this->info("✅ Fixed $file");
                $fixed++;
            } else {
                $this->line("⏭️  $file (no changes needed)");
            }
        }

        $this->info("\n📊 Fixed $fixed migration files");
        $this->info("Now run: php artisan migrate:fresh");

        return 0;
    }
}
