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
            
            // Content seeders
            BannerSeeder::class,
            AffiliateLinksSeeder::class,
            BooksSeeder::class,
            BlogSeeder::class,
            AdvertisementSeeder::class,
            
            // User and group seeders
            GroupSeeder::class,
            AdminUserSeeder::class,
            DashboardPermissionSeeder::class, // Analytics permissions
            
            // Customer and related seeders
            CustomerSeeder::class,
            LocationSeeder::class,
            CustomerBusinessSeeder::class,
            CustomerStoreSeeder::class,
            
            // Campaign and donor seeders
            CampaignSeeder::class,
            DonorSeeder::class,
            
            // Listing and job-related seeders
            SampleListingsSeeder::class, // New comprehensive listing seeder
            AllCategoryPostsSeeder::class, // Posts for all categories
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
