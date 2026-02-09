<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::take(5)->get();
        $categories = Category::whereNull('parent_id')->take(3)->get();
        $currencies = Currency::take(3)->get();
        $locations = Location::take(5)->get();
        $packages = Package::take(2)->get();

        if ($customers->count() === 0) {
            $this->command->warn('No customers found. Skipping ListingSeeder.');
            return;
        }

        $listings = [
            [
                'customer_id' => $customers[0]->customer_id,
                'location_id' => $locations->count() > 0 ? $locations[0]->location_id : null,
                'category_id' => $categories->count() > 0 ? $categories[0]->category_id : null,
                'currency_id' => $currencies->count() > 0 ? $currencies[0]->currency_id : null,
                'package_id' => $packages->count() > 0 ? $packages[0]->package_id : null,
                'title' => 'Senior PHP Developer - Remote Position',
                'slug' => Str::slug('Senior PHP Developer - Remote Position'),
                'description' => 'We are looking for an experienced PHP developer to join our remote team. Must have 5+ years of experience with Laravel framework.',
                'price' => 80000,
                'type' => 'international',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 1 ? $customers[1]->customer_id : $customers[0]->customer_id,
                'location_id' => $locations->count() > 1 ? $locations[1]->location_id : null,
                'category_id' => $categories->count() > 1 ? $categories[1]->category_id : null,
                'currency_id' => $currencies->count() > 1 ? $currencies[1]->currency_id : null,
                'package_id' => $packages->count() > 1 ? $packages[1]->package_id : null,
                'title' => 'Frontend Developer - React/Vue.js',
                'slug' => Str::slug('Frontend Developer - React/Vue.js'),
                'description' => 'Join our frontend team and work on cutting-edge web applications using React and Vue.js.',
                'price' => 75000,
                'type' => 'international',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 2 ? $customers[2]->customer_id : $customers[0]->customer_id,
                'location_id' => $locations->count() > 2 ? $locations[2]->location_id : null,
                'category_id' => $categories->count() > 2 ? $categories[2]->category_id : null,
                'currency_id' => $currencies->count() > 2 ? $currencies[2]->currency_id : null,
                'package_id' => $packages->count() > 0 ? $packages[0]->package_id : null,
                'title' => 'Full Stack Developer - Python/Django',
                'slug' => Str::slug('Full Stack Developer - Python/Django'),
                'description' => 'Looking for a talented full-stack developer with experience in Python and Django framework.',
                'price' => 90000,
                'type' => 'international',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 3 ? $customers[3]->customer_id : $customers[0]->customer_id,
                'location_id' => $locations->count() > 3 ? $locations[3]->location_id : null,
                'category_id' => $categories->count() > 0 ? $categories[0]->category_id : null,
                'currency_id' => $currencies->count() > 0 ? $currencies[0]->currency_id : null,
                'package_id' => null,
                'title' => 'UI/UX Designer - Creative Agency',
                'slug' => Str::slug('UI/UX Designer - Creative Agency'),
                'description' => 'Creative agency seeking a talented UI/UX designer to create amazing user experiences.',
                'price' => 65000,
                'type' => 'international',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 4 ? $customers[4]->customer_id : $customers[0]->customer_id,
                'location_id' => $locations->count() > 4 ? $locations[4]->location_id : null,
                'category_id' => $categories->count() > 1 ? $categories[1]->category_id : null,
                'currency_id' => $currencies->count() > 1 ? $currencies[1]->currency_id : null,
                'package_id' => null,
                'title' => 'DevOps Engineer - AWS/Kubernetes',
                'slug' => Str::slug('DevOps Engineer - AWS/Kubernetes'),
                'description' => 'Experienced DevOps engineer needed to manage our cloud infrastructure and deployment pipelines.',
                'price' => 95000,
                'type' => 'international',
                'status' => 'active',
            ],
        ];

        foreach ($listings as $listing) {
            Listing::create($listing);
        }
    }
}

