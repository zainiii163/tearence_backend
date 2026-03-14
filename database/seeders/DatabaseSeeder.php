<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // Core data seeders (must run first due to foreign key dependencies)
            CurrencySeeder::class,
            LanguageSeeder::class,
            CountrySeeder::class,
            ZoneSeeder::class,
            CategorySeeder::class,
            PackageSeeder::class,
            
            // Ad pricing plans (before content that uses them)
            AdPricingPlansSeeder::class,
            
            // Buy & Sell marketplace seeders
            BuySellCategorySeeder::class,
            BuySellPromotionPlanSeeder::class,
            BuySellAdvertSeeder::class,
            
            // Books marketplace seeders
            PricingPlanSeeder::class,
            BookAdvertSeeder::class,
            
            // Services Marketplace System
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            
            // Sponsored Adverts System
            SponsoredCategorySeeder::class,
            SponsoredPricingPlanSeeder::class,
            SponsoredAdvertSeeder::class,
            
            // Banner Adverts System
            BannerCategorySeeder::class,
            BannerAdSeeder::class,
            
            // Listing and job-related seeders
            SampleListingsSeeder::class,
            AllCategoryPostsSeeder::class,
            ListingSeeder::class,
            CandidateProfileSeeder::class,
            JobAlertSeeder::class,
            JobUpsellSeeder::class,
            CandidateUpsellSeeder::class,
            
            // Revenue tracking (must be last)
            RevenueTrackingSeeder::class,
        ]);
    }
}
