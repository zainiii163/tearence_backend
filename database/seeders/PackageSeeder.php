<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'title' => 'Basic Package',
                'description' => 'Basic listing package for standard listings',
                'price' => 0.00,
                'listing_days' => 30,
                'promo_days' => 0,
                'promo_show_promoted_area' => false,
                'promo_show_featured_area' => false,
                'promo_show_at_top' => false,
                'promo_sign' => false,
                'recommended_sign' => false,
                'auto_renewal' => false,
                'pictures' => 5,
                'duration_days' => 30,
                'max_listings' => 10,
                'is_active' => true,
            ],
            [
                'title' => 'Standard Package',
                'description' => 'Standard listing package with promotional features',
                'price' => 29.99,
                'listing_days' => 60,
                'promo_days' => 30,
                'promo_show_promoted_area' => true,
                'promo_show_featured_area' => false,
                'promo_show_at_top' => false,
                'promo_sign' => true,
                'recommended_sign' => false,
                'auto_renewal' => false,
                'pictures' => 10,
                'duration_days' => 60,
                'max_listings' => 50,
                'is_active' => true,
            ],
            [
                'title' => 'Premium Package',
                'description' => 'Premium listing package with all promotional features',
                'price' => 59.99,
                'listing_days' => 90,
                'promo_days' => 60,
                'promo_show_promoted_area' => true,
                'promo_show_featured_area' => true,
                'promo_show_at_top' => true,
                'promo_sign' => true,
                'recommended_sign' => true,
                'auto_renewal' => true,
                'pictures' => 20,
                'duration_days' => 90,
                'max_listings' => 100,
                'is_active' => true,
            ],
            [
                'title' => 'Professional Package',
                'description' => 'Professional package for businesses with maximum exposure',
                'price' => 99.99,
                'listing_days' => 180,
                'promo_days' => 120,
                'promo_show_promoted_area' => true,
                'promo_show_featured_area' => true,
                'promo_show_at_top' => true,
                'promo_sign' => true,
                'recommended_sign' => true,
                'auto_renewal' => true,
                'pictures' => 50,
                'duration_days' => 180,
                'max_listings' => 500,
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}

