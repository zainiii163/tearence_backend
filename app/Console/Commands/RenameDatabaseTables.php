<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameDatabaseTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:rename-tables 
                            {--dry-run : Show what would be renamed without actually doing it}
                            {--force : Force renaming even if target tables exist}
                            {--table= : Rename only a specific table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove ea_ prefix from database tables';

    /**
     * Table mappings: old_name => new_name
     */
    protected $tableMappings = [
        // Books tables
        'ea_books' => 'books',
        'ea_book_categories' => 'book_categories',
        'ea_book_purchases' => 'book_purchases',
        'ea_book_saves' => 'book_saves',
        'ea_book_upsells' => 'book_upsells',

        // Service tables
        'ea_services' => 'services',
        'ea_service_categories' => 'service_categories',
        'ea_service_media' => 'service_media',
        'ea_service_packages' => 'service_packages',
        'ea_service_addons' => 'service_addons',
        'ea_service_providers' => 'service_providers',
        'ea_service_promotions' => 'service_promotions',

        // Affiliate tables
        'ea_affiliate_links' => 'affiliate_links',
        'ea_affiliate_posts' => 'affiliate_posts',
        'ea_affiliate_post_upsells' => 'affiliate_post_upsells',
        'ea_affiliate_upsell_plans' => 'affiliate_upsell_plans',

        // User and customer tables
        'ea_users' => 'users',
        'ea_customer' => 'customer',
        'ea_customer_business' => 'customer_business',
        'ea_customer_store' => 'customer_store',
        'ea_user_analytics' => 'user_analytics',

        // Venue and event tables
        'ea_venues' => 'venues',
        'ea_venue_services' => 'venue_services',
        'ea_events' => 'events',

        // Banner tables
        'ea_banner' => 'banner',
        'ea_banner_ads' => 'banner_ads',
        'ea_banner_categories' => 'banner_categories',

        // Listing tables
        'ea_listing' => 'listing',
        'ea_listing_analytics' => 'listing_analytics',
        'ea_listing_favorite' => 'listing_favorite',
        'ea_listing_image' => 'listing_image',
        'ea_listing_upsells' => 'listing_upsells',

        // Job tables
        'ea_job_alerts' => 'job_alerts',
        'ea_job_upsells' => 'job_upsells',

        // Candidate tables
        'ea_candidate_profiles' => 'candidate_profiles',
        'ea_candidate_upsells' => 'candidate_upsells',

        // Resorts and travel tables
        'ea_resorts_travel_adverts' => 'resorts_travel_adverts',
        'ea_resorts_travel_categories' => 'resorts_travel_categories',

        // Other tables
        'ea_authors' => 'authors',
        'ea_analytics_reports' => 'analytics_reports',
        'ea_campaign' => 'campaign',
        'ea_dashboard_permissions' => 'dashboard_permissions',
        'ea_donor' => 'donor',
        'ea_group' => 'group',
        'ea_blog' => 'blog',
        'ea_location' => 'location',
        'ea_revenue_tracking' => 'revenue_tracking',
        'ea_staff_management' => 'staff_management',
        'ea_system_analytics' => 'system_analytics',
        'ea_advertisement' => 'advertisement',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $specificTable = $this->option('table');

        $this->info('🔄 Database Table Renaming Process');
        $this->info('==================================');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No actual changes will be made');
        }

        try {
            $currentTables = $this->getCurrentTables();
            $this->info("Found " . count($currentTables) . " tables in database");

            $tablesToProcess = $this->getTablesToProcess($specificTable);
            
            if ($specificTable && !isset($this->tableMappings[$specificTable])) {
                $this->error("Table '$specificTable' not found in mappings");
                return 1;
            }

            $this->processTableRenames($tablesToProcess, $currentTables, $dryRun, $force);
            
            if (!$dryRun) {
                $this->checkForeignKeys();
            }

            $this->info('✅ Process completed successfully!');

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Get current tables from database
     */
    protected function getCurrentTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        return array_map('current', $tables);
    }

    /**
     * Get tables to process based on options
     */
    protected function getTablesToProcess(?string $specificTable): array
    {
        if ($specificTable) {
            return [$specificTable => $this->tableMappings[$specificTable]];
        }
        return $this->tableMappings;
    }

    /**
     * Process table renames
     */
    protected function processTableRenames(array $tables, array $currentTables, bool $dryRun, bool $force): void
    {
        $renamed = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($tables));
        $progressBar->start();

        foreach ($tables as $oldName => $newName) {
            $progressBar->advance();

            if (!in_array($oldName, $currentTables)) {
                $this->line("\n  ⏭️  $oldName -> $newName (table doesn't exist)");
                $skipped++;
                continue;
            }

            if (!$force && in_array($newName, $currentTables)) {
                $this->line("\n  ⚠️  $oldName -> $newName (target table already exists)");
                $skipped++;
                continue;
            }

            try {
                if ($dryRun) {
                    $this->line("\n  📝 $oldName -> $newName (would rename)");
                } else {
                    DB::statement("RENAME TABLE `$oldName` TO `$newName`");
                    $this->line("\n  ✅ $oldName -> $newName (renamed)");
                }
                $renamed++;
            } catch (\Exception $e) {
                $this->line("\n  ❌ $oldName -> $newName (error: " . $e->getMessage() . ")");
                $errors++;
            }
        }

        $progressBar->finish();

        $this->newLine();
        $this->info('Summary:');
        $this->info("  Renamed: $renamed tables");
        $this->info("  Skipped: $skipped tables");
        $this->info("  Errors: $errors tables");
    }

    /**
     * Check for foreign key constraints that need updating
     */
    protected function checkForeignKeys(): void
    {
        $this->newLine();
        $this->info('Checking foreign key constraints...');

        $constraints = DB::select("
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME LIKE 'ea_%'
        ");

        if (count($constraints) > 0) {
            $this->warn('Found ' . count($constraints) . ' foreign key constraints referencing old table names:');
            
            foreach ($constraints as $constraint) {
                $this->line("  📋 Table: {$constraint->TABLE_NAME}");
                $this->line("     Constraint: {$constraint->CONSTRAINT_NAME}");
                $this->line("     References: {$constraint->REFERENCED_TABLE_NAME}({$constraint->COLUMN_NAME})");
                $this->newLine();
            }

            $this->warn('⚠️  You may need to manually update these constraints.');
        } else {
            $this->info('✅ No foreign key constraints need updating.');
        }
    }
}
