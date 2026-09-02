<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForkliftsTrainingAdvertSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $expiresAt = now()->addYears(1);
        $email = 'hanzoali96@gmail.com';

        // Resolve admin user (users table) and customer (customers table)
        $user = DB::table('users')->where('email', $email)->first();
        $customer = DB::table('customer')->where('email', $email)->first();

        if (!$user && !$customer) {
            $this->command->error("No user or customer found for {$email}. Aborting.");
            return;
        }

        // Resolve promoted advert category (Jobs & Services)
        $category = DB::table('promoted_advert_categories')
            ->where('slug', 'jobs-services')
            ->first();

        $categoryId = $category?->id ?? 3;

        $baseData = [
            'title'       => 'Professional Forklift Training',
            'tagline'     => 'Build your skills. Boost your career.',
            'description' => 'We provide high quality training for a wide range of Material Handling Equipment. Nationwide training by fully insured and experienced instructors. Accredited training — safe and professional.',
            'advert_type' => 'service',
            'country'     => 'United Kingdom',
            'city'        => 'Wolverhampton',
            'price'       => null,
            'currency'    => 'GBP',
            'condition'   => 'not_applicable',
            'main_image'  => 'promoted-adverts/forklifts-training.jpg',
            'additional_images' => json_encode(['forklifts-training-2.jpg', 'forklifts-training-3.jpg']),
            'seller_name' => 'Forklifts Training Ltd',
            'business_name' => 'Forklifts Training Ltd',
            'phone'       => '01922 315615',
            'email'       => $email,
            'verified_seller' => true,
            'status'      => 'active',
            'is_active'   => true,
            'approved_at' => $now,
        ];

        // 1. Create in promoted_adverts (promoted + featured)
        $promotedExists = DB::table('promoted_adverts')
            ->where('slug', 'professional-forklift-training')
            ->where('email', $email)
            ->exists();

        if (!$promotedExists) {
            $promotedId = DB::table('promoted_adverts')->insertGetId(array_merge($baseData, [
                'slug'             => 'professional-forklift-training',
                'key_features'     => json_encode(['Forklift Training', 'Pivot Steer Training', 'Telehandler Training', 'And more courses']),
                'category_id'      => $categoryId,
                'price_type'       => 'negotiable',
                'is_featured'      => true,
                'promotion_tier'   => 'network_wide_boost',
                'promotion_price'  => 199.99,
                'promotion_start'  => $now->toDateString(),
                'promotion_end'    => $expiresAt->toDateString(),
                'user_id'          => $user?->user_id,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]));
            $this->command->info("Created promoted_advert #{$promotedId}");
        } else {
            $this->command->info("promoted_adverts already exists — skipping.");
        }

        // 2. Create in sponsored_adverts (sponsored)
        $sponsoredExists = DB::table('sponsored_adverts')
            ->where('slug', 'professional-forklift-training')
            ->where('email', $email)
            ->exists();

        if (!$sponsoredExists) {
            // Use only columns that exist across all migration versions
            $sponsoredData = [
                'title'             => 'Professional Forklift Training',
                'slug'              => 'professional-forklift-training',
                'tagline'           => 'Build your skills. Boost your career.',
                'description'       => $baseData['description'],
                'advert_type'       => 'service',
                'category'          => 'Jobs & Services',
                'country'           => 'United Kingdom',
                'city'              => 'Wolverhampton',
                'price'             => null,
                'main_image'        => 'promoted-adverts/forklifts-training.jpg',
                'additional_images' => json_encode(['forklifts-training-2.jpg', 'forklifts-training-3.jpg']),
                'phone'             => '01922 315615',
                'email'             => $email,
                'sponsored_tier'    => 'premium',
                'is_active'         => true,
                'payment_status'    => 'paid',
                'user_id'           => $user?->user_id,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            // Add columns only if they exist on this install
            $sponsoredCols = collect(DB::select('SHOW COLUMNS FROM sponsored_adverts'))->pluck('Field')->toArray();
            if (in_array('seller_name', $sponsoredCols)) {
                $sponsoredData['seller_name'] = 'Forklifts Training Ltd';
            }
            if (in_array('business_name', $sponsoredCols)) {
                $sponsoredData['business_name'] = 'Forklifts Training Ltd';
            }
            if (in_array('contact_name', $sponsoredCols)) {
                $sponsoredData['contact_name'] = 'Forklifts Training Ltd';
            }
            if (in_array('contact_phone', $sponsoredCols)) {
                $sponsoredData['contact_phone'] = '01922 315615';
            }
            if (in_array('contact_email', $sponsoredCols)) {
                $sponsoredData['contact_email'] = $email;
            }
            if (in_array('status', $sponsoredCols)) {
                $sponsoredData['status'] = 'approved';
            }
            if (in_array('promotion_price', $sponsoredCols)) {
                $sponsoredData['promotion_price'] = 299.99;
            }
            if (in_array('tier_price', $sponsoredCols)) {
                $sponsoredData['tier_price'] = 299.99;
            }
            if (in_array('promotion_start', $sponsoredCols)) {
                $sponsoredData['promotion_start'] = $now;
            }
            if (in_array('promotion_end', $sponsoredCols)) {
                $sponsoredData['promotion_end'] = $expiresAt;
            }
            if (in_array('approved_at', $sponsoredCols)) {
                $sponsoredData['approved_at'] = $now;
            }
            if (in_array('verified_seller', $sponsoredCols)) {
                $sponsoredData['verified_seller'] = true;
            }
            if (in_array('is_verified_seller', $sponsoredCols)) {
                $sponsoredData['is_verified_seller'] = true;
            }

            DB::table('sponsored_adverts')->insert($sponsoredData);
            $this->command->info("Created sponsored_advert");
        } else {
            $this->command->info("sponsored_adverts already exists — skipping.");
        }

        // 3. Create in featured_adverts (upsell_tier = sponsored)
        $featuredExists = DB::table('featured_adverts')
            ->where('slug', 'LIKE', 'professional-forklift-training%')
            ->where('contact_email', $email)
            ->exists();

        if (!$featuredExists) {
            if (!$customer) {
                $this->command->warn("No customer record for {$email} — skipping featured_advert.");
            } else {
                DB::table('featured_adverts')->insert([
                'customer_id'       => $customer?->customer_id,
                'title'             => 'Professional Forklift Training',
                'slug'              => 'professional-forklift-training-' . Str::random(4),
                'description'       => $baseData['description'],
                'price'             => null,
                'currency'          => 'GBP',
                'advert_type'       => 'service',
                'condition'         => null,
                'images'            => json_encode(['forklifts-training.jpg', 'forklifts-training-2.jpg', 'forklifts-training-3.jpg']),
                'contact_name'      => 'Forklifts Training Ltd',
                'contact_email'     => $email,
                'contact_phone'     => '01922 315615',
                'country'           => 'United Kingdom',
                'city'              => 'Wolverhampton',
                'upsell_tier'       => 'sponsored',
                'upsell_price'      => 299.99,
                'payment_status'    => 'paid',
                'starts_at'         => $now,
                'expires_at'        => $expiresAt,
                'is_active'         => true,
                'is_verified_seller' => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
                $this->command->info("Created featured_advert");
            }
        } else {
            $this->command->info("featured_adverts already exists — skipping.");
        }

        $this->command->info("Forklifts Training Ltd advert created as sponsored, promoted & featured.");
    }
}
