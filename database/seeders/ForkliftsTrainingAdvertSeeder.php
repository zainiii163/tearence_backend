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

        $advertData = [
            'title'       => 'Professional Forklift Training',
            'slug'        => 'professional-forklift-training',
            'tagline'     => 'Build your skills. Boost your career.',
            'description' => 'We provide high quality training for a wide range of Material Handling Equipment. Nationwide training by fully insured and experienced instructors. Accredited training — safe and professional.',
            'key_features' => json_encode([
                'Forklift Training',
                'Pivot Steer Training',
                'Telehandler Training',
                'And more courses',
                'Experienced Instructors',
                'Accredited Training',
                'Safe & Professional',
            ]),
            'advert_type' => 'service',
            'category_id' => $categoryId,
            'country'     => 'United Kingdom',
            'city'        => 'Wolverhampton',
            'price'       => null,
            'currency'    => 'GBP',
            'price_type'  => 'negotiable',
            'condition'   => 'not_applicable',
            'main_image'  => 'promoted-adverts/forklifts-training.jpg',
            'seller_name' => 'Forklifts Training Ltd',
            'business_name' => 'Forklifts Training Ltd',
            'phone'       => '01922 315615',
            'email'       => $email,
            'website'     => null,
            'verified_seller' => true,
            'status'      => 'active',
            'is_active'   => true,
            'approved_at' => $now,
        ];

        // 1. Create in promoted_adverts (promoted + featured)
        $promotedId = DB::table('promoted_adverts')->insertGetId(array_merge($advertData, [
            'slug'             => 'professional-forklift-training',
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

        // 2. Create in sponsored_adverts (sponsored)
        DB::table('sponsored_adverts')->insert(array_merge($advertData, [
            'slug'              => 'professional-forklift-training',
            'contact_name'      => 'Forklifts Training Ltd',
            'contact_phone'     => '01922 315615',
            'contact_email'     => $email,
            'sponsored_tier'    => 'premium',
            'promotion_price'   => 299.99,
            'promotion_start'   => $now,
            'promotion_end'     => $expiresAt,
            'payment_status'    => 'paid',
            'user_id'           => $user?->user_id,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]));
        $this->command->info("Created sponsored_advert");

        // 3. Create in featured_adverts (upsell_tier = sponsored)
        DB::table('featured_adverts')->insert([
            'customer_id'       => $customer?->customer_id,
            'title'             => 'Professional Forklift Training',
            'slug'              => 'professional-forklift-training-' . Str::random(4),
            'description'       => $advertData['description'],
            'price'             => null,
            'currency'          => 'GBP',
            'advert_type'       => 'service',
            'condition'         => null,
            'images'            => json_encode(['forklifts-training.jpg']),
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

        $this->command->info("Forklifts Training Ltd advert created as sponsored, promoted & featured.");
    }
}
