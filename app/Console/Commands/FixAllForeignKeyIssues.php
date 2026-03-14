<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixAllForeignKeyIssues extends Command
{
    protected $signature = 'db:fix-all-foreign-key-issues';
    protected $description = 'Fix all foreign key constraints with correct table and column references';

    public function handle()
    {
        $this->info('🔧 Fixing all foreign key constraints with correct references...');
        
        $migrationsPath = database_path('migrations/');
        $migrationsToFix = [
            // Books related
            '2026_03_08_100002_create_book_upsells_table.php',
            '2026_03_08_100003_create_book_saves_table.php',
            
            // User related
            '2026_03_10_120002_create_promoted_advert_analytics_table.php',
            '2026_03_10_120001_create_promoted_advert_favorites_table.php',
            '2026_03_08_200000_create_sponsored_adverts_table.php',
            '2026_03_08_200002_create_sponsored_advert_ratings_table.php',
            
            // Other
            '2026_03_07_205714_create_banner_ads_table.php',
            '2026_03_08_100002_create_property_analytics_table.php',
            '2026_03_08_100003_create_property_saved_table.php',
            '2026_03_08_000001_create_resorts_travel_adverts_table.php',
            '2026_03_07_211230_create_vehicles_table.php',
            '2026_03_07_201412_create_services_table.php',
            '2026_03_07_201138_create_service_providers_table.php',
        ];

        $fixed = 0;

        foreach ($migrationsToFix as $file) {
            $filePath = $migrationsPath . $file;
            
            if (!File::exists($filePath)) {
                $this->line("⏭️  $file (not found)");
                continue;
            }

            $content = File::get($filePath);
            $originalContent = $content;

            // Fix user_id references - change to unsignedInteger and add explicit foreign key
            if (str_contains($file, 'create_') && !str_contains($file, 'users')) {
                // Replace foreignId('user_id') with unsignedInteger
                $content = preg_replace(
                    '/\$table->foreignId\(\'user_id\'\)->constrained\(\)->onDelete\(\'cascade\'\);/',
                    "\$table->unsignedInteger('user_id');\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');",
                    $content
                );
                
                // Replace unsignedBigInteger('user_id') with unsignedInteger and add foreign key
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'user_id\'\);/',
                    "\$table->unsignedInteger('user_id');\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');",
                    $content
                );
                
                // Replace nullable user_id references
                $content = preg_replace(
                    '/\$table->foreignId\(\'user_id\'\)->nullable\(\)->constrained\(\)->onDelete\(\'set null\'\);/',
                    "\$table->unsignedInteger('user_id')->nullable();\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');",
                    $content
                );
                
                // Replace unsignedInteger('user_id')->nullable() with foreign key
                $content = preg_replace(
                    '/\$table->unsignedInteger\(\'user_id\'\)->nullable\(\);/',
                    "\$table->unsignedInteger('user_id')->nullable();\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');",
                    $content
                );
            }

            // Fix book_upsells table - book_id should reference books.id
            if (str_contains($file, 'create_book_upsells_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'book_id\'\);/',
                    "\$table->foreignId('book_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix book_saves table - book_id should reference books.id
            if (str_contains($file, 'create_book_saves_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'book_id\'\);/',
                    "\$table->foreignId('book_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix promoted_advert_analytics table
            if (str_contains($file, 'create_promoted_advert_analytics_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'promoted_advert_id\'\);/',
                    "\$table->foreignId('promoted_advert_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix promoted_advert_favorites table
            if (str_contains($file, 'create_promoted_advert_favorites_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'promoted_advert_id\'\);/',
                    "\$table->foreignId('promoted_advert_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix sponsored_adverts table
            if (str_contains($file, 'create_sponsored_adverts_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'user_id\'\)/',
                    "\$table->unsignedInteger('user_id')",
                    $content
                );
                if (!str_contains($content, "->references('user_id')->on('users')")) {
                    $content = preg_replace(
                        '/\$table->unsignedInteger\(\'user_id\'\);/',
                        "\$table->unsignedInteger('user_id');\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');",
                        $content
                    );
                }
            }

            // Fix sponsored_advert_ratings table
            if (str_contains($file, 'create_sponsored_advert_ratings_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'sponsored_advert_id\'\);/',
                    "\$table->foreignId('sponsored_advert_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix property_analytics table
            if (str_contains($file, 'create_property_analytics_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'property_id\'\);/',
                    "\$table->foreignId('property_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix property_saved table
            if (str_contains($file, 'create_property_saved_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'property_id\'\);/',
                    "\$table->foreignId('property_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix banner_ads table
            if (str_contains($file, 'create_banner_ads_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'user_id\'\)/',
                    "\$table->unsignedInteger('user_id')",
                    $content
                );
                if (!str_contains($content, "->references('user_id')->on('users')")) {
                    $content = preg_replace(
                        '/\$table->unsignedInteger\(\'user_id\'\);/',
                        "\$table->unsignedInteger('user_id');\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');",
                        $content
                    );
                }
            }

            // Fix resorts_travel_adverts table
            if (str_contains($file, 'create_resorts_travel_adverts_table')) {
                $content = preg_replace(
                    '/\$table->unsignedBigInteger\(\'user_id\'\)/',
                    "\$table->unsignedInteger('user_id')",
                    $content
                );
                if (!str_contains($content, "->references('user_id')->on('users')")) {
                    $content = preg_replace(
                        '/\$table->unsignedInteger\(\'user_id\'\);/',
                        "\$table->unsignedInteger('user_id');\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');",
                        $content
                    );
                }
            }

            if ($content !== $originalContent) {
                File::put($filePath, $content);
                $this->info("✅ Fixed $file");
                $fixed++;
            } else {
                $this->line("⏭️  $file (no changes needed)");
            }
        }

        $this->info("\n📊 Fixed $fixed migration files");
        $this->info("All foreign key constraints have been properly fixed");
        $this->info("Now run: php artisan migrate:fresh --seed");

        return 0;
    }
}
