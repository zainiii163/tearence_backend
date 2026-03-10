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
            
            // User and group seeders
            GroupSeeder::class,
            AdminUserSeeder::class,
            DashboardPermissionSeeder::class,
            
            // Customer and related seeders
            CustomerSeeder::class,
            LocationSeeder::class,
            CustomerBusinessSeeder::class,
            CustomerStoreSeeder::class,
            
            // Campaign and donor seeders
            CampaignSeeder::class,
            DonorSeeder::class,
            
            // Content seeders
            BannerSeeder::class,
            AffiliateLinksSeeder::class,
            BooksAuthorsSeeder::class,
            BooksSeeder::class,
            BlogSeeder::class,
            AdvertisementSeeder::class,
            
            // New comprehensive seeders
            ServiceSeeder::class,
            VehicleSeeder::class,
            BannerAdsSeeder::class,
            AffiliateSeeder::class,
            FundingSeeder::class,
            ResortsTravelSeeder::class,
            JobsSeeder::class,
            PropertySeeder::class,
            SponsoredAdvertsSeeder::class,
            
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
