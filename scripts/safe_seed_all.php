<?php
/**
 * Run each DatabaseSeeder entry individually; continue on duplicate/unique errors.
 */
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Ensure critical missing tables for local
if (!Schema::hasTable('service_orders')) {
    Schema::create('service_orders', function ($table) {
        $table->id();
        $table->unsignedBigInteger('service_id')->nullable()->index();
        $table->unsignedBigInteger('buyer_id')->nullable()->index();
        $table->unsignedBigInteger('seller_id')->nullable()->index();
        $table->unsignedBigInteger('package_id')->nullable()->index();
        $table->json('requirements')->nullable();
        $table->decimal('total_price', 12, 2)->nullable();
        $table->decimal('fee_percent', 5, 2)->nullable();
        $table->decimal('platform_fee', 12, 2)->nullable();
        $table->decimal('seller_amount', 12, 2)->nullable();
        $table->string('delivery_time')->nullable();
        $table->string('status')->default('pending')->index();
        $table->text('buyer_notes')->nullable();
        $table->text('seller_notes')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamp('cancelled_at')->nullable();
        $table->decimal('refund_amount', 12, 2)->nullable();
        $table->timestamps();
    });
    echo "Created missing table: service_orders\n";
}

$seeders = [
    'Database\\Seeders\\CurrencySeeder',
    'Database\\Seeders\\LanguageSeeder',
    'Database\\Seeders\\CountrySeeder',
    'Database\\Seeders\\ZoneSeeder',
    'Database\\Seeders\\CategorySeeder',
    'Database\\Seeders\\PackageSeeder',
    'Database\\Seeders\\AdPricingPlansSeeder',
    'Database\\Seeders\\BuySellCategorySeeder',
    'Database\\Seeders\\BuySellPromotionPlanSeeder',
    'Database\\Seeders\\BuySellAdvertSeeder',
    'Database\\Seeders\\PricingPlanSeeder',
    'Database\\Seeders\\BookAdvertSeeder',
    'Database\\Seeders\\ServiceCategorySeeder',
    'Database\\Seeders\\ServiceSeeder',
    'Database\\Seeders\\EventsVenuesCategorySeeder',
    'Database\\Seeders\\ClientStockImagesSeeder',
    'Database\\Seeders\\DocsWhatsAppImagesSeeder',
    'Database\\Seeders\\FundingSeeder',
    'Database\\Seeders\\BusinessTemplateSeeder',
    'Database\\Seeders\\SponsoredCategorySeeder',
    'Database\\Seeders\\SponsoredPricingPlanSeeder',
    'Database\\Seeders\\SponsoredAdvertSeeder',
    'Database\\Seeders\\PromoPricingPlanSeeder',
    'Database\\Seeders\\AffiliateUpsellPlanSeeder',
    'Database\\Seeders\\TeamRoleSeeder',
    'Database\\Seeders\\BannerCategorySeeder',
    'Database\\Seeders\\BannerAdSeeder',
    'Database\\Seeders\\CategoryPaidBannersSeeder',
    'Database\\Seeders\\SampleListingsSeeder',
    'Database\\Seeders\\AllCategoryPostsSeeder',
    'Database\\Seeders\\ListingSeeder',
    'Database\\Seeders\\CandidateProfileSeeder',
    'Database\\Seeders\\JobAlertSeeder',
    'Database\\Seeders\\JobUpsellSeeder',
    'Database\\Seeders\\CandidateUpsellSeeder',
    'Database\\Seeders\\RevenueTrackingSeeder',
];

$ok = 0;
$skipped = 0;
$failed = [];

foreach ($seeders as $class) {
    $short = class_basename($class);
    echo ">>> $short ... ";
    try {
        Artisan::call('db:seed', ['--class' => $class, '--force' => true]);
        echo "OK\n";
        $ok++;
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (
            stripos($msg, 'Duplicate') !== false
            || stripos($msg, '1062') !== false
            || stripos($msg, 'UniqueConstraint') !== false
        ) {
            echo "SKIP (already seeded)\n";
            $skipped++;
        } else {
            $shortMsg = preg_replace('/\s+/', ' ', substr($msg, 0, 200));
            echo "FAIL: $shortMsg\n";
            $failed[] = [$short, $shortMsg];
        }
    }
}

echo "\nTables now: " . count(DB::select('SHOW TABLES')) . "\n";
echo "Seeders OK=$ok SKIP=$skipped FAIL=" . count($failed) . "\n";
if ($failed) {
    foreach ($failed as [$n, $m]) {
        echo " - $n: $m\n";
    }
    exit(1);
}
exit(0);
