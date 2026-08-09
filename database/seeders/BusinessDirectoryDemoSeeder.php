<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerBusiness;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Real Business directory posts for demo users John Doe + Vikas.
 * Safe to re-run (upserts by slug).
 */
class BusinessDirectoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        $john = Customer::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password_hash' => Hash::make('password123'),
                'phone' => '+447700900001',
                'email_verified_at' => now(),
                'customer_uid' => Str::random(10),
            ]
        );

        $vikas = Customer::firstOrCreate(
            ['email' => 'vikas@worldwideadverts.info'],
            [
                'first_name' => 'Vikas',
                'last_name' => 'Admin',
                'password_hash' => Hash::make('Admin@123'),
                'phone' => '+447700900002',
                'email_verified_at' => now(),
                'user_type' => 'business',
                'customer_uid' => Str::random(10),
            ]
        );

        $posts = [
            [
                'customer_id' => $john->customer_id,
                'slug' => 'doe-digital-retail-hub',
                'business_name' => 'Doe Digital Retail Hub',
                'business_description' => 'Online and high-street retail partner for worldwide brands — sourcing, store ops and marketplace fulfilment.',
                'business_phone_number' => '+44 20 7946 0101',
                'business_address' => '12 Market Street, London, UK',
                'business_email' => 'hello@doeretail.example.com',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'John Doe',
                'business_category_slug' => 'retail',
                'status' => 'active',
            ],
            [
                'customer_id' => $john->customer_id,
                'slug' => 'john-doe-professional-services',
                'business_name' => 'John Doe Professional Services',
                'business_description' => 'Consulting, bookkeeping support and business setup services for startups and SMEs.',
                'business_phone_number' => '+44 20 7946 0102',
                'business_address' => '88 Commerce Road, Manchester, UK',
                'business_email' => 'services@johndoe.example.com',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'John Doe',
                'business_category_slug' => 'professional-services',
                'status' => 'active',
            ],
            [
                'customer_id' => $vikas->customer_id,
                'slug' => 'vikas-global-tech-solutions',
                'business_name' => 'Vikas Global Tech Solutions',
                'business_description' => 'IT services, software consulting and digital transformation for growing companies worldwide.',
                'business_phone_number' => '+44 20 7946 2200',
                'business_address' => '200 Innovation Way, Birmingham, UK',
                'business_email' => 'vikas@worldwideadverts.info',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'Vikas',
                'business_category_slug' => 'technology-electronics',
                'status' => 'active',
            ],
            [
                'customer_id' => $vikas->customer_id,
                'slug' => 'vikas-healthcare-wellness-group',
                'business_name' => 'Vikas Healthcare & Wellness Group',
                'business_description' => 'Clinics, wellness centres and health-service partners listed for global clients.',
                'business_phone_number' => '+44 20 7946 2201',
                'business_address' => '45 Care Avenue, Leeds, UK',
                'business_email' => 'vikas.health@worldwideadverts.info',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'Vikas',
                'business_category_slug' => 'healthcare-wellness',
                'status' => 'active',
            ],
            [
                'customer_id' => $vikas->customer_id,
                'slug' => 'vikas-education-training-academy',
                'business_name' => 'Vikas Education & Training Academy',
                'business_description' => 'Corporate training, courses and skills programmes for teams and individuals.',
                'business_phone_number' => '+44 20 7946 2202',
                'business_address' => '9 Learning Lane, Bristol, UK',
                'business_email' => 'vikas.learn@worldwideadverts.info',
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => 'Vikas',
                'business_category_slug' => 'education-training',
                'status' => 'active',
            ],
        ];

        foreach ($posts as $post) {
            CustomerBusiness::updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }

        // Soft-clean obvious faker junk titles owned by john (optional)
        CustomerBusiness::where('customer_id', $john->customer_id)
            ->whereIn('business_name', ['Kaseem Torres', 'Kieran Salazar', 'Jamal Kinney'])
            ->update(['status' => 'inactive']);

        $this->command?->info('Business directory demo posts ready for John Doe + Vikas.');
    }
}
