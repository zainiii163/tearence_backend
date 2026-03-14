<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class RestoreAllForeignKeys extends Command
{
    protected $signature = 'db:restore-all-foreign-keys';
    protected $description = 'Restore all foreign key constraints properly in migrations';

    public function handle()
    {
        $this->info('🔧 Restoring all foreign key constraints properly...');
        
        $migrationsPath = database_path('migrations/');
        $migrationsToFix = [
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

        foreach ($migrationsToFix as $file) {
            $filePath = $migrationsPath . $file;
            
            if (!File::exists($filePath)) {
                $this->line("⏭️  $file (not found)");
                continue;
            }

            $content = File::get($filePath);
            $originalContent = $content;

            // Fix vehicles table - add proper foreign key constraints
            if (str_contains($file, 'create_vehicles_table')) {
                $content = str_replace(
                    "\$table->foreignId('vehicle_category_id'); // FK constraint removed due to migration order",
                    "\$table->foreignId('vehicle_category_id')->constrained()->onDelete('cascade');",
                    $content
                );
                
                // Add user_id foreign key
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix book_upsells table
            if (str_contains($file, 'create_book_upsells_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('book_id');",
                    "\$table->foreignId('book_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id'); // Who purchased the upsell",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who purchased the upsell",
                    $content
                );
            }

            // Fix book_saves table
            if (str_contains($file, 'create_book_saves_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('book_id');",
                    "\$table->foreignId('book_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix promoted_advert_analytics table
            if (str_contains($file, 'create_promoted_advert_analytics_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('promoted_advert_id');",
                    "\$table->foreignId('promoted_advert_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedInteger('user_id')->nullable();",
                    "\$table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');",
                    $content
                );
            }

            // Fix promoted_advert_favorites table
            if (str_contains($file, 'create_promoted_advert_favorites_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('promoted_advert_id');",
                    "\$table->foreignId('promoted_advert_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix sponsored_adverts table
            if (str_contains($file, 'create_sponsored_adverts_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix sponsored_advert_ratings table
            if (str_contains($file, 'create_sponsored_advert_ratings_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('sponsored_advert_id');",
                    "\$table->foreignId('sponsored_advert_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix property_analytics table
            if (str_contains($file, 'create_property_analytics_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('property_id');",
                    "\$table->foreignId('property_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedInteger('user_id')->nullable();",
                    "\$table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');",
                    $content
                );
            }

            // Fix property_saved table
            if (str_contains($file, 'create_property_saved_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('property_id');",
                    "\$table->foreignId('property_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix banner_ads table
            if (str_contains($file, 'create_banner_ads_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix resorts_travel_adverts table
            if (str_contains($file, 'create_resorts_travel_adverts_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix services table
            if (str_contains($file, 'create_services_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('service_provider_id');",
                    "\$table->foreignId('service_provider_id')->nullable()->constrained()->onDelete('set null');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->unsignedBigInteger('user_id');",
                    $content
                );
                // Add explicit foreign key for user_id since users table uses user_id as primary key
                if (!str_contains($content, "->references('user_id')->on('users')")) {
                    $content = str_replace(
                        "\$table->unsignedBigInteger('user_id');",
                        "\$table->unsignedBigInteger('user_id');\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');",
                        $content
                    );
                }
            }

            // Fix service_addons table
            if (str_contains($file, 'create_service_addons_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('service_id');",
                    "\$table->foreignId('service_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix service_promotions table
            if (str_contains($file, 'create_service_promotions_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('service_id');",
                    "\$table->foreignId('service_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix event_venue_service table
            if (str_contains($file, 'create_event_venue_service_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('service_id');",
                    "\$table->foreignId('service_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedBigInteger('event_id');",
                    "\$table->foreignId('event_id')->constrained()->onDelete('cascade');",
                    $content
                );
                $content = str_replace(
                    "\$table->unsignedBigInteger('venue_id');",
                    "\$table->foreignId('venue_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix banners table
            if (str_contains($file, 'create_banners_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
            }

            // Fix authors table
            if (str_contains($file, 'create_authors_table')) {
                $content = str_replace(
                    "\$table->unsignedBigInteger('user_id');",
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    $content
                );
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
        $this->info("All foreign key constraints have been properly restored");
        $this->info("Now run: php artisan migrate:fresh --seed");

        return 0;
    }
}
