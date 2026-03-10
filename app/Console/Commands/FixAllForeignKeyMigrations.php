<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixAllForeignKeyMigrations extends Command
{
    protected $signature = 'db:fix-all-foreign-keys';
    protected $description = 'Fix all foreign key constraint issues in migrations';

    public function handle()
    {
        $this->info('🔧 Fixing all foreign key constraint issues...');
        
        $migrationsPath = database_path('migrations/');
        $problematicMigrations = [
            // Service related
            '2026_03_07_201138_create_service_providers_table.php',
            '2026_03_07_201412_create_services_table.php',
            '2026_03_09_174711_create_banners_table.php',
            '2026_03_09_180300_create_service_addons_table.php',
            '2026_03_09_181057_create_service_promotions_table.php',
            '2026_03_09_190000_update_banner_table_for_full_features.php',
            '2026_03_07_194013_create_event_venue_service_table.php',
            
            // Books related
            '2026_03_10_120000_create_books_table.php',
            '2024_02_01_030521_create_books_table.php',
            '2026_03_08_100000_create_authors_table.php',
            '2026_03_08_100002_create_book_upsells_table.php',
            '2026_03_08_100003_create_book_saves_table.php',
            
            // User related
            '2026_03_10_120001_create_promoted_adverts_table.php',
            '2026_03_10_120002_create_promoted_advert_analytics_table.php',
            '2026_03_10_120001_create_promoted_advert_favorites_table.php',
            '2026_03_08_200000_create_sponsored_adverts_table.php',
            '2026_03_08_200002_create_sponsored_advert_ratings_table.php',
            
            // Other
            '2026_03_07_205714_create_banner_ads_table.php',
            '2026_03_08_100002_create_property_analytics_table.php',
            '2026_03_08_100003_create_property_saved_table.php',
            '2026_03_08_000001_create_resorts_travel_adverts_table.php',
            '2026_03_09_000000_create_resorts_travel_tables.php',
            '2026_03_07_211230_create_vehicles_table.php',
        ];

        $fixed = 0;

        foreach ($problematicMigrations as $file) {
            $filePath = $migrationsPath . $file;
            
            if (!File::exists($filePath)) {
                $this->line("⏭️  $file (not found)");
                continue;
            }

            $content = File::get($filePath);
            $originalContent = $content;

            // Replace foreignId with unsignedBigInteger and remove constraints
            $content = preg_replace_callback(
                '/\$table->foreignId\(([^)]+)\)->constrained\(([^)]+)\)->onDelete\([^)]+\);/',
                function ($matches) {
                    $column = trim($matches[1], "'");
                    return "\$table->unsignedBigInteger($column);";
                },
                $content
            );

            // Replace foreignId without constraints
            $content = preg_replace_callback(
                '/\$table->foreignId\(([^)]+)\)->nullable\(\)->constrained\(([^)]+)\)->onDelete\([^)]+\);/',
                function ($matches) {
                    $column = trim($matches[1], "'");
                    return "\$table->unsignedBigInteger($column)->nullable();";
                },
                $content
            );

            // Replace foreignId without nullable
            $content = preg_replace_callback(
                '/\$table->foreignId\(([^)]+)\)->constrained\(([^)]+)\)->onDelete\([^)]+\);/',
                function ($matches) {
                    $column = trim($matches[1], "'");
                    return "\$table->unsignedBigInteger($column);";
                },
                $content
            );

            // Replace foreignId with just constrained
            $content = preg_replace_callback(
                '/\$table->foreignId\(([^)]+)\)->constrained\(\);/',
                function ($matches) {
                    $column = trim($matches[1], "'");
                    return "\$table->unsignedBigInteger($column);";
                },
                $content
            );

            // Replace foreignId with just nullable
            $content = preg_replace_callback(
                '/\$table->foreignId\(([^)]+)\)->nullable\(\);/',
                function ($matches) {
                    $column = trim($matches[1], "'");
                    return "\$table->unsignedBigInteger($column)->nullable();";
                },
                $content
            );

            // Remove explicit foreign key constraints
            $content = preg_replace(
                '/\$table->foreign\([^)]+\)->references\([^)]+\)->on\([^)]+\)->onDelete\([^)]+\);[\s\n]*/',
                '',
                $content
            );

            // Fix specific user_id references that use 'user_id' instead of 'id'
            $content = preg_replace(
                '/\$table->foreign\(\'user_id\'\)->references\(\'user_id\'\)->on\(\'users\'\)->onDelete\([^)]+\);/',
                '',
                $content
            );

            // Fix user_id references that use 'id'
            $content = preg_replace(
                '/\$table->foreign\(\'user_id\'\)->references\(\'id\'\)->on\(\'users\'\)->onDelete\([^)]+\);/',
                '',
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
        $this->info("All foreign key constraints have been temporarily removed");
        $this->info("Now run: php artisan migrate:fresh");

        return 0;
    }
}
