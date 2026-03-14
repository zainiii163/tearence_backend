<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixRemainingUserForeignKeys extends Command
{
    protected $signature = 'db:fix-remaining-user-fks';
    protected $description = 'Fix all remaining user_id foreign key references';

    public function handle()
    {
        $this->info('🔧 Fixing remaining user_id foreign key issues...');
        
        $migrationsPath = database_path('migrations/');
        $problematicMigrations = [
            '2026_03_10_140003_create_property_analytics_table.php',
            '2026_03_10_140004_create_property_enquiries_table.php',
            '2026_03_10_140006_create_affiliate_applications_table.php',
            '2026_03_10_140006_create_job_saved_listings_table.php',
            '2026_03_12_000002_create_sponsored_advert_analytics_table.php',
            '2026_03_12_000003_create_sponsored_advert_favourites_table.php',
            '2026_03_14_000002_create_funding_pledges_table.php',
            '2026_03_14_000006_create_funding_favorites_table.php',
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

            // Fix foreignId('user_id') references to use unsignedInteger and correct foreign key
            $content = preg_replace(
                '/\$table->foreignId\(\'user_id\'\)->constrained\(\)->onDelete\(\'set null\'\);/',
                "\$table->unsignedInteger('user_id')->nullable();\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');",
                $content
            );

            $content = preg_replace(
                '/\$table->foreignId\(\'user_id\'\)->constrained\(\)->onDelete\(\'cascade\'\);/',
                "\$table->unsignedInteger('user_id');\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');",
                $content
            );

            // Fix unsignedBigInteger('user_id') references
            $content = preg_replace(
                '/\$table->unsignedBigInteger\(\'user_id\'\);/',
                "\$table->unsignedInteger('user_id');",
                $content
            );

            $content = preg_replace(
                '/\$table->unsignedBigInteger\(\'user_id\'\)->nullable\(\);/',
                "\$table->unsignedInteger('user_id')->nullable();",
                $content
            );

            // Add foreign keys for user_id if they don't exist
            if (str_contains($content, "\$table->unsignedInteger('user_id');") && 
                !str_contains($content, "->references('user_id')->on('users')")) {
                
                $content = preg_replace(
                    '/\$table->unsignedInteger\(\'user_id\'\);/',
                    "\$table->unsignedInteger('user_id');\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');",
                    $content
                );
            }

            if (str_contains($content, "\$table->unsignedInteger('user_id')->nullable();") && 
                !str_contains($content, "->references('user_id')->on('users')")) {
                
                $content = preg_replace(
                    '/\$table->unsignedInteger\(\'user_id\'\)->nullable\(\);/',
                    "\$table->unsignedInteger('user_id')->nullable();\n            \$table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');",
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
        $this->info("Now run: php artisan migrate:smart");

        return 0;
    }
}
